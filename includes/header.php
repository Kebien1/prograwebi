<?php 
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); } 
?>
<!doctype html>
<html lang="es">
<head>
    <title>Panel de Control</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
            <div class="container">
                <a class="navbar-brand fw-bold text-primary" href="dashboard.php">PrograWeb</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="rol.php"><i class="bi bi-person-badge"></i> Roles</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="usuarios.php"><i class="bi bi-people"></i> Usuarios</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="permisos.php"><i class="bi bi-key"></i> Permisos</a>
                            </li>
                            <li class="nav-item border-start ms-2 ps-2">
                                <a class="nav-link fw-bold text-success" href="rol_permisos.php">
                                    <i class="bi bi-shield-lock"></i> Asignar Permisos
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <ul class="navbar-nav">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li class="nav-item">
                                <span class="navbar-text me-3">Hola, <?php echo $_SESSION['nick'] ?? 'Usuario'; ?></span>
                            </li>
                            <li class="nav-item">
                                <a class="btn btn-outline-danger btn-sm mt-1" href="cerrar_sesion.php">Cerrar sesión</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main class="container">