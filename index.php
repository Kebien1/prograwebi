<?php
session_start();
require_once 'config/bd.php';

// 1. Obtener los 3 cursos más recientes para mostrar en la portada
$stmtCursos = $conexion->prepare("SELECT * FROM cursos WHERE estado = 1 ORDER BY id DESC LIMIT 4");
$stmtCursos->execute();
$cursos_home = $stmtCursos->fetchAll();

// 2. Obtener los planes activos
$stmtPlanes = $conexion->prepare("SELECT * FROM planes WHERE estado = 1 ORDER BY precio ASC");
$stmtPlanes->execute();
$planes_home = $stmtPlanes->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EduPlatform - Aprende a tu ritmo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        /* Pequeños ajustes para el carrusel y tarjetas sin usar CSS externo */
        .carousel-item { height: 500px; }
        .carousel-item img { object-fit: cover; height: 100%; filter: brightness(0.7); }
        .feature-icon { width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; }
        .hover-lift { transition: transform 0.2s; }
        .hover-lift:hover { transform: translateY(-5px); }
    </style>
</head>
<body class="bg-light text-dark">

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary fs-4" href="#"><i class="bi bi-mortarboard-fill"></i> EduPlatform</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center gap-3">
                    <li class="nav-item"><a class="nav-link fw-semibold" href="#inicio">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link fw-semibold" href="#cursos">Cursos</a></li>
                    <li class="nav-item"><a class="nav-link fw-semibold" href="#planes">Precios</a></li>
                    
                    <?php if(isset($_SESSION['usuario_id'])): ?>
                        <li class="nav-item ms-2">
                            <a href="modules/<?php echo ($_SESSION['rol_id'] == 1) ? 'admin' : 'estudiante'; ?>/dashboard.php" 
                               class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                               Ir al Dashboard <i class="bi bi-arrow-right"></i>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-2">
                            <a href="modules/auth/login.php" class="btn btn-outline-primary rounded-pill px-4 fw-bold">Ingresar</a>
                        </li>
                        <li class="nav-item">
                            <a href="modules/auth/registro.php" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Registrarse</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <header id="inicio" class="carousel slide carousel-fade" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1920&q=80" class="d-block w-100" alt="Estudiantes">
                <div class="carousel-caption d-flex flex-column justify-content-center h-100 text-start pb-5 mb-5">
                    <div class="container">
                        <div class="col-lg-6">
                            <span class="badge bg-primary mb-2">Educación Online</span>
                            <h1 class="display-3 fw-bold text-white mb-3">Impulsa tu Futuro Profesional</h1>
                            <p class="lead text-white-50 mb-4">Accede a cursos de alta calidad impartidos por expertos. Aprende a tu ritmo, desde cualquier lugar.</p>
                            <a href="modules/auth/registro.php" class="btn btn-light text-primary btn-lg rounded-pill fw-bold px-5">Empezar Gratis</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <img src="https://images.unsplash.com/photo-1531482615713-2afd69097998?auto=format&fit=crop&w=1920&q=80" class="d-block w-100" alt="Tecnología">
                <div class="carousel-caption d-flex flex-column justify-content-center h-100 text-start pb-5 mb-5">
                    <div class="container">
                        <div class="col-lg-6">
                            <span class="badge bg-warning text-dark mb-2">Novedades</span>
                            <h1 class="display-3 fw-bold text-white mb-3">Tecnología al alcance de todos</h1>
                            <p class="lead text-white-50 mb-4">Desarrollo web, diseño, marketing y más. Actualizamos nuestro contenido constantemente.</p>
                            <a href="#cursos" class="btn btn-warning text-dark btn-lg rounded-pill fw-bold px-5">Ver Cursos</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#inicio" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#inicio" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </header>

    <section class="py-5 bg-white">
        <div class="container py-4">
            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <div class="feature-icon bg-primary bg-opacity-10 text-primary mx-auto mb-3">
                        <i class="bi bi-laptop"></i>
                    </div>
                    <h4 class="fw-bold">Acceso 24/7</h4>
                    <p class="text-muted">Entra a tus clases desde cualquier dispositivo. Tu progreso se guarda automáticamente en la nube.</p>
                </div>
                <div class="col-md-4">
                    <div class="feature-icon bg-success bg-opacity-10 text-success mx-auto mb-3">
                        <i class="bi bi-patch-check"></i>
                    </div>
                    <h4 class="fw-bold">Contenido de Calidad</h4>
                    <p class="text-muted">Lecciones estructuradas, materiales descargables y evaluaciones para medir tu aprendizaje.</p>
                </div>
                <div class="col-md-4">
                    <div class="feature-icon bg-info bg-opacity-10 text-info mx-auto mb-3">
                        <i class="bi bi-infinity"></i>
                    </div>
                    <h4 class="fw-bold">Sin Límites</h4>
                    <p class="text-muted">Repite las lecciones las veces que quieras. Una vez adquieres un curso, es tuyo para siempre.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="cursos" class="py-5 bg-light">
        <div class="container py-4">
            <div class="text-center mb-5">
                <h2 class="fw-bold display-6 text-dark">Cursos Destacados</h2>
                <p class="text-muted">Explora nuestras últimas incorporaciones</p>
            </div>

            <?php if(count($cursos_home) > 0): ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                    <?php foreach($cursos_home as $c): ?>
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm hover-lift bg-white">
                                <?php 
                                    $ruta_img = "uploads/cursos/" . $c['imagen_portada'];
                                    $img_mostrar = (file_exists($ruta_img) && !empty($c['imagen_portada'])) 
                                        ? $ruta_img 
                                        : "https://via.placeholder.com/400x250?text=Curso";
                                ?>
                                <img src="<?php echo $img_mostrar; ?>" class="card-img-top" alt="Curso" style="height: 180px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title fw-bold text-dark text-truncate"><?php echo htmlspecialchars($c['titulo']); ?></h5>
                                    <p class="card-text text-muted small">
                                        <?php echo substr(htmlspecialchars($c['descripcion']), 0, 80); ?>...
                                    </p>
                                </div>
                                <div class="card-footer bg-white border-top-0 d-flex justify-content-between align-items-center pb-3">
                                    <span class="fw-bold text-primary fs-5">$<?php echo number_format($c['precio'], 2); ?></span>
                                    <a href="modules/auth/login.php" class="btn btn-outline-primary btn-sm rounded-pill">Ver detalles</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-5">
                    <a href="modules/auth/login.php" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow">Ver Catálogo Completo</a>
                </div>
            <?php else: ?>
                <div class="alert alert-warning text-center">No hay cursos publicados por el momento.</div>
            <?php endif; ?>
        </div>
    </section>

    <section id="planes" class="py-5 bg-white">
        <div class="container py-5">
            <div class="text-center mb-5">
                <span class="text-primary fw-bold text-uppercase tracking-wide">Suscripciones</span>
                <h2 class="fw-bold display-6 text-dark mt-2">Elige el plan perfecto para ti</h2>
                <p class="text-muted">Mejora tu experiencia con más sesiones simultáneas.</p>
            </div>

            <div class="row justify-content-center g-4">
                <?php foreach($planes_home as $plan): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100 hover-lift text-center p-3">
                            <?php if($plan['precio'] > 0 && $plan['precio'] < 50): ?>
                                <div class="position-absolute top-0 start-50 translate-middle">
                                    <span class="badge bg-warning text-dark rounded-pill px-3 shadow-sm">Popular</span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body d-flex flex-column">
                                <h4 class="fw-bold text-dark mb-3"><?php echo htmlspecialchars($plan['nombre']); ?></h4>
                                <div class="display-4 fw-bold text-primary mb-3">
                                    <?php echo ($plan['precio'] == 0) ? 'Gratis' : '$'.number_format($plan['precio'], 0); ?>
                                </div>
                                <p class="text-muted mb-4"><?php echo htmlspecialchars($plan['descripcion']); ?></p>
                                
                                <ul class="list-unstyled text-start mx-auto mb-4">
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Acceso a cursos comprados</li>
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Soporte técnico</li>
                                    <li class="mb-2"><i class="bi bi-laptop text-primary me-2"></i> <strong><?php echo $plan['limite_sesiones']; ?></strong> Dispositivos activos</li>
                                </ul>
                                
                                <div class="mt-auto">
                                    <a href="modules/auth/registro.php" class="btn btn-outline-dark w-100 rounded-pill fw-bold">Elegir Plan</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="py-5 bg-primary text-white text-center">
        <div class="container py-4">
            <h2 class="fw-bold mb-3">¿Listo para comenzar a aprender?</h2>
            <p class="lead mb-4 text-white-50">Únete a cientos de estudiantes hoy mismo.</p>
            <a href="modules/auth/registro.php" class="btn btn-light text-primary btn-lg rounded-pill fw-bold px-5 shadow">Crear Cuenta Gratis</a>
        </div>
    </section>

    <?php require_once 'includes/footer.php'; ?>