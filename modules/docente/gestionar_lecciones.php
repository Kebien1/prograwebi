<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
require_once '../../includes/csrf.php'; // Incluimos la seguridad
verificarRol(2); // Solo Docente
require_once '../../includes/header.php';

$id_curso = $_GET['id'] ?? 0;
$mensaje = "";
$leccion_a_editar = null;

// 1. Verificar que el curso pertenezca a este docente
$stmt = $conexion->prepare("SELECT titulo FROM cursos WHERE id = ? AND docente_id = ?");
$stmt->execute([$id_curso, $_SESSION['usuario_id']]);
$curso = $stmt->fetch();

if (!$curso) {
    echo "<script>window.location='mis_cursos.php';</script>";
    exit;
}

// 2. Lógica para CARGAR DATOS SI ESTAMOS EDITANDO
if (isset($_GET['editar'])) {
    $stmtEdit = $conexion->prepare("SELECT * FROM lecciones WHERE id = ? AND curso_id = ?");
    $stmtEdit->execute([$_GET['editar'], $id_curso]);
    $leccion_a_editar = $stmtEdit->fetch();
}

// 3. PROCESAR FORMULARIO (Crear o Actualizar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf(); // Validamos que el formulario sea seguro

    $titulo = trim($_POST['titulo']);
    $url = trim($_POST['video_url']);
    $desc = trim($_POST['descripcion']);
    $accion = $_POST['accion']; // 'crear' o 'actualizar'
    
    // Limpieza de URL de YouTube (Convierte watch?v= en embed/)
    if (strpos($url, 'watch?v=') !== false) {
        $url = str_replace('watch?v=', 'embed/', $url);
        $url = explode('&', $url)[0];
    } elseif (strpos($url, 'youtu.be/') !== false) {
        $parts = explode('youtu.be/', $url);
        $url = 'https://www.youtube.com/embed/' . end($parts);
    }

    if ($titulo && $url) {
        if ($accion == 'crear') {
            // INSERTAR
            $sql = "INSERT INTO lecciones (curso_id, titulo, video_url, descripcion, orden) VALUES (?, ?, ?, ?, 0)";
            $conexion->prepare($sql)->execute([$id_curso, $titulo, $url, $desc]);
            $mensaje = "<div class='alert alert-success'>Lección creada correctamente.</div>";
        } elseif ($accion == 'actualizar') {
            // ACTUALIZAR (UPDATE)
            $id_lec = $_POST['id_leccion'];
            $sql = "UPDATE lecciones SET titulo=?, video_url=?, descripcion=? WHERE id=? AND curso_id=?";
            $conexion->prepare($sql)->execute([$titulo, $url, $desc, $id_lec, $id_curso]);
            $mensaje = "<div class='alert alert-success'>Lección actualizada correctamente.</div>";
            
            // Limpiar modo edición
            $leccion_a_editar = null; 
        }
    }
}

// 4. ELIMINAR
if (isset($_GET['borrar'])) {
    $id_leccion = $_GET['borrar'];
    $conexion->prepare("DELETE FROM lecciones WHERE id=? AND curso_id=?")->execute([$id_leccion, $id_curso]);
    header("Location: gestionar_lecciones.php?id=$id_curso");
    exit;
}

// 5. Listar lecciones existentes
$stmtLec = $conexion->prepare("SELECT * FROM lecciones WHERE curso_id = ? ORDER BY id ASC");
$stmtLec->execute([$id_curso]);
$lecciones = $stmtLec->fetchAll();
?>

<div class="container mt-4">
    <div class="d-flex align-items-center mb-4">
        <a href="mis_cursos.php" class="btn btn-outline-secondary me-3 rounded-circle">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="mb-0 fw-bold">Gestionar Contenido</h4>
            <small class="text-muted">Curso: <?php echo htmlspecialchars($curso['titulo']); ?></small>
        </div>
    </div>

    <?php echo $mensaje; ?>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 20px; z-index: 1;">
                <div class="card-header <?php echo $leccion_a_editar ? 'bg-warning text-dark' : 'bg-primary text-white'; ?>">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi <?php echo $leccion_a_editar ? 'bi-pencil-square' : 'bi-plus-circle'; ?>"></i> 
                        <?php echo $leccion_a_editar ? 'Editar Lección' : 'Nueva Lección'; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post">
                        <?php echo csrf_campo(); ?>
                        
                        <input type="hidden" name="accion" value="<?php echo $leccion_a_editar ? 'actualizar' : 'crear'; ?>">
                        <?php if($leccion_a_editar): ?>
                            <input type="hidden" name="id_leccion" value="<?php echo $leccion_a_editar['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Título</label>
                            <input type="text" name="titulo" class="form-control" required
                                   value="<?php echo $leccion_a_editar ? htmlspecialchars($leccion_a_editar['titulo']) : ''; ?>" 
                                   placeholder="Ej: Introducción...">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Video (YouTube)</label>
                            <input type="url" name="video_url" class="form-control" required
                                   value="<?php echo $leccion_a_editar ? htmlspecialchars($leccion_a_editar['video_url']) : ''; ?>"
                                   placeholder="https://youtube.com/...">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="3"><?php echo $leccion_a_editar ? htmlspecialchars($leccion_a_editar['descripcion']) : ''; ?></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn <?php echo $leccion_a_editar ? 'btn-warning' : 'btn-primary'; ?> fw-bold">
                                <?php echo $leccion_a_editar ? 'Guardar Cambios' : 'Agregar Lección'; ?>
                            </button>
                            
                            <?php if($leccion_a_editar): ?>
                                <a href="gestionar_lecciones.php?id=<?php echo $id_curso; ?>" class="btn btn-outline-secondary btn-sm">
                                    Cancelar Edición
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Lección</th>
                                    <th class="text-end pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($lecciones as $l): ?>
                                <tr class="<?php echo ($leccion_a_editar && $leccion_a_editar['id'] == $l['id']) ? 'table-warning' : ''; ?>">
                                    <td class="ps-4">
                                        <div class="fw-bold"><?php echo htmlspecialchars($l['titulo']); ?></div>
                                        <small class="text-muted text-truncate d-block" style="max-width: 250px;">
                                            <?php echo htmlspecialchars($l['descripcion']); ?>
                                        </small>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="gestionar_lecciones.php?id=<?php echo $id_curso; ?>&editar=<?php echo $l['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary me-1" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="gestionar_lecciones.php?id=<?php echo $id_curso; ?>&borrar=<?php echo $l['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('¿Eliminar esta lección?');" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </a>
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
</div>

<?php require_once '../../includes/footer.php'; ?>