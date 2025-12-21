<?php
session_start();
require_once '../../config/bd.php';

// 1. Validaciones básicas
$id_curso = $_GET['id'] ?? 0;
$indice_leccion = $_GET['l'] ?? 0;

if (!$id_curso) { header("Location: ../../index.php"); exit; }

// 2. Obtener Curso y Usuario
$usuario_id = $_SESSION['usuario_id'] ?? 0;
$rol_id = $_SESSION['rol_id'] ?? 0;

$stmtCurso = $conexion->prepare("SELECT * FROM cursos WHERE id = ?");
$stmtCurso->execute([$id_curso]);
$curso = $stmtCurso->fetch();
if (!$curso) { echo "Curso no encontrado."; exit; }

// 3. Obtener Lecciones
$stmtLecciones = $conexion->prepare("SELECT * FROM lecciones WHERE curso_id = ? ORDER BY id ASC");
$stmtLecciones->execute([$id_curso]);
$lecciones = $stmtLecciones->fetchAll();

if (count($lecciones) == 0) { echo "Este curso aún no tiene lecciones."; exit; }

// Validar índice actual
if (!isset($lecciones[$indice_leccion])) { $indice_leccion = 0; }
$leccionActual = $lecciones[$indice_leccion];

// 4. Lógica de Permisos
$acceso_total = false;
$puede_ver_video_actual = false;

if ($rol_id == 1 || $rol_id == 2) {
    $acceso_total = true; // Admin/Profe
} elseif ($usuario_id > 0) {
    // Estudiante: Verificar compra
    $stmtCompra = $conexion->prepare("SELECT id FROM compras WHERE usuario_id = ? AND item_id = ? AND tipo_item = 'curso'");
    $stmtCompra->execute([$usuario_id, $id_curso]);
    if ($stmtCompra->rowCount() > 0) $acceso_total = true;
}

if ($acceso_total || $leccionActual['es_gratis'] == 1) {
    $puede_ver_video_actual = true;
}

// 5. CORRECCIÓN: Obtener Recursos de la lección actual desde la tabla 'materiales'
$recursos = [];
if (isset($leccionActual['id'])) {
    try {
        // CORREGIDO: Tabla 'materiales' en lugar de 'recursos'
        $stmtRec = $conexion->prepare("SELECT * FROM materiales WHERE leccion_id = ?");
        $stmtRec->execute([$leccionActual['id']]);
        $recursos = $stmtRec->fetchAll();
    } catch (Exception $e) {
        $recursos = [];
    }
}

require_once '../../includes/header.php'; 
?>

<style>
    /* Área del Video */
    .video-area {
        background-color: #000;
        min-height: 450px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Sidebar Temario (Modo Claro) */
    .sidebar-curso {
        background-color: #fff;
        border-left: 1px solid #dee2e6;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .list-group-item.active {
        background-color: #e7f1ff;
        color: #0d6efd;
        font-weight: bold;
        border-left: 4px solid #0d6efd;
    }
    
    .item-seccion {
        background-color: #f1f3f5;
        color: #495057;
        font-weight: 800;
        text-transform: uppercase;
        font-size: 0.8rem;
        padding: 0.75rem 1rem;
        letter-spacing: 1px;
        border-bottom: 1px solid #e9ecef;
    }
</style>

<div class="container-fluid p-0">
    <div class="row g-0">
        
        <div class="col-lg-9">
            <div class="video-area position-relative">
                <?php 
                    $esSeccionTitulo = (stripos($leccionActual['titulo'], 'SECCIÓN:') !== false || stripos($leccionActual['titulo'], 'MODULO:') !== false);
                ?>

                <?php if ($esSeccionTitulo): ?>
                    <div class="text-center text-white">
                        <h3><?php echo htmlspecialchars($leccionActual['titulo']); ?></h3>
                        <p class="text-muted">Selecciona una clase para continuar.</p>
                    </div>

                <?php elseif ($puede_ver_video_actual): ?>
                    <div class="ratio ratio-16x9 w-100 h-100" style="max-height: 85vh;">
                         <?php 
                            $url = $leccionActual['video_url']; 
                            $esYoutube = (stripos($url, 'youtube.com') !== false || stripos($url, 'youtu.be') !== false);
                        ?>

                        <?php if($esYoutube): ?>
                            <?php
                                $videoId = '';
                                $patron = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';
                                if(preg_match($patron, $url, $match)) {
                                    $videoId = $match[1];
                                }
                            ?>
                            <iframe src="https://www.youtube.com/embed/<?php echo $videoId; ?>?rel=0&modestbranding=1" title="Video de la clase" allowfullscreen></iframe>
                        <?php else: ?>
                            <video src="<?php echo $url; ?>" controls controlsList="nodownload"></video>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <div class="text-center text-white p-5">
                        <i class="bi bi-lock-fill display-1 text-secondary mb-3"></i>
                        <h3 class="fw-bold">Clase Bloqueada</h3>
                        <p class="mb-4">Este contenido es exclusivo para estudiantes inscritos.</p>
                        <?php if(!$acceso_total): ?>
                            <a href="ver_curso.php?id=<?php echo $id_curso; ?>" class="btn btn-success fw-bold">Comprar Curso</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if(!$esSeccionTitulo): ?>
            <div class="p-4 bg-white">
                <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc" type="button">Descripción</button>
                    </li>
                    <?php if(count($recursos) > 0 && $puede_ver_video_actual): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" id="recursos-tab" data-bs-toggle="tab" data-bs-target="#recursos" type="button">
                            Recursos <span class="badge bg-secondary ms-1"><?php echo count($recursos); ?></span>
                        </button>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="desc" role="tabpanel">
                        <h2 class="h4 fw-bold mb-3"><?php echo htmlspecialchars($leccionActual['titulo']); ?></h2>
                        <div class="text-secondary lh-lg">
                            <?php echo nl2br(htmlspecialchars($leccionActual['descripcion'])); ?>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="recursos" role="tabpanel">
                        <h5 class="fw-bold mb-3">Material de descarga</h5>
                        <div class="list-group">
                            <?php foreach($recursos as $rec): ?>
                                <?php 
                                    // CORREGIDO: Ruta apuntando a 'uploads/materiales/'
                                    $rutaArchivo = "../../uploads/materiales/" . $rec['archivo']; 
                                ?>
                                <a href="<?php echo $rutaArchivo; ?>" target="_blank" class="list-group-item list-group-item-action d-flex align-items-center">
                                    <div class="bg-light p-2 rounded me-3 text-danger">
                                        <i class="bi bi-file-earmark-pdf-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($rec['titulo'] ?? 'Archivo adjunto'); ?></h6>
                                        <small class="text-muted">Clic para descargar</small>
                                    </div>
                                    <i class="bi bi-download ms-auto text-muted"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-3 d-none d-lg-block border-start">
            <div class="sidebar-curso shadow-sm" style="max-height: 100vh; overflow-y: auto;">
                
                <div class="p-3 bg-light border-bottom sticky-top">
                    <div class="d-flex justify-content-between mb-2">
                        <small class="text-muted fw-bold text-uppercase">Temario</small>
                        <a href="ver_curso.php?id=<?php echo $id_curso; ?>" class="text-decoration-none small">Salir</a>
                    </div>
                    <h6 class="fw-bold mb-0 text-truncate"><?php echo htmlspecialchars($curso['titulo']); ?></h6>
                </div>

                <div class="list-group list-group-flush">
                    <?php foreach ($lecciones as $index => $leccion): ?>
                        <?php 
                            $esSeccion = (stripos($leccion['titulo'], 'SECCIÓN:') !== false || stripos($leccion['titulo'], 'MODULO:') !== false);

                            if ($esSeccion): 
                        ?>
                            <div class="item-seccion">
                                <?php echo str_ireplace(['SECCIÓN:', 'MODULO:'], '', htmlspecialchars($leccion['titulo'])); ?>
                            </div>

                        <?php else: 
                                $isActive = ($index == $indice_leccion);
                                $isLocked = !($acceso_total || $leccion['es_gratis'] == 1);
                                $link = $isLocked ? '#' : "?id=$id_curso&l=$index";
                                $claseItem = $isActive ? 'active' : ($isLocked ? 'disabled bg-light' : '');
                        ?>
                            <a href="<?php echo $link; ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-2 <?php echo $claseItem; ?>">
                                <?php if($isLocked): ?>
                                    <i class="bi bi-lock-fill text-muted"></i>
                                <?php elseif($isActive): ?>
                                    <i class="bi bi-play-circle-fill"></i>
                                <?php else: ?>
                                    <i class="bi bi-play-circle text-secondary"></i>
                                <?php endif; ?>

                                <span class="small fw-bold lh-sm w-100">
                                    <?php echo htmlspecialchars($leccion['titulo']); ?>
                                </span>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>