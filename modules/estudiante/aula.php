<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(3); 
require_once '../../includes/header.php';

// 1. Validaciones iniciales
$id_curso = $_GET['id'] ?? 0;
if (!$id_curso) {
    echo "<script>window.location='mis_compras.php';</script>";
    exit;
}

// 2. Verificar compra
$sqlCheck = "SELECT id FROM compras WHERE usuario_id = ? AND item_id = ? AND tipo_item = 'curso'";
$stmtCheck = $conexion->prepare($sqlCheck);
$stmtCheck->execute([$_SESSION['usuario_id'], $id_curso]);

if ($stmtCheck->rowCount() == 0) {
    echo "<script>alert('Acceso denegado. Debes adquirir el curso primero.'); window.location='ver_curso.php?id=$id_curso';</script>";
    exit;
}

// 3. Obtener datos del curso
$stmt = $conexion->prepare("SELECT * FROM cursos WHERE id = ?");
$stmt->execute([$id_curso]);
$curso = $stmt->fetch();

if (!$curso) {
    echo "<div class='container mt-5 alert alert-danger'>El curso no existe.</div>";
    require_once '../../includes/footer.php';
    exit;
}

// 4. Obtener Lecciones
$sqlLecciones = "SELECT * FROM lecciones WHERE curso_id = ? ORDER BY orden ASC";
$stmtLecciones = $conexion->prepare($sqlLecciones);
$stmtLecciones->execute([$id_curso]);
$lecciones = $stmtLecciones->fetchAll();

// 5. NUEVO: Obtener Materiales de Apoyo del Docente
// Buscamos materiales subidos por el ID del docente dueño de este curso
$sqlMateriales = "SELECT * FROM materiales WHERE docente_id = ? ORDER BY id DESC";
$stmtMateriales = $conexion->prepare($sqlMateriales);
$stmtMateriales->execute([$curso['docente_id']]);
$materiales = $stmtMateriales->fetchAll();

// Control de índice de lección
if (empty($lecciones)) {
    echo "<div class='container mt-5'><div class='alert alert-info'>El docente aún no ha subido contenido.</div></div>";
    require_once '../../includes/footer.php';
    exit;
}

$indice_actual = isset($_GET['indice']) ? (int)$_GET['indice'] : 0;
if (!isset($lecciones[$indice_actual])) { $indice_actual = 0; }
$clase_actual = $lecciones[$indice_actual];
?>

<div class="container-fluid">
    <div class="row flex-nowrap">
        
        <div class="col-auto col-md-3 col-xl-2 px-0 bg-dark border-end border-secondary">
            <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-3 text-white min-vh-100">
                <span class="fs-5 d-none d-sm-inline fw-bold mb-3 text-warning">
                    <i class="bi bi-list-task"></i> Temario
                </span>
                <div class="w-100 overflow-auto" style="max-height: 80vh;">
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100">
                        <?php foreach($lecciones as $index => $leccion): ?>
                            <li class="nav-item w-100 mb-1">
                                <a href="aula.php?id=<?php echo $id_curso; ?>&indice=<?php echo $index; ?>" 
                                   class="nav-link px-2 align-middle text-white <?php echo ($index == $indice_actual) ? 'active bg-primary' : ''; ?>">
                                    <i class="bi <?php echo ($index == $indice_actual) ? 'bi-play-circle-fill' : 'bi-play-circle'; ?> me-2"></i>
                                    <span class="d-none d-sm-inline small"><?php echo htmlspecialchars($leccion['titulo']); ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <hr class="text-white w-100 mt-auto">
                <div class="pb-4 w-100">
                    <a href="mis_compras.php" class="btn btn-outline-light w-100 btn-sm"><i class="bi bi-arrow-left"></i> Salir</a>
                </div>
            </div>
        </div>

        <div class="col py-3 bg-light">
            <div class="container px-lg-5">
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="fw-bold text-dark mt-1"><?php echo htmlspecialchars($clase_actual['titulo']); ?></h2>
                    <span class="badge bg-primary rounded-pill px-3 py-2">
                        Clase <?php echo $indice_actual + 1; ?> / <?php echo count($lecciones); ?>
                    </span>
                </div>

                <div class="card border-0 shadow-lg mb-4 overflow-hidden">
                    <div class="ratio ratio-16x9 bg-black">
                        <iframe src="<?php echo htmlspecialchars($clase_actual['video_url']); ?>" allowfullscreen></iframe>
                    </div>
                    <div class="card-body p-4">
                        <h5 class="fw-bold">Descripción</h5>
                        <p class="card-text text-secondary"><?php echo nl2br(htmlspecialchars($clase_actual['descripcion'])); ?></p>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-5">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-success"><i class="bi bi-folder2-open"></i> Materiales de Apoyo del Docente</h5>
                    </div>
                    <div class="card-body">
                        <?php if(empty($materiales)): ?>
                            <p class="text-muted small mb-0">No hay archivos adjuntos para este curso.</p>
                        <?php else: ?>
                            <div class="row row-cols-1 row-cols-md-2 g-3">
                                <?php foreach($materiales as $mat): ?>
                                    <div class="col">
                                        <div class="d-flex align-items-center p-3 border rounded hover-bg-light">
                                            <div class="fs-1 text-danger me-3"><i class="bi bi-file-earmark-pdf-fill"></i></div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($mat['titulo']); ?></h6>
                                                <small class="text-muted">Subido: <?php echo date('d/m/Y', strtotime($mat['fecha_subida'] ?? 'now')); ?></small>
                                            </div>
                                            <a href="../../uploads/materiales/<?php echo $mat['archivo']; ?>" target="_blank" class="btn btn-sm btn-outline-success rounded-pill">
                                                <i class="bi bi-download"></i> Descargar
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <?php if($indice_actual > 0): ?>
                        <a href="aula.php?id=<?php echo $id_curso; ?>&indice=<?php echo $indice_actual - 1; ?>" class="btn btn-outline-secondary px-4">Anterior</a>
                    <?php else: ?>
                        <button class="btn btn-outline-secondary px-4" disabled>Inicio</button>
                    <?php endif; ?>

                    <?php if($indice_actual < count($lecciones) - 1): ?>
                        <a href="aula.php?id=<?php echo $id_curso; ?>&indice=<?php echo $indice_actual + 1; ?>" class="btn btn-primary px-4">Siguiente</a>
                    <?php else: ?>
                        <a href="mis_compras.php" class="btn btn-success px-4">Finalizar</a>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('contextmenu', event => event.preventDefault());
    document.onkeydown = function(e) {
        if(e.keyCode == 123 || (e.ctrlKey && e.shiftKey && e.keyCode == 73) || (e.ctrlKey && e.keyCode == 85)) return false;
    }
</script>
</body>
</html>