<?php
// 1. INICIAR SESIÓN (IMPORTANTE: Esto debe ir al principio)
session_start();

// 2. Configuración
require_once 'config/bd.php';

// 3. Determinar URL del Dashboard si está logueado
$dashboardUrl = "modules/auth/login.php"; // Por defecto al login
if(isset($_SESSION['rol_id'])) {
    if($_SESSION['rol_id'] == 1) $dashboardUrl = "modules/admin/dashboard.php";
    elseif($_SESSION['rol_id'] == 2) $dashboardUrl = "modules/docente/dashboard.php";
    else $dashboardUrl = "modules/estudiante/dashboard.php";
}

// 4. Obtener Planes (Consultas de BD)
try {
    $stmtPlanes = $conexion->query("SELECT * FROM planes ORDER BY precio ASC");
    $planes = $stmtPlanes->fetchAll();
} catch (Exception $e) { $planes = []; }

// 5. Obtener Cursos RECIENTES
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
    <title>EduPlatform | Aprende sin límites</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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
                    <li class="nav-item"><a class="nav-link" href="#planes">Planes</a></li>
                    
                    <?php if(isset($_SESSION['usuario_id'])): ?>
                        <li class="nav-item ms-lg-3 d-flex align-items-center">
                            <span class="text-white me-3 small">Hola, <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></span>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $dashboardUrl; ?>" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold text-dark">
                                <i class="bi bi-speedometer2"></i> Ir a Mi Panel
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="modules/auth/logout.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3" title="Cerrar Sesión">
                                <i class="bi bi-power"></i>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-lg-3">
                            <a href="modules/auth/login.php" class="btn btn-outline-light btn-sm rounded-pill px-3">Ingresar</a>
                        </li>
                        <li class="nav-item">
                            <a href="modules/auth/registro.php" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">Registrarse</a>
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
                    <h1 class="display-4 fw-bold mb-3">Domina el Futuro Hoy</h1>
                    <p class="lead mb-4 opacity-75">
                        Accede a la mejor educación online. Cursos prácticos, instructores expertos y una comunidad global esperándote.
                    </p>
                    
                    <div class="d-flex justify-content-center gap-3">
                        <?php if(isset($_SESSION['usuario_id'])): ?>
                            <a href="<?php echo $dashboardUrl; ?>" class="btn btn-light btn-lg text-primary fw-bold px-4 rounded-pill shadow-sm">
                                Continuar Aprendiendo
                            </a>
                        <?php else: ?>
                            <a href="modules/auth/registro.php" class="btn btn-light btn-lg text-primary fw-bold px-4 rounded-pill shadow-sm">
                                Empezar Gratis
                            </a>
                        <?php endif; ?>
                        
                        <a href="#cursos" class="btn btn-outline-light btn-lg px-4 rounded-pill">
                            Ver Catálogo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="py-5 bg-white border-bottom">
        <div class="container">
            <div class="row text-center g-4">
                <div class="col-md-4">
                    <h2 class="fw-bold text-primary display-6">+10k</h2>
                    <p class="text-muted text-uppercase fw-bold small">Estudiantes</p>
                </div>
                <div class="col-md-4">
                    <h2 class="fw-bold text-success display-6">+500</h2>
                    <p class="text-muted text-uppercase fw-bold small">Cursos</p>
                </div>
                <div class="col-md-4">
                    <h2 class="fw-bold text-warning display-6">4.8</h2>
                    <p class="text-muted text-uppercase fw-bold small">Valoración</p>
                </div>
            </div>
        </div>
    </section>

    <section id="cursos" class="py-5 bg-light">
        <div class="container py-4">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Cursos Recientes</h2>
                <p class="text-muted">Explora lo último de nuestros docentes.</p>
            </div>

            <?php if(empty($cursos)): ?>
                <div class="alert alert-info text-center">
                    Aún no hay cursos publicados.
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php foreach($cursos as $c): ?>
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="bg-light ratio ratio-16x9 d-flex align-items-center justify-content-center text-secondary">
                                    <i class="bi bi-play-circle display-1 opacity-25"></i>
                                </div>
                                
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="badge bg-primary bg-opacity-10 text-primary">Nuevo</span>
                                        <span class="fw-bold text-success">$<?php echo number_format($c['precio'], 0); ?></span>
                                    </div>
                                    <h5 class="card-title fw-bold"><?php echo htmlspecialchars($c['titulo']); ?></h5>
                                    <p class="card-text text-muted small text-truncate">
                                        <?php echo htmlspecialchars($c['descripcion']); ?>
                                    </p>
                                </div>
                                <div class="card-footer bg-white border-0 pb-3">
                                    <small class="text-muted d-block mb-3">
                                        <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($c['docente']); ?>
                                    </small>
                                    <a href="modules/estudiante/ver_curso.php?id=<?php echo $c['id']; ?>" class="btn btn-outline-primary w-100 rounded-pill">
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

    <section class="py-5 bg-white">
        <div class="container py-4">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="ratio ratio-4x3 bg-light rounded-4 shadow-sm d-flex align-items-center justify-content-center">
                        <i class="bi bi-people display-1 text-muted opacity-25"></i>
                    </div>
                </div>
                <div class="col-lg-6">
                    <span class="text-uppercase text-warning fw-bold small">Beneficios</span>
                    <h2 class="fw-bold mb-4">Aprendizaje a tu ritmo</h2>
                    
                    <div class="d-flex mb-4">
                        <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle me-3">
                            <i class="bi bi-laptop fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold">Acceso 24/7</h5>
                            <p class="text-muted small">Estudia desde cualquier dispositivo.</p>
                        </div>
                    </div>

                    <div class="d-flex mb-4">
                        <div class="p-3 bg-success bg-opacity-10 text-success rounded-circle me-3">
                            <i class="bi bi-award fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold">Certificados</h5>
                            <p class="text-muted small">Valida tus conocimientos al terminar.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="planes" class="py-5 bg-light">
        <div class="container py-4">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Planes Flexibles</h2>
                <p class="text-muted">Elige tu suscripción.</p>
            </div>

            <div class="row row-cols-1 row-cols-md-3 g-4 justify-content-center">
                <?php foreach ($planes as $plan): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-header py-3 text-center bg-white border-bottom-0">
                                <h4 class="my-0 fw-bold"><?php echo htmlspecialchars($plan['nombre']); ?></h4>
                            </div>
                            <div class="card-body text-center d-flex flex-column">
                                <h1 class="card-title fw-bold">
                                    $<?php echo number_format($plan['precio'], 0); ?>
                                    <small class="text-muted fw-light fs-5">/mes</small>
                                </h1>
                                <ul class="list-unstyled mt-3 mb-4 flex-grow-1 small text-muted">
                                    <li class="mb-2"><i class="bi bi-check-lg text-success"></i> Acceso total</li>
                                    <li class="mb-2"><i class="bi bi-laptop text-primary"></i> <?php echo $plan['limite_sesiones']; ?> Dispositivos</li>
                                </ul>
                                <a href="modules/auth/registro.php?plan=<?php echo $plan['id']; ?>" class="w-100 btn btn-lg btn-outline-primary rounded-pill">
                                    Elegir <?php echo $plan['nombre']; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white py-4 text-center">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> EduPlatform. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>