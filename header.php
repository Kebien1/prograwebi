<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); } ?>

<!doctype html>
<html lang="es">
    <head>
        <title></title>
        <meta charset="utf-8" />
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1, shrink-to-fit=no"
        />

        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
            rel="stylesheet"
            integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
            crossorigin="anonymous"
        />
        
    </head>

    <body cl>
        <header>
            <nav class="navbar navbar-expand navbar-light bg-light ">
                <div class="container">
                <ul class="nav navbar-nav ">
                    <li class="nav-item ">
                        <a class="nav-link " href="https://indexphp.wuaze.com/dashboard.php" aria-current="page">Inicio <span class="visually-hidden">(current)</span></a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link " href="https://indexphp.wuaze.com/Roles/rol.php" aria-current="page">ROL <span class="visually-hidden">(current)</span></a>
                    </li>

                    <li class="nav-item ">
                        <a class="nav-link " href="https://indexphp.wuaze.com/Usuarios/usuarios.php" aria-current="page">Usuarios <span class="visually-hidden">(current)</span></a>
                    </li>

                    <li class="nav-item ">
                        <a class="nav-link " href="https://indexphp.wuaze.com/Permisos/permisos.php" aria-current="page">Permisos <span class="visually-hidden">(current)</span></a>
                    </li>
                </ul>
                </div>
                <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                    <a class="nav-link text-danger" href="https://indexphp.wuaze.com/cerrar_sesion.php">Cerrar sesión</a>
                    </li>
                <?php endif; ?>
                </ul>
            </nav>

        </header>
        <main class="container mt-4">
    