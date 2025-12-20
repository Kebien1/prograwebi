<?php
// 1. INICIAR SESIÓN
session_start();

// 2. Configuración
require_once 'config/bd.php';

// 3. Determinar URL del Dashboard si está logueado
$dashboardUrl = "modules/auth/login.php"; 
if(isset($_SESSION['rol_id'])) {
    if($_SESSION['rol_id'] == 1) $dashboardUrl = "modules/admin/dashboard.php";
    elseif($_SESSION['rol_id'] == 2) $dashboardUrl = "modules/docente/dashboard.php";
    else $dashboardUrl = "modules/estudiante/dashboard.php";
}

// 4. Obtener Cursos RECIENTES (Sin precios)
try {
    $sqlCursos = "SELECT c.*, u.nombre_completo as docente 
                  FROM cursos c 
                  JOIN usuarios u ON c.docente_id = u.id 
                  ORDER BY c.id DESC LIMIT 6";
    $stmtCursos = $conexion->query($sqlCursos);
    $cursos = $stmtCursos->fetchAll();
} catch (Exception $e) { $cursos = []; }
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EduPlatform | Educación Gratuita</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .hover-scale:hover { transform: scale(1.02); transition: 0.3s; }
        .hero-section { background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://source.unsplash.com/1600x900/?education,library'); background-size: cover; background-position: center; }
        .feature-icon { width: 4rem; height: 4rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-warning" href="#">
                <i class="bi bi-mortarboard-fill"></i> EduPlatform
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="menuPrincipal">
                <ul class="navbar-nav ms-auto gap-2 align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#cursos">Cursos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#como-funciona">Cómo Funciona</a></li>
                    
                    <?php if(isset($_SESSION['usuario_id'])): ?>
                        <li class="nav-item ms-lg-3">
                            <span class="text-white me-2 small">Hola, <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></span>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $dashboardUrl; ?>" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold text-dark">
                                <i class="bi bi-speedometer2"></i> Mi Panel
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="modules/auth/logout.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3" title="Salir">
                                <i class="bi bi-power"></i>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-lg-3">
                            <a href="modules/auth/login.php" class="btn btn-outline-light btn-sm rounded-pill px-3">Ingresar</a>
                        </li>
                        <li class="nav-item">
                            <a href="modules/auth/registro.php" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">Registrarse Gratis</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <header class="bg-primary text-white text-center py-5 mt-5 mb-5">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-3">Aprende sin Límites ni Costos</h1>
                    <p class="lead mb-4 opacity-90">
                        Accede a una educación de calidad de forma totalmente gratuita. Únete a nuestra comunidad y domina nuevas habilidades hoy mismo.
                    </p>
                    
                    <div class="d-flex justify-content-center gap-3">
                        <?php if(!isset($_SESSION['usuario_id'])): ?>
                            <a href="modules/auth/registro.php" class="btn btn-light btn-lg text-primary fw-bold px-4 rounded-pill shadow-sm">
                                Empezar Ahora
                            </a>
                        <?php else: ?>
                            <a href="<?php echo $dashboardUrl; ?>" class="btn btn-light btn-lg text-primary fw-bold px-4 rounded-pill shadow-sm">
                                Ir a mis Clases
                            </a>
                        <?php endif; ?>
                        
                        <a href="#cursos" class="btn btn-outline-light btn-lg px-4 rounded-pill">
                            Explorar Cursos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="py-4 bg-white border-bottom">
        <div class="container">
            <div class="row text-center g-4">
                <div class="col-md-4">
                    <h2 class="fw-bold text-primary mb-0">+100</h2>
                    <p class="text-muted small text-uppercase fw-bold">Cursos Gratis</p>
                </div>
                <div class="col-md-4">
                    <h2 class="fw-bold text-success mb-0">24/7</h2>
                    <p class="text-muted small text-uppercase fw-bold">Acceso Online</p>
                </div>
                <div class="col-md-4">
                    <h2 class="fw-bold text-warning mb-0">100%</h2>
                    <p class="text-muted small text-uppercase fw-bold">Gratuito</p>
                </div>
            </div>
        </div>
    </section>

    <section id="como-funciona" class="py-5 bg-light">
        <div class="container py-4">
            <div class="text-center mb-5">
                <h2 class="fw-bold">¿Cómo funciona?</h2>
                <p class="text-muted">Es muy fácil empezar a aprender.</p>
            </div>
            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <div class="card h-100 border-0 bg-transparent">
                        <div class="card-body">
                            <div class="feature-icon bg-primary bg-opacity-10 text-primary mx-auto mb-3">
                                <i class="bi bi-person-plus-fill"></i>
                            </div>
                            <h5 class="fw-bold">1. Crea tu cuenta</h5>
                            <p class="text-muted small">Regístrate en menos de 1 minuto con tu correo electrónico. Es gratis.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 bg-transparent">
                        <div class="card-body">
                            <div class="feature-icon bg-success bg-opacity-10 text-success mx-auto mb-3">
                                <i class="bi bi-search"></i>
                            </div>
                            <h5 class="fw-bold">2. Elige tu curso</h5>
                            <p class="text-muted small">Navega por nuestro catálogo y haz clic en "Inscribirse" en los temas que te gusten.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 bg-transparent">
                        <div class="card-body">
                            <div class="feature-icon bg-warning bg-opacity-10 text-warning mx-auto mb-3">
                                <i class="bi bi-play-circle-fill"></i>
                            </div>
                            <h5 class="fw-bold">3. Empieza a aprender</h5>
                            <p class="text-muted small">Accede a las lecciones en video y material de lectura al instante.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="cursos" class="py-5 bg-white">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-end mb-5">
                <div>
                    <h2 class="fw-bold mb-1">Cursos Recientes</h2>
                    <p class="text-muted mb-0">Lo último agregado por nuestros docentes.</p>
                </div>
                <a href="modules/estudiante/catalogo.php" class="btn btn-outline-primary rounded-pill">Ver Todos</a>
            </div>

            <?php if(empty($cursos)): ?>
                <div class="alert alert-info text-center">
                    Aún no hay cursos publicados. ¡Vuelve pronto!
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php foreach($cursos as $c): ?>
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm hover-scale">
                                <?php 
                                    $ruta_imagen = "uploads/cursos/" . $c['imagen_portada'];
                                    if (!empty($c['imagen_portada']) && file_exists($ruta_imagen)): 
                                ?>
                                    <img src="<?php echo $ruta_imagen; ?>" class="card-img-top" alt="Portada" style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light ratio ratio-16x9 d-flex align-items-center justify-content-center text-secondary" style="height: 200px;">
                                        <i class="bi bi-card-image display-1 opacity-25"></i>
                                    </div>
                                <?php endif; ?>

                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="badge bg-primary bg-opacity-10 text-primary">Curso</span>
                                        <span class="badge bg-success">Gratis</span>
                                    </div>
                                    <h5 class="card-title fw-bold text-truncate"><?php echo htmlspecialchars($c['titulo']); ?></h5>
                                    <p class="card-text text-muted small text-truncate">
                                        <?php echo htmlspecialchars($c['descripcion']); ?>
                                    </p>
                                </div>
                                <div class="card-footer bg-white border-0 pb-3">
                                    <small class="text-muted d-block mb-3">
                                        <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($c['docente']); ?>
                                    </small>
                                    <a href="modules/estudiante/ver_curso.php?id=<?php echo $c['id']; ?>" class="btn btn-primary w-100 rounded-pill shadow-sm">
                                        Ver Curso
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="py-5 bg-dark text-white text-center">
        <div class="container py-4">
            <h2 class="fw-bold mb-3">¿Listo para comenzar tu carrera?</h2>
            <p class="lead mb-4 text-white-50">Únete a cientos de estudiantes que ya están cambiando su futuro.</p>
            <?php if(!isset($_SESSION['usuario_id'])): ?>
                <a href="modules/auth/registro.php" class="btn btn-warning btn-lg fw-bold px-5 rounded-pill">
                    Registrarme Gratis
                </a>
            <?php endif; ?>
        </div>
    </section>

    <footer class="bg-white text-center py-4 border-top">
        <div class="container">
            <p class="mb-0 text-muted small">&copy; <?php echo date('Y'); ?> EduPlatform - Proyecto Educativo</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>