<?php
session_start();
require_once '../../config/bd.php';

// 1. Validaciones básicas de seguridad
$id_curso = $_GET['id'] ?? 0;
$indice_leccion = $_GET['l'] ?? 0; // Índice de la lección actual (empieza en 0)

if (!$id_curso) {
    header("Location: ../../index.php");
    exit;
}

// Validar si el usuario está logueado
$usuario_id = $_SESSION['usuario_id'] ?? 0;
$rol_id = $_SESSION['rol_id'] ?? 0;

// 2. Obtener datos del curso
$stmtCurso = $conexion->prepare("SELECT * FROM cursos WHERE id = ?");
$stmtCurso->execute([$id_curso]);
$curso = $stmtCurso->fetch();

if (!$curso) { echo "Curso no encontrado."; exit; }

// 3. Obtener TODAS las lecciones del curso
$stmtLecciones = $conexion->prepare("SELECT * FROM lecciones WHERE curso_id = ? ORDER BY id ASC");
$stmtLecciones->execute([$id_curso]);
$lecciones = $stmtLecciones->fetchAll();

if (count($lecciones) == 0) { echo "Este curso aún no tiene lecciones."; exit; }

// Validar que el índice solicitado exista
if (!isset($lecciones[$indice_leccion])) {
    $indice_leccion = 0; // Si el índice no existe, volver al primero
}
$leccionActual = $lecciones[$indice_leccion];


// 4. Lógica de Permisos (CRUCIAL)
$acceso_total = false; // ¿Compró el curso?
$puede_ver_video_actual = false; // ¿Puede ver ESTE video específico?

if ($rol_id == 1 || $rol_id == 2) {
    // Admin y Docentes siempre tienen acceso total
    $acceso_total = true;
    $puede_ver_video_actual = true;
} elseif ($usuario_id > 0) {
    // Estudiantes: Verificar compra
    $stmtCompra = $conexion->prepare("SELECT id FROM compras WHERE usuario_id = ? AND item_id = ? AND tipo_item = 'curso'");
    $stmtCompra->execute([$usuario_id, $id_curso]);
    if ($stmtCompra->rowCount() > 0) {
        $acceso_total = true;
    }
}

// Si tiene acceso total, puede ver cualquier video.
// Si NO tiene acceso total, solo puede ver si el video actual es gratis.
if ($acceso_total) {
    $puede_ver_video_actual = true;
} elseif ($leccionActual['es_gratis'] == 1) {
    $puede_ver_video_actual = true;
}

// Indices para navegación anterior/siguiente
$indice_ant = ($indice_leccion > 0) ? $indice_leccion - 1 : null;
$indice_sig = ($indice_leccion < count($lecciones) - 1) ? $indice_leccion + 1 : null;

require_once '../../includes/header.php'; 
?>

<style>
    /* Contenedor principal del sidebar oscuro */
    .sidebar-oscuro {
        background-color: #121212; /* Fondo muy oscuro, casi negro */
        color: #e0e0e0; /* Texto claro */
    }
    
    /* Cabecera del sidebar */
    .sidebar-oscuro-header {
        background-color: #1a1a1a;
        border-bottom: 1px solid #333;
        padding: 1.5rem;
    }

    /* Items de la lista normal */
    .sidebar-oscuro .list-group-item {
        background-color: transparent; /* Hereda el fondo oscuro */
        border: none;
        border-bottom: 1px solid #2a2a2a; /* Separador sutil */
        color: #b3b3b3; /* Texto gris claro para items no activos */
        transition: all 0.2s ease;
    }
    
    /* Hover sobre los items */
    .sidebar-oscuro .list-group-item-action:hover {
        background-color: #2a2a2a;
        color: #fff;
    }

    /* Item ACTIVO (el video que se está viendo) */
    .sidebar-oscuro .list-group-item.active {
        background-color: #3700b3; /* Un tono morado/azul oscuro profesional (puedes cambiarlo por tu color primario) */
        color: #fff;
        border-color: transparent;
        font-weight: bold;
    }

    /* Items BLOQUEADOS (candado) */
    .sidebar-oscuro .list-group-item.disabled {
        opacity: 0.5;
        background-color: transparent;
        color: #777;
        cursor: not-allowed;
    }
</style>


<div class="container-fluid p-0 bg-light overflow-hidden" style="height: calc(100vh - 60px);"> <div class="row g-0 h-100">
        
        <div class="col-md-8 col-lg-9 bg-black p-0 d-flex flex-column align-items-center justify-content-center" style="min-height: 300px;">
            <?php if ($puede_ver_video_actual): ?>
                <div class="ratio ratio-16x9 w-100" style="max-height: 100%;">
                     <?php 
                        // Función auxiliar simple para detectar tipo de video (YouTube vs local)
                        $url = $leccionActual['url_video'];
                        $esYoutube = (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false);
                    ?>

                    <?php if($esYoutube): ?>
                        <?php
                            // Extraer ID de YouTube de forma sencilla
                            $videoId = '';
                            if(preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
                                $videoId = $match[1];
                            }
                        ?>
                        <iframe src="https://www.youtube.com/embed/<?php echo $videoId; ?>?rel=0&modestbranding=1" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                    <?php else: ?>
                        <video src="<?php echo $url; ?>" controls controlsList="nodownload" class="w-100 h-100" style="object-fit: contain;"></video>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                 <div class="text-center text-white p-5">
                    <i class="bi bi-lock-fill display-1 text-secondary mb-4"></i>
                    <h2 class="fw-bold">Contenido Bloqueado</h2>
                    <p class="lead mb-4">Esta lección es parte del contenido premium del curso.</p>
                    <?php if(!$usuario_id): ?>
                         <a href="../../modules/auth/login.php" class="btn btn-primary btn-lg">Inicia Sesión para Comprar</a>
                    <?php elseif($esEstudiante && !$acceso_total): ?>
                        <a href="ver_curso.php?id=<?php echo $id_curso; ?>" class="btn btn-success btn-lg fw-bold">
                            <i class="bi bi-cart-fill"></i> Comprar Curso Completo
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-md-4 col-lg-3 sidebar-oscuro overflow-auto h-100 shadow-lg">
            
            <div class="sidebar-oscuro-header d-flex justify-content-between align-items-center sticky-top">
                <h5 class="mb-0 fw-bold">Contenido del Curso</h5>
                <a href="ver_curso.php?id=<?php echo $id_curso; ?>" class="text-decoration-none text-muted small">
                    <i class="bi bi-arrow-left"></i> Volver info
                </a>
            </div>

            <div class="p-3 bg-dark border-bottom border-secondary d-md-none">
                 <span class="badge bg-primary mb-2">Reproduciendo ahora:</span>
                 <h6 class="text-white mb-0"><?php echo htmlspecialchars($leccionActual['titulo']); ?></h6>
            </div>

            <div class="list-group list-group-flush">
                <?php foreach ($lecciones as $index => $leccion): ?>
                    <?php 
                        $isActive = ($index == $indice_leccion);
                        $isLocked = !($acceso_total || $leccion['es_gratis'] == 1);
                        
                        // Determinamos el estado y el icono
                        $itemClass = $isActive ? 'active' : '';
                        if ($isLocked) $itemClass .= ' disabled';

                        $icono = $isLocked ? 'bi bi-lock-fill' : ($isActive ? 'bi bi-play-fill' : 'bi bi-play-circle');
                        $colorIcono = $isLocked ? 'text-muted' : ($isActive ? 'text-white' : 'text-secondary');
                        if(!$isLocked && $leccion['es_gratis']) $colorIcono = 'text-success'; // Icono verde si es gratis y no está bloqueado
                    ?>
                    
                    <a href="<?php echo $isLocked ? '#' : '?id='.$id_curso.'&l='.$index; ?>" 
                       class="list-group-item list-group-item-action d-flex align-items-center p-3 <?php echo $itemClass; ?>"
                       <?php echo $isLocked ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
                        
                        <i class="<?php echo $icono . ' ' . $colorIcono; ?> fs-5 me-3"></i>
                        
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <span class="leccion-titulo lh-sm">
                                    <?php echo $index + 1 . ". " . htmlspecialchars($leccion['titulo']); ?>
                                </span>
                                <?php if(!$acceso_total && $leccion['es_gratis'] == 1 && !$isActive): ?>
                                    <span class="badge bg-success ms-2 rounded-pill" style="font-size: 0.7rem;">Gratis</span>
                                <?php endif; ?>
                            </div>
                            </div>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <div class="p-3 mt-auto border-top border-secondary sticky-bottom" style="background-color: #1a1a1a;">
                 <div class="d-flex justify-content-between gap-2">
                    <?php if ($indice_ant !== null): ?>
                        <a href="?id=<?php echo $id_curso; ?>&l=<?php echo $indice_ant; ?>" class="btn btn-outline-light btn-sm flex-grow-1">
                            <i class="bi bi-chevron-left"></i> Anterior
                        </a>
                    <?php else: ?>
                        <button class="btn btn-outline-secondary btn-sm flex-grow-1" disabled><i class="bi bi-chevron-left"></i> Anterior</button>
                    <?php endif; ?>

                    <?php if ($indice_sig !== null): ?>
                        <a href="?id=<?php echo $id_curso; ?>&l=<?php echo $indice_sig; ?>" class="btn btn-primary btn-sm flex-grow-1 fw-bold">
                            Siguiente <i class="bi bi-chevron-right"></i>
                        </a>
                    <?php else: ?>
                         <button class="btn btn-outline-secondary btn-sm flex-grow-1" disabled>Siguiente <i class="bi bi-chevron-right"></i></button>
                    <?php endif; ?>
                </div>
            </div>
            
        </div> </div>
</div>

<?php 
// Nota: No incluimos el footer estándar en el aula para maximizar el espacio
// require_once '../../includes/footer_admin.php'; 
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>