<?php
// modules/admin/cursos_lecciones.php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(1); // Solo acceso para Administrador
require_once '../../includes/header.php';

$id_curso = $_GET['id'] ?? 0;
$mensaje = "";
$leccion_a_editar = null;

// 1. Verificar que el curso exista
$stmt = $conexion->prepare("SELECT titulo FROM cursos WHERE id = ?");
$stmt->execute([$id_curso]);
$curso = $stmt->fetch();

if (!$curso) {
    echo "<script>window.location='cursos_lista.php';</script>";
    exit;
}

// 2. Cargar datos para editar si se solicita
if (isset($_GET['editar'])) {
    $stmtEdit = $conexion->prepare("SELECT * FROM lecciones WHERE id = ? AND curso_id = ?");
    $stmtEdit->execute([$_GET['editar'], $id_curso]);
    $leccion_a_editar = $stmtEdit->fetch();
}

// 3. Procesar Formulario (Crear o Actualizar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $url = trim($_POST['video_url']);
    $desc = trim($_POST['descripcion']);
    // Nuevos campos para pago
    $es_de_pago = isset($_POST['es_de_pago']) ? $_POST['es_de_pago'] : 0;
    $precio = isset($_POST['precio']) && $_POST['precio'] !== '' ? $_POST['precio'] : 0.00;
    
    $accion = $_POST['accion'];
    
    // Lógica para limpiar y convertir URLs de YouTube a formato 'embed'
    if (strpos($url, 'watch?v=') !== false) {
        $url = str_replace('watch?v=', 'embed/', $url);
        $url = explode('&', $url)[0];
    } elseif (strpos($url, 'youtu.be/') !== false) {
        $parts = explode('youtu.be/', $url);
        $url = 'https://www.youtube.com/embed/' . end($parts);
    }

    if ($titulo && $url) {
        if ($accion == 'crear') {
            // INSERT incluyendo es_de_pago y precio
            $sql = "INSERT INTO lecciones (curso_id, titulo, video_url, descripcion, orden, es_de_pago, precio) VALUES (?, ?, ?, ?, 0, ?, ?)";
            $conexion->prepare($sql)->execute([$id_curso, $titulo, $url, $desc, $es_de_pago, $precio]);
            $mensaje = "<div class='alert alert-success'>Lección añadida correctamente.</div>";
        } elseif ($accion == 'actualizar') {
            $id_lec = $_POST['id_leccion'];
            // UPDATE incluyendo es_de_pago y precio
            $sql = "UPDATE lecciones SET titulo=?, video_url=?, descripcion=?, es_de_pago=?, precio=? WHERE id=? AND curso_id=?";
            $conexion->prepare($sql)->execute([$titulo, $url, $desc, $es_de_pago, $precio, $id_lec, $id_curso]);
            $mensaje = "<div class='alert alert-success'>Lección actualizada.</div>";
            $leccion_a_editar = null; 
        }
    }
}

// 4. Lógica para borrar lección
if (isset($_GET['borrar'])) {
    $id_leccion = $_GET['borrar'];
    $conexion->prepare("DELETE FROM lecciones WHERE id=? AND curso_id=?")->execute([$id_leccion, $id_curso]);
    header("Location: cursos_lecciones.php?id=$id_curso");
    exit;
}

// 5. Listar todas las lecciones del curso
$stmtLec = $conexion->prepare("SELECT * FROM lecciones WHERE curso_id = ? ORDER BY id ASC");
$stmtLec->execute([$id_curso]);
$lecciones = $stmtLec->fetchAll();
?>

<div class="container mt-4">
    <div class="d-flex align-items-center mb-4">
        <a href="cursos_lista.php" class="btn btn-outline-secondary me-3 rounded-circle"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h4 class="mb-0 fw-bold">Contenido del Curso</h4>
            <span class="badge bg-primary"><?php echo htmlspecialchars($curso['titulo']); ?></span>
        </div>
    </div>

    <?php echo $mensaje; ?>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header <?php echo $leccion_a_editar ? 'bg-warning' : 'bg-dark'; ?> text-white">
                    <h5 class="mb-0 small fw-bold"><?php echo $leccion_a_editar ? 'Editar Lección' : 'Nueva Lección'; ?></h5>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="accion" value="<?php echo $leccion_a_editar ? 'actualizar' : 'crear'; ?>">
                        <?php if($leccion_a_editar): ?>
                            <input type="hidden" name="id_leccion" value="<?php echo $leccion_a_editar['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Título de la Clase</label>
                            <input type="text" name="titulo" class="form-control form-control-sm" required
                                   value="<?php echo $leccion_a_editar ? htmlspecialchars($leccion_a_editar['titulo']) : ''; ?>">
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Acceso</label>
                                <select name="es_de_pago" class="form-select form-select-sm">
                                    <option value="0" <?php echo ($leccion_a_editar && $leccion_a_editar['es_de_pago'] == 0) ? 'selected' : ''; ?>>Gratis</option>
                                    <option value="1" <?php echo ($leccion_a_editar && $leccion_a_editar['es_de_pago'] == 1) ? 'selected' : ''; ?>>De Pago</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Precio ($)</label>
                                <input type="number" step="0.01" name="precio" class="form-control form-control-sm" placeholder="0.00"
                                       value="<?php echo $leccion_a_editar ? $leccion_a_editar['precio'] : ''; ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">URL de Video (YouTube)</label>
                            <input type="url" name="video_url" class="form-control form-control-sm" placeholder="https://www.youtube.com/watch?v=..." required
                                   value="<?php echo $leccion_a_editar ? htmlspecialchars($leccion_a_editar['video_url']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Descripción / Notas</label>
                            <textarea name="descripcion" class="form-control form-control-sm" rows="3"><?php echo $leccion_a_editar ? htmlspecialchars($leccion_a_editar['descripcion']) : ''; ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100 shadow-sm">Guardar Lección</button>
                        <?php if($leccion_a_editar): ?>
                            <a href="cursos_lecciones.php?id=<?php echo $id_curso; ?>" class="btn btn-light btn-sm w-100 mt-2 border">Cancelar</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 small">Orden</th>
                                <th class="small">Lección</th>
                                <th class="small">Tipo</th>
                                <th class="text-end pe-4 small">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($lecciones as $index => $l): ?>
                            <tr>
                                <td class="ps-4 text-muted small"><?php echo $index + 1; ?></td>
                                <td>
                                    <div class="fw-bold text-dark small"><?php echo htmlspecialchars($l['titulo']); ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-link-45deg"></i> Video cargado</div>
                                </td>
                                <td>
                                    <?php if($l['es_de_pago'] == 1): ?>
                                        <span class="badge bg-warning text-dark"><i class="bi bi-currency-dollar"></i> $<?php echo number_format($l['precio'], 2); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Gratis</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="cursos_lecciones.php?id=<?php echo $id_curso; ?>&editar=<?php echo $l['id']; ?>" 
                                       class="btn btn-sm btn-outline-primary border-0"><i class="bi bi-pencil"></i></a>
                                    <a href="cursos_lecciones.php?id=<?php echo $id_curso; ?>&borrar=<?php echo $l['id']; ?>" 
                                       class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('¿Borrar esta lección?');"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($lecciones)): ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted small">Aún no hay lecciones en este curso.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer_admin.php'; ?>