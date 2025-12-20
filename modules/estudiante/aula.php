<?php
session_start();
require_once '../../config/bd.php';

// 1. Seguridad: Verificar Sesión y Rol (Admin y Estudiante)
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['rol_id'], [1, 3])) {
    header("Location: ../../index.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$rol_id = $_SESSION['rol_id'];
$id_curso = $_GET['id'] ?? 0;
$indice_actual = isset($_GET['indice']) ? (int)$_GET['indice'] : 0;

if (!$id_curso) {
    header("Location: dashboard.php");
    exit;
}

// 2. Verificar compra (Solo si es estudiante)
if ($rol_id == 3) {
    $check = $conexion->prepare("SELECT id FROM compras WHERE usuario_id = ? AND item_id = ? AND tipo_item = 'curso'");
    $check->execute([$usuario_id, $id_curso]);
    if ($check->rowCount() == 0) { 
        header("Location: ver_curso.php?id=" . $id_curso);
        exit;
    }
}

// 3. Obtener Datos del Curso y Lecciones
$stmt = $conexion->prepare("SELECT * FROM cursos WHERE id = ?");
$stmt->execute([$id_curso]);
$curso = $stmt->fetch();

if (!$curso) die("El curso no existe.");

$stmtLecciones = $conexion->prepare("SELECT * FROM lecciones WHERE curso_id = ? ORDER BY orden ASC, id ASC");
$stmtLecciones->execute([$id_curso]);
$lecciones = $stmtLecciones->fetchAll();

// 4. Determinar lección actual
$clase_actual = null;
if (!empty($lecciones)) {
    if (!isset($lecciones[$indice_actual])) { $indice_actual = 0; }
    $clase_actual = $lecciones[$indice_actual];
}
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
        body { 
            background-color: #0f111a; 
            color: #e0e0e0; 
            height: 100vh; 
            display: flex; 
            flex-direction: column; 
            overflow: hidden; /* El scroll lo manejamos dentro de las áreas */
        }
        
        /* Navbar */
        .aula-navbar { 
            background-color: #1a1c29; 
            border-bottom: 1px solid #2d2f40; 
            height: 60px; 
            display: flex; 
            align-items: center; 
            padding: 0 20px; 
            flex-shrink: 0;
        }
        
        /* Layout Principal */
        .main-layout { 
            flex: 1; 
            display: flex; 
            overflow: hidden; 
        }
        
        /* Área del Video (Izquierda) */
        .player-area { 
            flex: 1; 
            background: #000; 
            display: flex; 
            flex-direction: column; 
            overflow-y: auto; /* Scroll aquí */
        }

        /* Contenedor del Iframe para controlar el tamaño */
        .video-wrapper-limit {
            width: 100%;
            max-width: 1100px; /* Límite de ancho para que no sea gigante */
            margin: 0 auto;    /* Centrado horizontal */
            background: #000;
        }
        
        .iframe-container {
            position: relative;
            width: 100%;
            padding-top: 56.25%; /* 16:9 Aspect Ratio */
        }
        
        .iframe-container iframe { 
            position: absolute; 
            top: 0; 
            left: 0; 
            bottom: 0; 
            right: 0; 
            width: 100%; 
            height: 100%; 
            border: none; 
        }
        
        /* Sidebar Temario (Derecha) */
        .sidebar-area { 
            width: 350px; 
            background-color: #161821; 
            border-left: 1px solid #2d2f40; 
            display: flex; 
            flex-direction: column; 
            flex-shrink: 0;
        }
        .sidebar-header { padding: 15px; border-bottom: 1px solid #2d2f40; background: #1a1c29; }
        .sidebar-content { flex: 1; overflow-y: auto; }
        
        .lesson-item { 
            display: flex; align-items: center; padding: 15px; 
            border-bottom: 1px solid #222533; text-decoration: none; color: #a0a0a0; transition: 0.2s; 
        }
        .lesson-item:hover { background-color: #1f2130; color: #fff; }
        .lesson-item.active { background-color: #23263a; color: #3b82f6; border-left: 4px solid #3b82f6; }
        
        /* Responsive */
        @media (max-width: 992px) {
            .main-layout { flex-direction: column; overflow-y: auto; } /* En móvil el scroll es global */
            body { height: auto; overflow: auto; }
            .sidebar-area { width: 100%; height: auto; border-left: none; border-top: 1px solid #2d2f40; }
            .player-area { overflow: visible; height: auto; }
        }
    </style>
</head>
<body>

    <div class="aula-navbar justify-content-between">
        <div class="d-flex align-items-center">
            <a href="mis_compras.php" class="btn btn-outline-secondary border-0 text-white me-2"><i class="bi bi-arrow-left"></i></a>
            <span class="fw-bold text-truncate" style="max-width: 250px;"><?php echo htmlspecialchars($curso['titulo']); ?></span>
        </div>
        <div>
            <?php if(!empty($lecciones)): ?>
                <span class="badge bg-primary bg-opacity-25 text-primary border border-primary">
                    <?php echo $indice_actual + 1; ?> / <?php echo count($lecciones); ?>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <div class="main-layout">
        
        <div class="player-area">
            <?php if($clase_actual): ?>
                
                <div class="video-wrapper-limit">
                    <div class="iframe-container">
                        <iframe src="<?php echo htmlspecialchars($clase_actual['video_url']); ?>" allowfullscreen></iframe>
                    </div>
                </div>

                <div class="p-4 container-fluid" style="max-width: 1100px;">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h2 class="fw-bold text-white mb-0"><?php echo htmlspecialchars($clase_actual['titulo']); ?></h2>
                    </div>
                    
                    <div class="card bg-dark border-secondary border-opacity-25 mb-4">
                        <div class="card-body text-secondary">
                            <?php echo nl2br(htmlspecialchars($clase_actual['descripcion'])); ?>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mb-5">
                        <?php if($indice_actual > 0): ?>
                            <a href="aula.php?id=<?php echo $id_curso; ?>&indice=<?php echo $indice_actual - 1; ?>" class="btn btn-outline-secondary px-4">
                                <i class="bi bi-chevron-left"></i> Anterior
                            </a>
                        <?php else: ?>
                            <div></div>
                        <?php endif; ?>
                        
                        <?php if($indice_actual < count($lecciones) - 1): ?>
                            <a href="aula.php?id=<?php echo $id_curso; ?>&indice=<?php echo $indice_actual + 1; ?>" class="btn btn-primary px-4">
                                Siguiente <i class="bi bi-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

            <?php else: ?>
                <div class="d-flex flex-column align-items-center justify-content-center h-100 text-center p-5">
                    <i class="bi bi-cone-striped display-1 text-warning mb-4"></i>
                    <h3 class="fw-bold text-white">En construcción</h3>
                    <p class="text-muted">El docente aún no ha subido lecciones.</p>
                    <a href="mis_compras.php" class="btn btn-secondary mt-3">Volver</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="sidebar-area">
            <div class="sidebar-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-white"><i class="bi bi-collection-play"></i> Contenido</h6>
            </div>
            <div class="sidebar-content">
                <?php if(!empty($lecciones)): ?>
                    <?php foreach($lecciones as $index => $leccion): ?>
                        <a href="aula.php?id=<?php echo $id_curso; ?>&indice=<?php echo $index; ?>" 
                           class="lesson-item <?php echo ($index == $indice_actual) ? 'active' : ''; ?>">
                            <div class="me-3 fs-5">
                                <?php if($index == $indice_actual): ?>
                                    <i class="bi bi-play-circle-fill"></i>
                                <?php else: ?>
                                    <i class="bi bi-play-circle"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="small fw-bold lh-sm mb-1"><?php echo htmlspecialchars($leccion['titulo']); ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;">Lección <?php echo $index + 1; ?></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-4 text-center text-muted small">Sin contenido.</div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>