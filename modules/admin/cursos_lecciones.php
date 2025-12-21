<?php
// modules/admin/cursos_lecciones.php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(1); 
require_once '../../includes/header.php';

$id_curso = $_GET['id'] ?? 0;
$leccion_a_editar = null;

// Validar Curso
$stmt = $conexion->prepare("SELECT titulo FROM cursos WHERE id = ?");
$stmt->execute([$id_curso]);
$curso = $stmt->fetch();

if (!$curso) { echo "<script>window.location='cursos_lista.php';</script>"; exit; }

// Cargar Edición
if (isset($_GET['editar'])) {
    $stmtEdit = $conexion->prepare("SELECT * FROM lecciones WHERE id = ? AND curso_id = ?");
    $stmtEdit->execute([$_GET['editar'], $id_curso]);
    $leccion_a_editar = $stmtEdit->fetch();
}

// Guardar Lección
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $url = $_POST['video_url'];
    $desc = $_POST['descripcion'];
    $accion = $_POST['accion'];
    
    // Checkbox: 1 = Vista Previa Gratis, 0 = Privado
    $es_gratis = isset($_POST['es_gratis']) ? 1 : 0; 

    // Convertir YouTube
    if (strpos($url, 'watch?v=') !== false) {
        $url = str_replace('watch?v=', 'embed/', explode('&', $url)[0]);
    } elseif (strpos($url, 'youtu.be/') !== false) {
        $parts = explode('youtu.be/', $url);
        $url = 'https://www.youtube.com/embed/' . end($parts);
    }

    if ($accion == 'crear') {
        $sql = "INSERT INTO lecciones (curso_id, titulo, video_url, descripcion, es_gratis, orden) VALUES (?, ?, ?, ?, ?, 0)";
        $conexion->prepare($sql)->execute([$id_curso, $titulo, $url, $desc, $es_gratis]);
    } elseif ($accion == 'actualizar') {
        $id_lec = $_POST['id_leccion'];
        $sql = "UPDATE lecciones SET titulo=?, video_url=?, descripcion=?, es_gratis=? WHERE id=?";
        $conexion->prepare($sql)->execute([$titulo, $url, $desc, $es_gratis, $id_lec]);
        $leccion_a_editar = null;
    }
}

// Borrar
if (isset($_GET['borrar'])) {
    $conexion->prepare("DELETE FROM lecciones WHERE id=?")->execute([$_GET['borrar']]);
    header("Location: cursos_lecciones.php?id=$id_curso"); exit;
}

$lecciones = $conexion->query("SELECT * FROM lecciones WHERE curso_id=$id_curso ORDER BY id ASC")->fetchAll();
?>

<div class="container mt-4">
    <div class="d-flex align-items-center mb-4">
        <a href="cursos_lista.php" class="btn btn-outline-secondary me-3 rounded-circle"><i class="bi bi-arrow-left"></i></a>
        <h4>Contenido: <strong><?php echo htmlspecialchars($curso['titulo']); ?></strong></h4>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0"><?php echo $leccion_a_editar ? 'Editar Lección' : 'Nueva Lección'; ?></h6>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="accion" value="<?php echo $leccion_a_editar ? 'actualizar' : 'crear'; ?>">
                        <?php if($leccion_a_editar): ?>
                            <input type="hidden" name="id_leccion" value="<?php echo $leccion_a_editar['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Título</label>
                            <input type="text" name="titulo" class="form-control form-control-sm" required value="<?php echo $leccion_a_editar['titulo'] ?? ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Video URL (YouTube)</label>
                            <input type="text" name="video_url" class="form-control form-control-sm" required value="<?php echo $leccion_a_editar['video_url'] ?? ''; ?>">
                        </div>
                        
                        <div class="mb-3 form-check form-switch p-3 bg-light border rounded">
                            <input class="form-check-input" type="checkbox" name="es_gratis" value="1" id="checkGratis" 
                                <?php echo ($leccion_a_editar && $leccion_a_editar['es_gratis'] == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label fw-bold text-success" for="checkGratis">Habilitar Vista Previa</label>
                            <div class="form-text small">Si activas esto, cualquiera podrá ver la clase GRATIS.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Descripción</label>
                            <textarea name="descripcion" class="form-control form-control-sm" rows="3"><?php echo $leccion_a_editar['descripcion'] ?? ''; ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Guardar</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Lección</th><th>Estado</th><th class="text-end pe-4">Acciones</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($lecciones as $l): ?>
                            <tr>
                                <td class="ps-3 fw-bold"><?php echo htmlspecialchars($l['titulo']); ?></td>
                                <td>
                                    <?php if($l['es_gratis'] == 1): ?>
                                        <span class="badge bg-success">Vista Previa</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><i class="bi bi-lock-fill"></i> Privado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="cursos_lecciones.php?id=<?php echo $id_curso; ?>&editar=<?php echo $l['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <a href="cursos_lecciones.php?id=<?php echo $id_curso; ?>&borrar=<?php echo $l['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Borrar?');"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer_admin.php'; ?>