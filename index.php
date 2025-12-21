<?php
session_start();
require_once 'config/bd.php';

// Configuración de rutas
$dashboardUrl = "modules/auth/login.php"; 
$nombreUsuario = "";

if(isset($_SESSION['usuario_id'])) {
    $nombreUsuario = $_SESSION['nombre'];
    if($_SESSION['rol_id'] == 1) $dashboardUrl = "modules/admin/dashboard.php";
    elseif($_SESSION['rol_id'] == 2) $dashboardUrl = "modules/docente/dashboard.php";
    else $dashboardUrl = "modules/estudiante/dashboard.php";
}

// Consultas
try {
    // Cursos (Traemos 3 destacados para un diseño más grande)
    $sqlCursos = "SELECT c.*, u.nombre_completo as docente FROM cursos c JOIN usuarios u ON c.docente_id = u.id ORDER BY c.id DESC LIMIT 3";
    $cursos = $conexion->query($sqlCursos)->fetchAll();
    
    // Planes
    $sqlPlanes = "SELECT * FROM planes ORDER BY precio ASC";
    $planes = $conexion->query($sqlPlanes)->fetchAll();
} catch (Exception $e) { $cursos = []; $planes = []; }
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EduPlatform | Tu Futuro Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        /* Pequeños ajustes inline para degradados */
        .bg-gradient-primary { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); }
        .hero-overlay { background: rgba(0, 0, 0, 0.6); }
        .card-hover:hover { transform: translateY(-10px); box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important; transition: all 0.3s ease; }
        .img-overlay-card { height: 250px; object-fit: cover; }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary fs-4" href="#">
                <i class="bi bi-rocket-takeoff-fill"></i> EduPlatform
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav mx-auto fw-semibold gap-3">
                    <li class="nav-item"><a class="nav-link text-dark" href="#">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link text-secondary" href="#explorar">Explorar</a></li>
                    <li class="nav-item"><a class="nav-link text-secondary" href="#planes">Precios</a></li>
                </ul>
                <div class="d-flex gap-2">
                    <?php if(isset($_SESSION['usuario_id'])): ?>
                        <a href="<?php echo $dashboardUrl; ?>" class="btn btn-primary rounded-pill px-4 shadow-sm">
                            <i class="bi bi-grid-fill me-2"></i>Dashboard
                        </a>
                    <?php else: ?>
                        <a href="modules/auth/login.php" class="btn btn-outline-dark rounded-pill px-4">Entrar</a>
                        <a href="modules/auth/registro.php" class="btn btn-primary rounded-pill px-4 shadow-sm">Crear cuenta</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <header id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active" style="height: 85vh;">
                <img src="https://images.unsplash.com/photo-1531297420492-8d4896d4cc5d?q=80&w=2000" class="d-block w-100 h-100 object-fit-cover" alt="Tech">
                <div class="position-absolute top-0 start-0 w-100 h-100 hero-overlay d-flex align-items-center">
                    <div class="container text-white text-center">
                        <span class="badge bg-warning text-dark mb-3 px-3 py-2 rounded-pill text-uppercase ls-1">Nueva Plataforma</span>
                        <h1 class="display-1 fw-bold mb-4">Domina la Tecnología</h1>
                        <p class="fs-4 fw-light mb-5 opacity-75 mx-auto" style="max-width: 700px;">Cursos intensivos y prácticos para llevar tu carrera al siguiente nivel.</p>
                        <div class="bg-white p-2 rounded-pill shadow-lg mx-auto d-flex" style="max-width: 600px;">
                            <input type="text" class="form-control border-0 rounded-pill px-4 fs-5" placeholder="¿Qué quieres aprender hoy?">
                            <button class="btn btn-primary rounded-pill px-4 py-2 fw-bold">Buscar</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item" style="height: 85vh;">
                <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=2000" class="d-block w-100 h-100 object-fit-cover" alt="Team">
                <div class="position-absolute top-0 start-0 w-100 h-100 hero-overlay d-flex align-items-center">
                    <div class="container text-white text-start">
                        <div class="row">
                            <div class="col-lg-7">
                                <h1 class="display-2 fw-bold mb-4">Aprende en Equipo</h1>
                                <p class="fs-4 mb-4">Únete a grupos de estudio, comparte proyectos y recibe feedback de expertos en tiempo real.</p>
                                <a href="modules/auth/registro.php" class="btn btn-outline-light btn-lg rounded-pill px-5">Unirme a la comunidad</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
    </header>

    <section class="container" style="margin-top: -3rem; position: relative; z-index: 10;">
        <div class="row g-3 justify-content-center">
            <div class="col-6 col-md-2">
                <a href="#" class="card border-0 shadow-sm text-center py-4 text-decoration-none card-hover">
                    <i class="bi bi-code-slash fs-1 text-primary mb-2"></i>
                    <h6 class="text-dark fw-bold m-0">Código</h6>
                </a>
            </div>
            <div class="col-6 col-md-2">
                <a href="#" class="card border-0 shadow-sm text-center py-4 text-decoration-none card-hover">
                    <i class="bi bi-brush fs-1 text-danger mb-2"></i>
                    <h6 class="text-dark fw-bold m-0">Diseño</h6>
                </a>
            </div>
            <div class="col-6 col-md-2">
                <a href="#" class="card border-0 shadow-sm text-center py-4 text-decoration-none card-hover">
                    <i class="bi bi-graph-up-arrow fs-1 text-success mb-2"></i>
                    <h6 class="text-dark fw-bold m-0">Negocios</h6>
                </a>
            </div>
            <div class="col-6 col-md-2">
                <a href="#" class="card border-0 shadow-sm text-center py-4 text-decoration-none card-hover">
                    <i class="bi bi-camera fs-1 text-warning mb-2"></i>
                    <h6 class="text-dark fw-bold m-0">Foto</h6>
                </a>
            </div>
        </div>
    </section>

    <section id="explorar" class="py-5 my-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold display-6">Cursos Destacados</h2>
                <p class="text-muted">Selección premium para empezar hoy</p>
            </div>
            
            <?php foreach($cursos as $c): ?>
            <div class="card border-0 shadow mb-4 overflow-hidden card-hover rounded-4">
                <div class="row g-0">
                    <div class="col-md-5 position-relative">
                        <?php 
                            $img = !empty($c['imagen_portada']) ? "uploads/cursos/".$c['imagen_portada'] : "https://via.placeholder.com/500x300";
                        ?>
                        <img src="<?php echo $img; ?>" class="w-100 h-100 object-fit-cover" style="min-height: 250px;">
                        <span class="position-absolute top-0 start-0 m-3 badge bg-white text-dark shadow-sm"><?php echo $c['nivel']; ?></span>
                    </div>
                    <div class="col-md-7">
                        <div class="card-body p-4 p-lg-5 d-flex flex-column h-100 justify-content-center">
                            <h3 class="card-title fw-bold"><?php echo htmlspecialchars($c['titulo']); ?></h3>
                            <p class="card-text text-muted mb-4"><?php echo htmlspecialchars($c['descripcion']); ?></p>
                            <div class="d-flex align-items-center justify-content-between mt-auto">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                        <?php echo strtoupper(substr($c['docente'],0,1)); ?>
                                    </div>
                                    <div>
                                        <small class="d-block text-muted lh-1">Docente</small>
                                        <span class="fw-bold"><?php echo htmlspecialchars($c['docente']); ?></span>
                                    </div>
                                </div>
                                <a href="modules/estudiante/ver_curso.php?id=<?php echo $c['id']; ?>" class="btn btn-dark rounded-pill px-4">Ver Curso <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <div class="text-center mt-4">
                <a href="modules/estudiante/catalogo.php" class="btn btn-outline-primary btn-lg rounded-pill fw-bold">Ver Todo el Catálogo</a>
            </div>
        </div>
    </section>

    <section id="planes" class="py-5 bg-dark text-white position-relative overflow-hidden">
        <div class="position-absolute top-0 end-0 p-5 rounded-circle bg-primary opacity-25 blur" style="width: 400px; height: 400px; filter: blur(100px);"></div>
        <div class="position-absolute bottom-0 start-0 p-5 rounded-circle bg-secondary opacity-25 blur" style="width: 300px; height: 300px; filter: blur(80px);"></div>

        <div class="container position-relative z-1">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Planes Simples</h2>
                <p class="lead opacity-75">Sin contratos ocultos. Cancela cuando quieras.</p>
            </div>

            <div class="row justify-content-center align-items-center">
                <?php foreach($planes as $index => $p): ?>
                <?php $esDestacado = ($index === 1); // Destacamos el segundo plan por defecto ?>
                <div class="col-md-4 mb-4">
                    <div class="card <?php echo $esDestacado ? 'bg-primary text-white scale-up' : 'bg-transparent border border-secondary text-white'; ?> rounded-4 p-4 h-100" style="<?php echo $esDestacado ? 'transform: scale(1.05); border:none;' : ''; ?>">
                        <div class="card-body text-center">
                            <h4 class="fw-bold mb-3"><?php echo htmlspecialchars($p['nombre']); ?></h4>
                            <h1 class="display-4 fw-bold mb-0">$<?php echo number_format($p['precio'],0); ?></h1>
                            <small class="opacity-75">/ mes</small>
                            
                            <hr class="my-4 opacity-25">
                            
                            <ul class="list-unstyled text-start mx-auto mb-4" style="max-width: 200px;">
                                <li class="mb-2"><i class="bi bi-check2-circle me-2"></i> <?php echo $p['limite_sesiones']; ?> dispositivos</li>
                                <li class="mb-2"><i class="bi bi-check2-circle me-2"></i> Acceso total</li>
                                <li class="mb-2"><i class="bi bi-check2-circle me-2"></i> Certificados</li>
                            </ul>
                            
                            <a href="modules/estudiante/suscripcion.php" class="btn <?php echo $esDestacado ? 'btn-light text-primary' : 'btn-outline-light'; ?> w-100 rounded-pill fw-bold">Empezar Ahora</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white">
        <div class="container">
            <div class="bg-gradient-primary rounded-5 p-5 text-white text-center shadow-lg">
                <i class="bi bi-book-half display-1 mb-3 d-block opacity-50"></i>
                <h2 class="fw-bold mb-3">¿Prefieres leer?</h2>
                <p class="fs-5 mb-4 opacity-75">Accede a nuestra biblioteca exclusiva con tu suscripción.</p>
                <a href="#biblioteca" class="btn btn-light rounded-pill px-5 fw-bold text-primary">Ir a la Biblioteca</a>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

</body>
</html>