<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(3); 
require_once '../../includes/header.php';

$id_curso = $_GET['id'] ?? 0;

if (!$id_curso) {
    echo "<script>window.location='mis_compras.php';</script>";
    exit;
}

$sqlCheck = "SELECT id FROM compras WHERE usuario_id = ? AND item_id = ? AND tipo_item = 'curso'";
$stmtCheck = $conexion->prepare($sqlCheck);
$stmtCheck->execute([$_SESSION['usuario_id'], $id_curso]);

if ($stmtCheck->rowCount() == 0) {
    echo "<script>alert('Acceso denegado. Debes adquirir el curso primero.'); window.location='ver_curso.php?id=$id_curso';</script>";
    exit;
}

$stmt = $conexion->prepare("SELECT * FROM cursos WHERE id = ?");
$stmt->execute([$id_curso]);
$curso = $stmt->fetch();

if (!$curso) {
    echo "<div class='container mt-5 alert alert-danger'>El curso no existe.</div>";
    require_once '../../includes/footer.php';
    exit;
}

$sqlLecciones = "SELECT * FROM lecciones WHERE curso_id = ? ORDER BY orden ASC";
$stmtLecciones = $conexion->prepare($sqlLecciones);
$stmtLecciones->execute([$id_curso]);
$lecciones = $stmtLecciones->fetchAll();

if (empty($lecciones)) {
    echo "<div class='container mt-5'>
            <div class='alert alert-info shadow-sm'>
                <h4 class='alert-heading'><i class='bi bi-cone-striped'></i> Curso en construcción</h4>
                <p>El docente aún no ha subido contenido a este curso. Por favor vuelve más tarde.</p>
                <a href='mis_compras.php' class='btn btn-outline-primary'>Volver a mis cursos</a>
            </div>
          </div>";
    require_once '../../includes/footer.php';
    exit;
}

$indice_actual = isset($_GET['indice']) ? (int)$_GET['indice'] : 0;
if (!isset($lecciones[$indice_actual])) {
    $indice_actual = 0;
}
$clase_actual = $lecciones[$indice_actual];
?>

<div class="container-fluid" id="aula-container">
    <div class="row flex-nowrap">
        
        <div class="col-auto col-md-3 col-xl-2 px-0 bg-dark border-end border-secondary">
            <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-3 text-white min-vh-100">
                <span class="fs-5 d-none d-sm-inline fw-bold mb-3 text-warning">
                    <i class="bi bi-list-task"></i> Temario
                </span>
                
                <div class="w-100 overflow-auto" style="max-height: 80vh;">
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
                        <?php foreach($lecciones as $index => $leccion): ?>
                            <li class="nav-item w-100 mb-1">
                                <a href="aula.php?id=<?php echo $id_curso; ?>&indice=<?php echo $index; ?>" 
                                   class="nav-link px-2 align-middle text-white <?php echo ($index == $indice_actual) ? 'active bg-primary' : ''; ?>">
                                    <div class="d-flex align-items-center">
                                        <i class="bi <?php echo ($index == $indice_actual) ? 'bi-play-circle-fill' : 'bi-play-circle'; ?> fs-5 me-2"></i> 
                                        <span class="d-none d-sm-inline small text-truncate">
                                            <?php echo htmlspecialchars($leccion['titulo']); ?>
                                        </span>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <hr class="text-white w-100 mt-auto">
                <div class="pb-4 w-100">
                    <a href="mis_compras.php" class="btn btn-outline-light w-100 btn-sm d-flex align-items-center justify-content-center">
                        <i class="bi bi-arrow-left me-2"></i> <span class="d-none d-sm-inline">Salir</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="col py-3 bg-light">
            <div class="container px-lg-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <small class="text-muted text-uppercase fw-bold"><?php echo htmlspecialchars($curso['titulo']); ?></small>
                        <h2 class="fw-bold text-dark mt-1"><?php echo htmlspecialchars($clase_actual['titulo']); ?></h2>
                    </div>
                    <span class="badge bg-primary rounded-pill px-3 py-2">
                        Clase <?php echo $indice_actual + 1; ?> de <?php echo count($lecciones); ?>
                    </span>
                </div>

                <div class="card border-0 shadow-lg mb-4 overflow-hidden">
                    <div class="ratio ratio-16x9 bg-black">
                        <iframe src="<?php echo htmlspecialchars($clase_actual['video_url']); ?>" 
                                title="Reproductor de video" 
                                allowfullscreen 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                        </iframe>
                    </div>
                    <div class="card-body p-4">
                        <h5 class="fw-bold"><i class="bi bi-card-text me-2"></i>Descripción de la clase</h5>
                        <p class="card-text text-secondary lh-lg">
                            <?php echo nl2br(htmlspecialchars($clase_actual['descripcion'])); ?>
                        </p>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-5">
                    <?php if($indice_actual > 0): ?>
                        <a href="aula.php?id=<?php echo $id_curso; ?>&indice=<?php echo $indice_actual - 1; ?>" class="btn btn-outline-secondary px-4">
                            <i class="bi bi-arrow-left me-2"></i> Clase Anterior
                        </a>
                    <?php else: ?>
                        <button class="btn btn-outline-secondary px-4" disabled>
                            <i class="bi bi-arrow-left me-2"></i> Inicio
                        </button>
                    <?php endif; ?>

                    <?php if($indice_actual < count($lecciones) - 1): ?>
                        <a href="aula.php?id=<?php echo $id_curso; ?>&indice=<?php echo $indice_actual + 1; ?>" class="btn btn-primary px-4 fw-bold">
                            Siguiente Clase <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    <?php else: ?>
                        <a href="mis_compras.php" class="btn btn-success px-4 fw-bold">
                            <i class="bi bi-trophy-fill me-2"></i> Finalizar Curso
                        </a>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // 1. Bloquear Clic Derecho
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });

    // 2. Bloquear Teclas de Inspección (F12, Ctrl+U, Ctrl+Shift+I)
    document.onkeydown = function(e) {
        if(e.keyCode == 123) { return false; } // F12
        if(e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)) { return false; } // Ctrl+Shift+I
        if(e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)) { return false; } // Ctrl+Shift+J
        if(e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) { return false; } // Ctrl+U
    }
</script>

</body>
</html>