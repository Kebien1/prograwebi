<?php
// modules/admin/dashboard.php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(1); // Solo permite el acceso al Administrador
require_once '../../includes/header.php';

// 1. Estadísticas de Usuarios (Originales de Admin)
$totalUsers = $conexion->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$activeSessions = $conexion->query("SELECT COUNT(*) FROM sesiones_activas")->fetchColumn();

// 2. Estadísticas Académicas (Antes eran del Docente)
$totalCursos = $conexion->query("SELECT COUNT(*) FROM cursos")->fetchColumn();
$totalLecciones = $conexion->query("SELECT COUNT(*) FROM lecciones")->fetchColumn();
?>

<div class="container mt-4">
    <h2 class="fw-bold text-dark mb-4">Panel de Control General</h2>
    
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card bg-dark text-white h-100 border-0 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase opacity-75 small">Usuarios</h6>
                        <h2 class="fw-bold mb-0"><?php echo $totalUsers; ?></h2>
                    </div>
                    <i class="bi bi-people fs-1 opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-primary text-white h-100 border-0 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase opacity-75 small">Cursos</h6>
                        <h2 class="fw-bold mb-0"><?php echo $totalCursos; ?></h2>
                    </div>
                    <i class="bi bi-collection-play fs-1 opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-success text-white h-100 border-0 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase opacity-75 small">Lecciones</h6>
                        <h2 class="fw-bold mb-0"><?php echo $totalLecciones; ?></h2>
                    </div>
                    <i class="bi bi-book fs-1 opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-info text-white h-100 border-0 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase opacity-75 small">En Línea</h6>
                        <h2 class="fw-bold mb-0"><?php echo $activeSessions; ?></h2>
                    </div>
                    <i class="bi bi-activity fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <h4 class="fw-bold mb-3 text-secondary border-bottom pb-2">Accesos Directos de Gestión</h4>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm text-center py-4">
                <div class="card-body">
                    <i class="bi bi-person-lines-fill display-4 text-dark"></i>
                    <h5 class="card-title fw-bold mt-3">Usuarios</h5>
                    <p class="text-muted small">Administrar cuentas, roles y estados.</p>
                    <a href="usuarios.php" class="btn btn-outline-dark rounded-pill mt-2 stretched-link">Gestionar Usuarios</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm text-center py-4">
                <div class="card-body">
                    <i class="bi bi-camera-video display-4 text-primary"></i>
                    <h5 class="card-title fw-bold mt-3">Cursos y Clases</h5>
                    <p class="text-muted small">Crear cursos, subir videos y lecciones.</p>
                    <a href="cursos_lista.php" class="btn btn-outline-primary rounded-pill mt-2 stretched-link">Gestionar Contenido</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm text-center py-4">
                <div class="card-body">
                    <i class="bi bi-folder-plus display-4 text-success"></i>
                    <h5 class="card-title fw-bold mt-3">Recursos y Libros</h5>
                    <p class="text-muted small">Subir archivos de apoyo y biblioteca PDF.</p>
                    <a href="recursos.php" class="btn btn-outline-success rounded-pill mt-2 stretched-link">Gestionar Archivos</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php 
require_once '../../includes/footer_admin.php'; 
?>