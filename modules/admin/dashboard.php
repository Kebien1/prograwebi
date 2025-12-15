<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(1); // Solo Admin (ID 1)
require_once '../../includes/header.php';

// --- CONSULTAS DE ESTADÍSTICAS ---
try {
    // 1. Total usuarios registrados
    $totalUsers = $conexion->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    
    // 2. Sesiones activas en este momento
    $activeSessions = $conexion->query("SELECT COUNT(*) FROM sesiones_activas")->fetchColumn();
    
    // 3. Ventas totales (Manejo de error si la tabla está vacía)
    $stmtSales = $conexion->query("SELECT SUM(monto_pagado) FROM compras");
    $totalSales = $stmtSales ? ($stmtSales->fetchColumn() ?: 0) : 0;

} catch (Exception $e) {
    // Si falla algo, mostramos ceros para no romper la página
    $totalUsers = 0; $activeSessions = 0; $totalSales = 0;
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark mb-0"><i class="bi bi-speedometer2"></i> Panel de Administración</h2>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card bg-primary text-white h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-2 opacity-75">Usuarios Totales</h6>
                            <h2 class="display-4 fw-bold mb-0"><?php echo $totalUsers; ?></h2>
                        </div>
                        <i class="bi bi-people display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-success text-white h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-2 opacity-75">En Línea Ahora</h6>
                            <h2 class="display-4 fw-bold mb-0"><?php echo $activeSessions; ?></h2>
                            <small class="opacity-75">Dispositivos conectados</small>
                        </div>
                        <i class="bi bi-activity display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-dark text-white h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-2 opacity-75">Ingresos Totales</h6>
                            <h2 class="display-4 fw-bold mb-0">$<?php echo number_format($totalSales, 0); ?></h2>
                        </div>
                        <i class="bi bi-currency-dollar display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h4 class="fw-bold mb-3 text-secondary border-bottom pb-2">Gestión de la Plataforma</h4>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        
        <div class="col">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center py-4">
                    <div class="mb-3">
                        <i class="bi bi-person-lines-fill display-4 text-primary"></i>
                    </div>
                    <h5 class="card-title fw-bold">Usuarios</h5>
                    <p class="card-text text-muted small">Administrar estudiantes, docentes y sus accesos.</p>
                    <a href="usuarios.php" class="btn btn-outline-primary rounded-pill w-100 stretched-link">
                        Gestionar Usuarios
                    </a>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center py-4">
                    <div class="mb-3">
                        <i class="bi bi-tags-fill display-4 text-warning"></i>
                    </div>
                    <h5 class="card-title fw-bold">Planes</h5>
                    <p class="card-text text-muted small">Configurar precios y límites de sesiones simultáneas.</p>
                    <a href="planes.php" class="btn btn-outline-warning text-dark rounded-pill w-100 stretched-link">
                        Gestionar Planes
                    </a>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center py-4">
                    <div class="mb-3">
                        <i class="bi bi-globe display-4 text-info"></i>
                    </div>
                    <h5 class="card-title fw-bold">Sitio Web</h5>
                    <p class="card-text text-muted small">Ver cómo ven la página los visitantes.</p>
                    <a href="../../index.php" class="btn btn-outline-info text-dark rounded-pill w-100 stretched-link">
                        Ir al Inicio
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>