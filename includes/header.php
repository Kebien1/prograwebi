<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DEFINIR RUTA BASE
// Ajusta esto si tu carpeta se llama diferente en htdocs.
// Si tu proyecto está en http://localhost/prograwebi, déjalo así.
$base_url = 'http://localhost/prograwebi'; 
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EduPlatform</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        /* Efecto hover suave para tarjetas en otras vistas */
        .hover-scale { transition: transform 0.2s; }
        .hover-scale:hover { transform: scale(1.02); }
        
        /* Asegurar que el footer se quede abajo */
        .flex-shrink-0 {
            flex: 1;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?php echo $base_url; ?>/index.php">
        <i class="bi bi-mortarboard-fill text-primary"></i> EduPlatform
    </a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#userNav" aria-controls="userNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="userNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $base_url; ?>/index.php">Inicio</a>
        </li>

        <?php if(isset($_SESSION['usuario_id'])): ?>
            
            <?php if($_SESSION['rol_id'] == 1): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base_url; ?>/modules/admin/dashboard.php">Panel</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base_url; ?>/modules/admin/usuarios.php">Usuarios</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link fw-bold text-warning" href="<?php echo $base_url; ?>/modules/admin/planes.php">
                        <i class="bi bi-star-fill"></i> Planes
                    </a>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        Cursos
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo $base_url; ?>/modules/admin/cursos_lista.php">Listar Cursos</a></li>
                        <li><a class="dropdown-item" href="<?php echo $base_url; ?>/modules/admin/cursos_crear.php">Crear Nuevo</a></li>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if($_SESSION['rol_id'] == 3): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base_url; ?>/modules/estudiante/dashboard.php">Mi Panel</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base_url; ?>/modules/estudiante/catalogo.php">Catálogo</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base_url; ?>/modules/estudiante/mis_compras.php">Mis Cursos</a>
                </li>
            <?php endif; ?>

        <?php endif; ?>
      </ul>

      <ul class="navbar-nav ms-auto">
        <?php if(isset($_SESSION['usuario_id'])): ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    
                    <?php if($_SESSION['rol_id'] == 3): ?>
                        <li><a class="dropdown-item" href="<?php echo $base_url; ?>/modules/estudiante/perfil.php">Mi Perfil</a></li>
                        
                        <li><a class="dropdown-item" href="<?php echo $base_url; ?>/modules/estudiante/suscripcion.php">
                            <i class="bi bi-credit-card-2-front"></i> Mi Suscripción
                        </a></li>
                        
                        <li><hr class="dropdown-divider"></li>
                    <?php endif; ?>
                    
                    <li><a class="dropdown-item text-danger" href="<?php echo $base_url; ?>/modules/auth/logout.php">Cerrar Sesión</a></li>
                </ul>
            </li>
        <?php else: ?>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo $base_url; ?>/modules/auth/login.php">Iniciar Sesión</a>
            </li>
            <li class="nav-item">
                <a class="btn btn-primary btn-sm ms-2" href="<?php echo $base_url; ?>/modules/auth/registro.php">Regístrate</a>
            </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="flex-shrink-0">