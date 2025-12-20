<?php
session_start();
require_once '../../config/bd.php';

// 1. Seguridad básica
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['rol_id'], [1, 3])) {
    header("Location: ../../index.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$rol_id = $_SESSION['rol_id'];
$id_curso = $_GET['id'] ?? 0;
$indice_actual = isset($_GET['indice']) ? (int)$_GET['indice'] : 0;

if (!$id_curso) { header("Location: dashboard.php"); exit; }

// 2. Verificar compra (Solo si es estudiante)
if ($rol_id == 3) {
    $check = $conexion->prepare("SELECT id FROM compras WHERE usuario_id = ? AND item_id = ? AND tipo_item = 'curso'");
    $check->execute([$usuario_id, $id_curso]);
    if ($check->rowCount() == 0) { 
        header("Location: ver_curso.php?id=" . $id_curso);
        exit;
    }
}

// 3. Obtener Datos del Curso
$stmt = $conexion->prepare("SELECT * FROM cursos WHERE id = ?");
$stmt->execute([$id_curso]);
$curso = $stmt->fetch();
if (!$curso) die("El curso no existe.");

// 4. Obtener Lecciones
$stmtLecciones = $conexion->prepare("SELECT * FROM lecciones WHERE curso_id = ? ORDER BY orden ASC, id ASC");
$stmtLecciones->execute([$id_curso]);
$lecciones = $stmtLecciones->fetchAll();

// 5. Determinar lección actual
$clase_actual = null;
$recursos_clase = []; 

if (!empty($lecciones)) {
    if (!isset($lecciones[$indice_actual])) { $indice_actual = 0; }
    $clase_actual = $lecciones[$indice_actual];

    // Obtener recursos de la lección
    $stmtRecursos = $conexion->prepare("SELECT * FROM materiales WHERE leccion_id = ? ORDER BY id DESC");
    $stmtRecursos->execute([$clase_actual['id']]);
    $recursos_clase = $stmtRecursos->fetchAll();
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($curso['titulo']); ?> - Aula Virtual</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body { height: 100vh; overflow: hidden; background-color: #f8f9fa; }
        
        /* Navbar superior del aula */
        .aula-header { height: 60px; background: white; border-bottom: 1px solid #dee2e6; z-index: 10; }
        
        /* Contenedor principal flex */
        .aula-container { display: flex; height: calc(100vh - 60px); }
        
        /* Área del reproductor (Izquierda/Centro) */
        .player-area { flex: 1; overflow-y: auto; padding: 20px; }
        
        /* Barra lateral de lecciones (Derecha) */
        .sidebar-lecciones { width: 350px; background: white; border-left: 1px solid #dee2e6; display: flex; flex-direction: column; }
        .lista-scroll { flex: 1; overflow-y: auto; }

        /* Aspect Ratio del Video */
        .video-container { position: relative; padding-bottom: 56.25%; height: 0; background: #000; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .video-container iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; }

        /* Responsivo: En móviles la barra va abajo */
        @media (max-width: 992px) {
            body { height: auto; overflow: auto; }
            .aula-container { flex-direction: column; height: auto; }
            .sidebar-lecciones { width: 100%; height: auto; border-left: 0; border-top: 1px solid #dee2e6; }
            .player-area { padding: 15px; }
        }
    </style>
</head>
<body>

    <header class="aula-header d-flex align-items-center justify-content-between px-3 px-md-4 shadow-sm">
        <div class="d-flex align-items-center gap-3">
            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm rounded-circle">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h6 class="mb-0 fw-bold text-dark text-truncate" style="max-width: 300px;">
                <?php echo htmlspecialchars($curso['titulo']); ?>
            </h6>
        </div>
        <div>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3">
                Progreso: <?php echo $indice_actual + 1; ?> / <?php echo count($lecciones); ?>
            </span>
        </div>
    </header>

    <div class="aula-container">
        
        <main class="player-area bg-light">
            <div class="container-fluid" style="max-width: 1000px;">
                
                <?php if($clase_actual): ?>
                    <div class="video-container mb-4">
                        <iframe src="<?php echo htmlspecialchars($clase_actual['video_url']); ?>" allowfullscreen></iframe>
                    </div>

                    <div class="d-flex justify-content-between align-items-start mb-3 gap-3">
                        <h2 class="h3 fw-bold text-dark mb-0"><?php echo htmlspecialchars($clase_actual['titulo']); ?></h2>
                    </div>

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <h6 class="fw-bold text-secondary text-uppercase small mb-3">Descripción de la clase</h6>
                            <p class="card-text text-secondary">
                                <?php echo nl2br(htmlspecialchars($clase_actual['descripcion'])); ?>
                            </p>
                        </div>
                    </div>

                    <?php if(!empty($recursos_clase)): ?>
                        <h5 class="fw-bold text-dark mb-3">Recursos Disponibles</h5>
                        <div class="row g-3 mb-5">
                            <?php foreach($recursos_clase as $rec): ?>
                                <div class="col-sm-6">
                                    <a href="../../uploads/materiales/<?php echo $rec['archivo']; ?>" target="_blank" class="text-decoration-none">
                                        <div class="card h-100 border-0 shadow-sm hover-bg-light">
                                            <div class="card-body d-flex align-items-center gap-3">
                                                <div class="bg-warning bg-opacity-10 p-2 rounded text-warning">
                                                    <i class="bi bi-file-earmark-arrow-down fs-4"></i>
                                                </div>
                                                <div class="text-truncate">
                                                    <h6 class="mb-0 text-dark fw-bold text-truncate"><?php echo htmlspecialchars($rec['titulo']); ?></h6>
                                                    <small class="text-muted">Descargar archivo</small>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between my-5">
                        <?php if($indice_actual > 0): ?>
                            <a href="aula.php?id=<?php echo $id_curso; ?>&indice=<?php echo $indice_actual - 1; ?>" class="btn btn-outline-secondary px-4 rounded-pill">
                                <i class="bi bi-arrow-left"></i> Anterior
                            </a>
                        <?php else: ?>
                            <div></div>
                        <?php endif; ?>

                        <?php if($indice_actual < count($lecciones) - 1): ?>
                            <a href="aula.php?id=<?php echo $id_curso; ?>&indice=<?php echo $indice_actual + 1; ?>" class="btn btn-primary px-4 rounded-pill shadow-sm">
                                Siguiente Lección <i class="bi bi-arrow-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-cone-striped display-1 text-muted opacity-25"></i>
                        <h3 class="mt-3 text-dark">Contenido no disponible</h3>
                        <p class="text-muted">El instructor aún no ha publicado lecciones en este curso.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <aside class="sidebar-lecciones">
            <div class="p-3 bg-light border-bottom">
                <h6 class="mb-0 fw-bold text-secondary"><i class="bi bi-list-ul"></i> Contenido del Curso</h6>
            </div>
            
            <div class="lista-scroll list-group list-group-flush">
                <?php foreach($lecciones as $index => $leccion): ?>
                    <?php $isActive = ($index == $indice_actual); ?>
                    <a href="aula.php?id=<?php echo $id_curso; ?>&indice=<?php echo $index; ?>" 
                       class="list-group-item list-group-item-action py-3 <?php echo $isActive ? 'active border-start border-4 border-primary' : ''; ?>" 
                       <?php echo $isActive ? 'aria-current="true"' : ''; ?>>
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi <?php echo $isActive ? 'bi-play-circle-fill' : 'bi-play-circle'; ?> fs-5"></i>
                                <div>
                                    <small class="text-uppercase fw-bold" style="font-size: 0.7rem; opacity: 0.8;">Lección <?php echo $index + 1; ?></small>
                                    <h6 class="mb-0 fw-semibold" style="font-size: 0.95rem;"><?php echo htmlspecialchars($leccion['titulo']); ?></h6>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </aside>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>