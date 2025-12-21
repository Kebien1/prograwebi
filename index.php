<?php
// 1. INICIAR SESIÓN Y CONFIGURACIÓN
session_start();
require_once 'config/bd.php';

// 2. Determinar URL del Dashboard según el rol
$dashboardUrl = "modules/auth/login.php"; 
$nombreUsuario = "";

if(isset($_SESSION['usuario_id'])) {
    $nombreUsuario = $_SESSION['nombre'];
    if($_SESSION['rol_id'] == 1) $dashboardUrl = "modules/admin/dashboard.php";
    elseif($_SESSION['rol_id'] == 2) $dashboardUrl = "modules/docente/dashboard.php";
    else $dashboardUrl = "modules/estudiante/dashboard.php";
}

// 3. CONSULTAS A LA BASE DE DATOS
try {
    $sqlCursos = "SELECT c.*, u.nombre_completo as docente 
                  FROM cursos c 
                  JOIN usuarios u ON c.docente_id = u.id 
                  ORDER BY c.id DESC LIMIT 6";
    $cursos = $conexion->query($sqlCursos)->fetchAll();
} catch (Exception $e) { $cursos = []; }

try {
    $sqlPlanes = "SELECT * FROM planes ORDER BY precio ASC";
    $planes = $conexion->query($sqlPlanes)->fetchAll();
} catch (Exception $e) { $planes = []; }
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EduPlatform | Aprende sin límites</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow">
        <div class="container">
            <a class="navbar-brand text-warning fw-bold" href="#">
                <i class="bi bi-mortarboard-fill"></i> EduPlatform
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="menuPrincipal">
                <ul class="navbar-nav ms-auto gap-2 align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#inicio">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="#cursos">Cursos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#planes">Planes</a></li>
                    <li class="nav-item"><a class="nav-link" href="#biblioteca">Biblioteca</a></li>
                    
                    <?php
                    $cantidad_carrito = isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0;
                    ?>
                    <li class="nav-item">
                        <a href="modules/estudiante/carrito_ver.php" class="nav-link position-relative">
                            <i class="bi bi-cart-fill fs-5"></i>
                            <?php if($cantidad_carrito > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.6rem;">
                                    <?php echo $cantidad_carrito; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <?php if(isset($_SESSION['usuario_id'])): ?>
                        <li class="nav-item ms-lg-3">
                            <div class="dropdown">
                                <button class="btn btn-outline-light dropdown-toggle rounded-pill px-4" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($nombreUsuario); ?>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="<?php echo $dashboardUrl; ?>">Ir a mi Panel</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="modules/auth/logout.php">Cerrar Sesión</a></li>
                                </ul>
                            </div>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-lg-3">
                            <a href="modules/auth/login.php" class="btn btn-outline-light rounded-pill px-4 me-2">Ingresar</a>
                        </li>
                        <li class="nav-item">
                            <a href="modules/auth/registro.php" class="btn btn-warning rounded-pill px-4 fw-bold text-dark">Registro Gratis</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <header id="inicio" class="carousel slide carousel-fade" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#inicio" data-bs-slide-to="0" class="active"></button>
            <button type="button" data-bs-target="#inicio" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#inicio" data-bs-slide-to="2"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active" data-bs-interval="5000">
                <div style="height: 550px; background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=1920'); background-size: cover; background-position: center;">
                    <div class="d-flex h-100 align-items-center justify-content-center text-center">
                        <div class="container text-white">
                            <h1 class="display-3 fw-bold mb-3">Tu futuro empieza hoy</h1>
                            <p class="lead mb-4 fs-4">Accede a cientos de cursos en programación, diseño y negocios.</p>
                            <a href="modules/auth/registro.php" class="btn btn-warning btn-lg rounded-pill px-5 fw-bold shadow">Empieza Gratis</a>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#inicio" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
        <button class="carousel-control-next" type="button" data-bs-target="#inicio" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
    </header>

    <section id="cursos" class="py-5">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-end mb-5 border-bottom pb-3">
                <div>
                    <span class="badge bg-primary mb-2">Novedades</span>
                    <h2 class="fw-bold text-dark display-6 mb-0">Cursos Recientes</h2>
                </div>
                <a href="modules/estudiante/catalogo.php" class="btn btn-outline-dark rounded-pill fw-bold">Ver Catálogo Completo</a>
            </div>

            <?php if(empty($cursos)): ?>
                <div class="alert alert-info text-center py-5">
                    <h4>Estamos actualizando nuestro catálogo.</h4>
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php foreach($cursos as $c): ?>
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                                <?php 
                                    $ruta_imagen = "uploads/cursos/" . $c['imagen_portada'];
                                    if (empty($c['imagen_portada']) || !file_exists($ruta_imagen)) {
                                        $ruta_imagen = "https://via.placeholder.com/400x225?text=Curso+EduPlatform";
                                    }
                                ?>
                                <div class="ratio ratio-16x9">
                                    <img src="<?php echo $ruta_imagen; ?>" class="card-img-top object-fit-cover">
                                </div>

                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill"><?php echo htmlspecialchars($c['nivel']); ?></span>
                                        <small class="text-muted"><i class="bi bi-clock"></i> <?php echo htmlspecialchars($c['duracion']); ?></small>
                                    </div>
                                    <h5 class="card-title fw-bold text-truncate"><?php echo htmlspecialchars($c['titulo']); ?></h5>
                                    
                                    <div class="mt-2">
                                        <?php if($c['precio'] > 0): ?>
                                            <span class="fw-bold fs-5 text-dark">$<?php echo number_format($c['precio'], 2); ?></span>
                                        <?php else: ?>
                                            <span class="fw-bold fs-5 text-success">Gratis</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="card-footer bg-white border-0 pb-4 pt-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="modules/estudiante/ver_curso.php?id=<?php echo $c['id']; ?>" class="btn btn-outline-secondary btn-sm rounded-pill">Ver Detalle</a>
                                        
                                        <?php if($c['precio'] > 0): ?>
                                            <form action="modules/estudiante/carrito_acciones.php" method="POST" class="d-inline">
                                                <input type="hidden" name="accion" value="agregar">
                                                <input type="hidden" name="tipo" value="curso">
                                                <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                                <input type="hidden" name="titulo" value="<?php echo htmlspecialchars($c['titulo']); ?>">
                                                <input type="hidden" name="precio" value="<?php echo $c['precio']; ?>">
                                                
                                                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">
                                                    <i class="bi bi-cart-plus"></i> Agregar
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <a href="modules/estudiante/ver_curso.php?id=<?php echo $c['id']; ?>" class="btn btn-success btn-sm rounded-pill px-3">Inscribirse</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section id="planes" class="py-5 bg-light">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold display-5">Elige tu Plan</h2>
            </div>
            <?php if(!empty($planes)): ?>
                <div class="row justify-content-center g-4">
                    <?php foreach($planes as $p): ?>
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm p-3 text-center">
                                <h4 class="fw-bold mt-2"><?php echo htmlspecialchars($p['nombre']); ?></h4>
                                <h2 class="display-4 fw-bold my-3">$<?php echo number_format($p['precio'], 2); ?></h2>
                                <a href="modules/estudiante/suscripcion.php" class="btn btn-outline-primary rounded-pill w-100">Seleccionar</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <footer class="bg-dark text-secondary pt-5 pb-2 mt-auto">
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> EduPlatform.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>