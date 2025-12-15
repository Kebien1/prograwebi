<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Definir color y nombre según el rol
$navbarColor = "bg-primary"; 
$rolNombre = "Estudiante";
$dashboardUrl = "#"; // Por defecto

// Lógica de redirección según rol
if(isset($_SESSION['rol_id'])) {
    if($_SESSION['rol_id'] == 1) { 
        $navbarColor = "bg-dark"; // Admin
        $rolNombre = "Administrador";
        $dashboardUrl = "../../modules/admin/dashboard.php";
    } elseif($_SESSION['rol_id'] == 2) { 
        $navbarColor = "bg-success"; // Docente
        $rolNombre = "Docente";
        $dashboardUrl = "../../modules/docente/dashboard.php";
    } else {
        $navbarColor = "bg-primary"; // Estudiante
        $rolNombre = "Estudiante";
        $dashboardUrl = "../../modules/estudiante/dashboard.php";
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EduPlatform - <?php echo $rolNombre; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .hover-scale:hover { transform: scale(1.02); transition: 0.3s; }
    </style>
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark <?php echo $navbarColor; ?> shadow-sm mb-4 sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?php echo $dashboardUrl; ?>">
        <i class="bi bi-speedometer2"></i> EduPlatform
    </a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#userNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="userNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php if(isset($_SESSION['usuario_id'])): ?>
            <li class="nav-item">
                <a class="nav-link active fw-bold" href="<?php echo $dashboardUrl; ?>">
                    <i class="bi bi-house-door-fill"></i> Mi Panel
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../../index.php">
                    <i class="bi bi-globe"></i> Ver Portada
                </a>
            </li>
        <?php endif; ?>
      </ul>

      <ul class="navbar-nav ms-auto align-items-center">
        <?php if(isset($_SESSION['usuario_id'])): ?>
            <li class="nav-item me-3">
                <span class="badge bg-light text-dark border">
                    Plan <?php echo $_SESSION['plan_nombre'] ?? 'Básico'; ?>
                </span>
            </li>
            
            <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-white active fw-bold" href="#" role="button" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle"></i> <?php echo $_SESSION['nombre'] ?? 'Usuario'; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                <li><span class="dropdown-header text-uppercase small fw-bold">Rol: <?php echo $rolNombre; ?></span></li>
                <li><hr class="dropdown-divider"></li>
                
                <?php if($_SESSION['rol_id'] == 3): ?>
                <li>
                    <a class="dropdown-item" href="../../modules/estudiante/perfil.php">
                        <i class="bi bi-person-gear"></i> Mi Perfil
                    </a>
                </li>
                <?php endif; ?>

                <li>
                    <a class="dropdown-item text-danger" href="../../modules/auth/logout.php">
                        <i class="bi bi-power"></i> Cerrar Sesión
                    </a>
                </li>
            </ul>
            </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>