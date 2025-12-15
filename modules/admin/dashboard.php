<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(1);
require_once '../../includes/header.php';

$totalUsers = $conexion->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$activeSessions = $conexion->query("SELECT COUNT(*) FROM sesiones_activas")->fetchColumn();
?>

<div class="container mt-4">
    <h2 class="fw-bold text-dark mb-4">Panel de Administración</h2>
    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="card bg-primary text-white h-100 border-0 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><h6 class="text-uppercase opacity-75">Usuarios</h6><h2 class="display-4 fw-bold mb-0"><?php echo $totalUsers; ?></h2></div>
                    <i class="bi bi-people display-4 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-success text-white h-100 border-0 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><h6 class="text-uppercase opacity-75">En Línea</h6><h2 class="display-4 fw-bold mb-0"><?php echo $activeSessions; ?></h2></div>
                    <i class="bi bi-activity display-4 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <h4 class="fw-bold mb-3 text-secondary border-bottom pb-2">Accesos Directos</h4>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm text-center py-4">
                <div class="card-body">
                    <i class="bi bi-person-lines-fill display-4 text-primary"></i>
                    <h5 class="card-title fw-bold mt-3">Gestionar Usuarios</h5>
                    <a href="usuarios.php" class="btn btn-outline-primary rounded-pill mt-2 stretched-link">Ir a Usuarios</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm text-center py-4">
                <div class="card-body">
                    <i class="bi bi-globe display-4 text-info"></i>
                    <h5 class="card-title fw-bold mt-3">Sitio Web</h5>
                    <a href="../../index.php" class="btn btn-outline-info rounded-pill mt-2 stretched-link">Ir al Inicio</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>