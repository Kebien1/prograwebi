<?php
session_start();
require_once '../../config/bd.php';

// 1. Seguridad básica
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 3) {
    header("Location: ../../index.php");
    exit;
}

// 2. Obtener Datos
$id_curso = $_GET['id'] ?? 0;
$indice_actual = isset($_GET['indice']) ? (int)$_GET['indice'] : 0;

// Verificar compra
$check = $conexion->prepare("SELECT id FROM compras WHERE usuario_id = ? AND item_id = ? AND tipo_item = 'curso'");
$check->execute([$_SESSION['usuario_id'], $id_curso]);
if ($check->rowCount() == 0) { die("Acceso denegado."); }

// Obtener Curso
$stmt = $conexion->prepare("SELECT * FROM cursos WHERE id = ?");
$stmt->execute([$id_curso]);
$curso = $stmt->fetch();

// Obtener Lecciones
$stmtLecciones = $conexion->prepare("SELECT * FROM lecciones WHERE curso_id = ? ORDER BY orden ASC");
$stmtLecciones->execute([$id_curso]);
$lecciones = $stmtLecciones->fetchAll();

if (!isset($lecciones[$indice_actual])) { $indice_actual = 0; }
$clase_actual = $lecciones[$indice_actual];
?>
<!doctype html>
<html lang="es" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($curso['titulo']); ?> - Aula Virtual</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #0f111a; color: #e0e0e0; overflow-x: hidden; }
        
        /* Navbar superior minimalista */
        .aula-navbar { background-color: #1a1c29; border-bottom: 1px solid #2d2f40; height: 60px; display: flex; align-items: center; padding: 0 20px; }
        
        /* Contenedor principal */
        .video-container { height: calc(100vh - 60px); display: flex; }
        
        /* Área del Video (Izquierda) */
        .player-area { flex: 1; background: #000; display: flex; flex-direction: column; justify-content: center; overflow-y: auto; }
        .iframe-wrapper { position: relative; width: 100%; padding-top: 56.25%; /* 16:9 Aspect Ratio */ }
        .iframe-wrapper iframe { position: absolute; top: 0; left: 0; bottom: 0; right: 0; width: 100%; height: 100%; border: none; }
        
        /* Sidebar Temario (Derecha) */
        .sidebar-area { width: 350px; background-color: #161821; border-left: 1px solid #2d2f40; display: flex; flex-direction: column; }
        .sidebar-header { padding: 15px; border-bottom: 1px solid #2d2f40; }
        .sidebar-content { flex: 1; overflow-y: auto; }
        
        /* Items del temario */
        .lesson-item { 
            display: flex; align-items: center; padding: 12px 15px; 
            border-bottom: 1px solid #222533; text-decoration: none; color: #a0a0a0; transition: 0.2s; 
        }
        .lesson-item:hover { background-color: #1f2130; color: #fff; }
        .lesson-item.active { background-color: #23263a; color: #3b82f6; border-left: 3px solid #3b82f6; }
        .lesson-icon { margin-right: 10px; font-size: 1.2rem; }
        
        /* Responsive */
        @media (max-width: 992px) {
            .video-container { flex-direction: column; height: auto; }
            .sidebar-area { width: 100%; height: 400px; }
            .player-area { height: auto; min-height: 300px; }
        }
    </style>
</head>
<body>

    <div class="aula-navbar justify-content-between">
        <div class="d-flex align-items-center">
            <a href="mis_compras.php" class="text-white text-decoration-none me-3"><i class="bi bi-arrow-left"></i></a>
            <span class="fw-bold d-none d-md-block"><?php echo htmlspecialchars($curso['titulo']); ?></span>
        </div>
        <div>
            <span class="badge bg-primary bg-opacity-25 text-primary border border-primary">Progreso: <?php echo $indice_actual + 1; ?>/<?php echo count($lecciones); ?></span>
        </div>
    </div>

    <div class="video-container">
        
        <div class="player-area">
            <?php if(!empty($lecciones)): ?>
                <div class="iframe-wrapper">
                    <iframe src="<?php echo htmlspecialchars($clase_actual['video_url']); ?>?rel=0&modestbranding=1" allowfullscreen></iframe>
                </div>
                <div class="p-4">
                    <h3 class="fw-bold text-white"><?php echo htmlspecialchars($clase_actual['titulo']); ?></h3>
                    <p class="text-secondary mt-2"><?php echo nl2br(htmlspecialchars($clase_actual['descripcion'])); ?></p>
                    
                    <div class="d-flex gap-2 mt-4">
                        <?php if($indice_actual > 0): ?>
                            <a href="aula.php?id=<?php echo $id_curso; ?>&indice=<?php echo $indice_actual - 1; ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-chevron-left"></i> Anterior
                            </a>
                        <?php endif; ?>
                        
                        <?php if($indice_actual < count($lecciones) - 1): ?>
                            <a href="aula.php?id=<?php echo $id_curso; ?>&indice=<?php echo $indice_actual + 1; ?>" class="btn btn-primary">
                                Siguiente <i class="bi bi-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center text-muted">Este curso aún no tiene contenido.</div>
            <?php endif; ?>
        </div>

        <div class="sidebar-area">
            <div class="sidebar-header">
                <h6 class="mb-0 fw-bold text-white"><i class="bi bi-collection-play"></i> Contenido del Curso</h6>
            </div>
            <div class="sidebar-content">
                <?php foreach($lecciones as $index => $leccion): ?>
                    <a href="aula.php?id=<?php echo $id_curso; ?>&indice=<?php echo $index; ?>" 
                       class="lesson-item <?php echo ($index == $indice_actual) ? 'active' : ''; ?>">
                        
                        <div class="lesson-icon">
                            <?php if($index == $indice_actual): ?>
                                <i class="bi bi-play-circle-fill"></i>
                            <?php else: ?>
                                <i class="bi bi-play-circle"></i>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex-grow-1">
                            <div class="small fw-bold lh-sm"><?php echo htmlspecialchars($leccion['titulo']); ?></div>
                            <div class="text-muted" style="font-size: 0.75rem;">Lección <?php echo $index + 1; ?></div>
                        </div>

                        <?php if($index == $indice_actual): ?>
                            <div class="spinner-grow spinner-grow-sm text-primary" role="status"></div>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>