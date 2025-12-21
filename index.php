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
    elseif($_SESSION['rol_id'] == 2) $dashboardUrl = "modules/docente/dashboard.php"; // Si existiera rol docente separado
    else $dashboardUrl = "modules/estudiante/dashboard.php";
}

// 3. CONSULTAS A LA BASE DE DATOS

// A) Obtener Cursos RECIENTES (Límite 6)
try {
    $sqlCursos = "SELECT c.*, u.nombre_completo as docente 
                  FROM cursos c 
                  JOIN usuarios u ON c.docente_id = u.id 
                  ORDER BY c.id DESC LIMIT 6";
    $cursos = $conexion->query($sqlCursos)->fetchAll();
} catch (Exception $e) { $cursos = []; }

// B) Obtener Planes de Suscripción (Nuevo tema añadido)
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
                            <p class="lead mb-4 fs-4">Accede a cientos de cursos en programación, diseño y negocios desde cualquier lugar.</p>
                            <a href="modules/auth/registro.php" class="btn btn-warning btn-lg rounded-pill px-5 fw-bold shadow">Empieza Gratis</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item" data-bs-interval="5000">
                <div style="height: 550px; background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=1920'); background-size: cover; background-position: center;">
                    <div class="d-flex h-100 align-items-center justify-content-center text-center">
                        <div class="container text-white">
                            <h1 class="display-3 fw-bold mb-3">Comunidad de Aprendizaje</h1>
                            <p class="lead mb-4 fs-4">Conecta con docentes expertos y compañeros de todo el mundo.</p>
                            <a href="#cursos" class="btn btn-outline-light btn-lg rounded-pill px-5 fw-bold">Ver Cursos</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item" data-bs-interval="5000">
                <div style="height: 550px; background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1497633762265-9d179a990aa6?q=80&w=1920'); background-size: cover; background-position: center;">
                    <div class="d-flex h-100 align-items-center justify-content-center text-center">
                        <div class="container text-white">
                            <h1 class="display-3 fw-bold mb-3">Biblioteca Digital</h1>
                            <p class="lead mb-4 fs-4">Descarga libros y recursos exclusivos con nuestros planes Premium.</p>
                            <a href="#planes" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow">Ver Planes</a>
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

    <section class="py-5 bg-white shadow-sm position-relative" style="z-index: 2; margin-top: -50px;">
        <div class="container">
            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <div class="p-4 bg-light rounded-4 shadow-sm h-100">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                            <i class="bi bi-laptop fs-2"></i>
                        </div>
                        <h4 class="fw-bold">100% Online</h4>
                        <p class="text-muted">Estudia a tu propio ritmo, sin horarios fijos y desde cualquier dispositivo.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4 bg-light rounded-4 shadow-sm h-100">
                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                            <i class="bi bi-award fs-2"></i>
                        </div>
                        <h4 class="fw-bold">Certificados</h4>
                        <p class="text-muted">Obtén reconocimiento por tus logros al finalizar cada curso completo.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4 bg-light rounded-4 shadow-sm h-100">
                        <div class="bg-warning text-dark rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                            <i class="bi bi-book fs-2"></i>
                        </div>
                        <h4 class="fw-bold">Biblioteca</h4>
                        <p class="text-muted">Accede a una amplia colección de libros y recursos digitales.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

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
                    <i class="bi bi-cone-striped fs-1 mb-3"></i>
                    <h4>Estamos actualizando nuestro catálogo.</h4>
                    <p>Vuelve pronto para ver nuevos cursos.</p>
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php foreach($cursos as $c): ?>
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                                <?php 
                                    $ruta_imagen = "uploads/cursos/" . $c['imagen_portada'];
                                    // Imagen genérica si no hay portada
                                    if (empty($c['imagen_portada']) || !file_exists($ruta_imagen)) {
                                        $ruta_imagen = "https://via.placeholder.com/400x225?text=Curso+EduPlatform";
                                    }
                                ?>
                                <div class="ratio ratio-16x9">
                                    <img src="<?php echo $ruta_imagen; ?>" class="card-img-top object-fit-cover" alt="Portada">
                                </div>

                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill"><?php echo htmlspecialchars($c['nivel']); ?></span>
                                        <small class="text-muted"><i class="bi bi-clock"></i> <?php echo htmlspecialchars($c['duracion']); ?></small>
                                    </div>
                                    <h5 class="card-title fw-bold text-truncate"><?php echo htmlspecialchars($c['titulo']); ?></h5>
                                    <p class="card-text text-muted small text-truncate">
                                        <?php echo htmlspecialchars($c['descripcion']); ?>
                                    </p>
                                </div>
                                <div class="card-footer bg-white border-0 pb-4 pt-0">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-light rounded-circle p-2 me-2"><i class="bi bi-person text-secondary"></i></div>
                                        <small class="text-muted fw-bold"><?php echo htmlspecialchars($c['docente']); ?></small>
                                    </div>
                                    <a href="modules/estudiante/ver_curso.php?id=<?php echo $c['id']; ?>" class="btn btn-primary w-100 rounded-pill fw-bold shadow-sm">
                                        Ver Detalles
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section id="biblioteca" class="py-5 bg-dark text-white position-relative" style="background-image: url('https://images.unsplash.com/photo-1481627834876-b7833e8f5570?q=80&w=2000'); background-size: cover; background-attachment: fixed;">
        <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-75"></div>
        <div class="container position-relative z-1 py-5 text-center">
            <span class="badge bg-warning text-dark mb-3 fs-6">Nuevo Tema Agregado</span>
            <h2 class="display-4 fw-bold mb-4">Biblioteca Digital Premium</h2>
            <p class="lead mb-5 mx-auto" style="max-width: 700px;">
                Complementa tu aprendizaje con nuestra nueva colección de E-Books, guías técnicas y papers académicos. Disponibles para leer en línea o descargar según tu plan.
            </p>
            <div class="d-flex justify-content-center gap-3">
                <a href="modules/estudiante/catalogo.php" class="btn btn-warning btn-lg rounded-pill fw-bold px-5 shadow">
                    <i class="bi bi-book me-2"></i> Explorar Libros
                </a>
            </div>
        </div>
    </section>

    <section id="planes" class="py-5 bg-light">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h6 class="text-primary fw-bold text-uppercase ls-1">Suscripciones</h6>
                <h2 class="fw-bold display-5">Elige tu Plan Ideal</h2>
                <p class="text-muted">Invierte en tu educación con precios accesibles y transparentes.</p>
            </div>

            <?php if(empty($planes)): ?>
                <div class="alert alert-warning text-center">No hay planes configurados en este momento.</div>
            <?php else: ?>
                <div class="row justify-content-center g-4">
                    <?php foreach($planes as $p): ?>
                        <?php 
                            // Lógica visual simple para resaltar planes intermedios
                            $esRecomendado = ($p['precio'] > 0 && $p['precio'] < 50); 
                            $borde = $esRecomendado ? 'border-primary border-2 shadow' : 'border-0 shadow-sm';
                            $claseTitulo = $esRecomendado ? 'text-primary' : 'text-dark';
                        ?>
                        <div class="col-md-4">
                            <div class="card h-100 <?php echo $borde; ?> rounded-4 text-center p-3 position-relative bg-white">
                                <?php if($esRecomendado): ?>
                                    <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-primary px-3 py-2 shadow">
                                        MÁS POPULAR
                                    </span>
                                <?php endif; ?>
                                
                                <div class="card-body d-flex flex-column">
                                    <h4 class="fw-bold <?php echo $claseTitulo; ?> mt-2"><?php echo htmlspecialchars($p['nombre']); ?></h4>
                                    <h2 class="display-4 fw-bold text-dark my-3">
                                        <small class="fs-5 text-muted">$</small><?php echo number_format($p['precio'], 2); ?>
                                    </h2>
                                    <p class="text-muted mb-4 small text-uppercase fw-bold">Facturado Mensualmente</p>
                                    
                                    <ul class="list-unstyled mb-4 text-start mx-auto flex-grow-1" style="max-width: 250px;">
                                        <li class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i> <strong><?php echo $p['limite_sesiones']; ?></strong> Dispositivo(s)</li>
                                        <li class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i> Acceso 24/7</li>
                                        <li class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i> Certificado digital</li>
                                        <?php if(!empty($p['descripcion'])): ?>
                                            <li class="text-muted small fst-italic mt-3 border-top pt-2"><i class="bi bi-info-circle me-1"></i> <?php echo htmlspecialchars($p['descripcion']); ?></li>
                                        <?php endif; ?>
                                    </ul>

                                    <a href="modules/estudiante/suscripcion.php" class="btn <?php echo $esRecomendado ? 'btn-primary' : 'btn-outline-dark'; ?> w-100 rounded-pill fw-bold py-2">
                                        Seleccionar Plan
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <footer class="bg-dark text-secondary pt-5 pb-2 mt-auto">
        <div class="container">
            <div class="row g-4 mb-4">
                <div class="col-lg-4 col-md-6">
                    <h5 class="text-white fw-bold mb-3"><i class="bi bi-mortarboard-fill text-warning"></i> EduPlatform</h5>
                    <p class="small">Somos una plataforma educativa comprometida con la democratización del conocimiento. Aprende a tu ritmo con los mejores profesionales.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-secondary fs-5"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-secondary fs-5"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="text-secondary fs-5"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-secondary fs-5"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h6 class="text-white fw-bold mb-3">Navegación</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="#inicio" class="text-decoration-none text-secondary">Inicio</a></li>
                        <li class="mb-2"><a href="#cursos" class="text-decoration-none text-secondary">Cursos</a></li>
                        <li class="mb-2"><a href="#planes" class="text-decoration-none text-secondary">Planes</a></li>
                        <li class="mb-2"><a href="#biblioteca" class="text-decoration-none text-secondary">Biblioteca</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h6 class="text-white fw-bold mb-3">Categorías</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="#" class="text-decoration-none text-secondary">Desarrollo Web</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-secondary">Diseño Gráfico</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-secondary">Marketing</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-secondary">Negocios</a></li>
                    </ul>
                </div>

                <div class="col-lg-4 col-md-6">
                    <h6 class="text-white fw-bold mb-3">Mantente Actualizado</h6>
                    <p class="small">Recibe las últimas noticias y ofertas.</p>
                    <form class="d-flex gap-2">
                        <input type="email" class="form-control form-control-sm" placeholder="Tu correo">
                        <button class="btn btn-warning btn-sm fw-bold">Suscribir</button>
                    </form>
                </div>
            </div>

            <hr class="border-secondary opacity-25">
            
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start small">
                    &copy; <?php echo date('Y'); ?> EduPlatform. Todos los derechos reservados.
                </div>
                <div class="col-md-6 text-center text-md-end small">
                    <a href="#" class="text-decoration-none text-secondary me-3">Privacidad</a>
                    <a href="#" class="text-decoration-none text-secondary">Términos</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>