<?php
session_start();
require_once '../../config/bd.php';

// 1. Validaciones básicas
$id_curso = $_GET['id'] ?? 0;
// NOTA: Si usas secciones, el índice puede cambiar, así que buscamos la primera lección real si l=0
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

// Permiso para el video actual
if ($acceso_total) {
    $puede_ver_video_actual = true;
} elseif ($leccionActual['es_gratis'] == 1) {
    $puede_ver_video_actual = true;
}

// DETECTAR SI LA LECCIÓN ACTUAL ES SOLO UNA SECCIÓN (Título)
// Si el usuario intenta ver una "Sección" (que no tiene video), saltamos a la siguiente lección real.
if (stripos($leccionActual['titulo'], 'SECCIÓN:') !== false || stripos($leccionActual['titulo'], 'MODULO:') !== false) {
    // Es un título, no un video. No mostramos nada o redirigimos si es necesario.
    // (Visualmente se manejará abajo, aquí solo evitamos errores de lógica si fuera necesario)
}

// Navegación (Anterior / Siguiente)
// Lógica simple: +/- 1, pero saltando las "Secciones" si caen en ellas sería lo ideal. 
// Para mantenerlo simple y funcional, dejaremos la navegación lineal por ahora.
$indice_ant = ($indice_leccion > 0) ? $indice_leccion - 1 : null;
$indice_sig = ($indice_leccion < count($lecciones) - 1) ? $indice_leccion + 1 : null;

require_once '../../includes/header.php'; 
?>

<style>
    /* Área del Video (Siempre oscura para resaltar el contenido) */
    .video-area {
        background-color: #000;
        min-height: 500px;
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

    .sidebar-header {
        background-color: #f8f9fa;
        padding: 1rem;
        border-bottom: 1px solid #dee2e6;
    }

    /* Items de la lista */
    .list-group-item {
        border: none;
        border-bottom: 1px solid #f1f1f1;
        padding: 0.8rem 1rem;
        color: #495057;
        transition: all 0.2s;
    }

    .list-group-item:hover {
        background-color: #f8f9fa;
        color: #0d6efd; /* Azul Bootstrap */
    }

    /* Item Activo */
    .list-group-item.active {
        background-color: #e7f1ff; /* Azul muy clarito */
        color: #0d6efd;
        font-weight: bold;
        border-left: 4px solid #0d6efd; /* Borde indicador a la izquierda */
    }

    /* Item Bloqueado */
    .list-group-item.disabled {
        background-color: #fff;
        color: #adb5bd;
        opacity: 0.6;
    }

    /* TÍTULOS DE SECCIÓN (El truco) */
    .item-seccion {
        background-color: #f1f3f5; /* Gris suave */
        color: #212529;
        font-weight: 800;
        text-transform: uppercase;
        font-size: 0.85rem;
        padding: 1rem;
        letter-spacing: 1px;
        border-bottom: 2px solid #e9ecef;
        margin-top: 10px;
        pointer-events: none; /* No clickeable */
    }
</style>

<div class="container-fluid p-0">
    <div class="row g-0">
        
        <div class="col-lg-9 bg-dark">
            <div class="video-area position-relative">
                
                <?php 
                    // Verificamos si es una SECCIÓN (Título) o un VIDEO real
                    $esSeccionTitulo = (stripos($leccionActual['titulo'], 'SECCIÓN:') !== false || stripos($leccionActual['titulo'], 'MODULO:') !== false);
                ?>

                <?php if ($esSeccionTitulo): ?>
                    <div class="text-center text-white">
                        <h3><?php echo htmlspecialchars($leccionActual['titulo']); ?></h3>
                        <p class="text-muted">Selecciona una clase de la lista para comenzar.</p>
                    </div>

                <?php elseif ($puede_ver_video_actual): ?>
                    <div class="ratio ratio-16x9 w-100 h-100" style="max-height: 85vh;">
                         <?php 
                            $url = $leccionActual['url_video'];
                            $esYoutube = (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false);
                        ?>

                        <?php if($esYoutube): ?>
                            <?php
                                $videoId = '';
                                if(preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
                                    $videoId = $match[1];
                                }
                            ?>
                            <iframe src="https://www.youtube.com/embed/<?php echo $videoId; ?>?rel=0&modestbranding=1" allowfullscreen></iframe>
                        <?php else: ?>
                            <video src="<?php echo $url; ?>" controls controlsList="nodownload"></video>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <div class="text-center text-white p-5">
                        <i class="bi bi-lock-fill display-1 text-secondary mb-3"></i>
                        <h3 class="fw-bold">Clase Bloqueada</h3>
                        <p class="mb-4">Adquiere el curso para acceder a este contenido.</p>
                        <?php if(!$usuario_id): ?>
                             <a href="../../modules/auth/login.php" class="btn btn-primary">Iniciar Sesión</a>
                        <?php elseif(!$acceso_total): ?>
                            <a href="ver_curso.php?id=<?php echo $id_curso; ?>" class="btn btn-success fw-bold">Comprar Curso Completo</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if(!$esSeccionTitulo): ?>
            <div class="p-4 bg-white">
                <h2 class="fw-bold"><?php echo htmlspecialchars($leccionActual['titulo']); ?></h2>
                <p class="text-secondary mt-3"><?php echo nl2br(htmlspecialchars($leccionActual['descripcion'])); ?></p>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-3 d-none d-lg-block">
            <div class="sidebar-curso shadow-sm" style="max-height: 100vh; overflow-y: auto;">
                
                <div class="sidebar-header sticky-top">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted fw-bold text-uppercase">Contenido del curso</small>
                        <a href="ver_curso.php?id=<?php echo $id_curso; ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i> Salir</a>
                    </div>
                    <h6 class="fw-bold mb-0 text-truncate"><?php echo htmlspecialchars($curso['titulo']); ?></h6>
                    <div class="progress mt-2" style="height: 4px;">
                        <?php 
                            // Cálculo simple de progreso
                            $progreso = ($indice_leccion + 1) / count($lecciones) * 100; 
                        ?>
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $progreso; ?>%"></div>
                    </div>
                </div>

                <div class="list-group list-group-flush">
                    <?php foreach ($lecciones as $index => $leccion): ?>
                        <?php 
                            // 1. DETECTAR SI ES SECCIÓN (Truco)
                            $esSeccion = (stripos($leccion['titulo'], 'SECCIÓN:') !== false || stripos($leccion['titulo'], 'MODULO:') !== false);

                            if ($esSeccion): 
                                // RENDERIZAR COMO TÍTULO SEPARADOR
                        ?>
                            <div class="item-seccion">
                                <?php 
                                    // Limpiamos la palabra "SECCIÓN:" para mostrar solo el nombre
                                    echo str_ireplace(['SECCIÓN:', 'MODULO:', 'SECCION:'], '', htmlspecialchars($leccion['titulo'])); 
                                ?>
                            </div>

                        <?php else: 
                                // RENDERIZAR COMO LECCIÓN NORMAL
                                $isActive = ($index == $indice_leccion);
                                $isLocked = !($acceso_total || $leccion['es_gratis'] == 1);
                                
                                $link = $isLocked ? '#' : "?id=$id_curso&l=$index";
                                $claseItem = $isActive ? 'active' : ($isLocked ? 'disabled' : '');
                        ?>
                            <a href="<?php echo $link; ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-3 <?php echo $claseItem; ?>">
                                
                                <?php if($isLocked): ?>
                                    <i class="bi bi-lock-fill text-muted"></i>
                                <?php elseif($isActive): ?>
                                    <i class="bi bi-play-circle-fill"></i>
                                <?php else: ?>
                                    <i class="bi bi-play-circle text-secondary"></i>
                                <?php endif; ?>

                                <div class="w-100">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <span class="lh-sm small fw-bold">
                                            <?php echo htmlspecialchars($leccion['titulo']); ?>
                                        </span>
                                    </div>
                                    <?php if($leccion['es_gratis'] == 1 && !$acceso_total): ?>
                                        <span class="badge bg-success mt-1" style="font-size: 0.6rem;">Gratis</span>
                                    <?php endif; ?>
                                </div>
                            </a>

                        <?php endif; // Fin if/else seccion ?>
                    <?php endforeach; ?>
                </div>

            </div>
        </div>

    </div>
</div>

<div class="d-lg-none fixed-bottom bg-white border-top p-2 d-flex justify-content-between align-items-center shadow-lg">
    <span class="small fw-bold ms-2">Clase <?php echo $indice_leccion + 1; ?></span>
    <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasTemario">
        <i class="bi bi-list"></i> Ver Temario
    </button>
</div>

<div class="offcanvas offcanvas-bottom h-75" tabindex="-1" id="offcanvasTemario">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Temario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div class="list-group list-group-flush">
             <?php foreach ($lecciones as $index => $leccion): ?>
                <?php 
                   $esSeccion = (stripos($leccion['titulo'], 'SECCIÓN:') !== false);
                   if($esSeccion):
                ?>
                    <div class="bg-light p-3 fw-bold border-bottom"><?php echo htmlspecialchars($leccion['titulo']); ?></div>
                <?php else: 
                    $link = "?id=$id_curso&l=$index";
                ?>
                    <a href="<?php echo $link; ?>" class="list-group-item list-group-item-action p-3">
                        <?php echo ($index+1) . ". " . htmlspecialchars($leccion['titulo']); ?>
                    </a>
                <?php endif; ?>
             <?php endforeach; ?>
        </div>
    </div>
</div>