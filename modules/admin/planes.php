<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(1);
require_once '../../includes/header.php';

$planes = $conexion->query("SELECT * FROM planes ORDER BY precio ASC")->fetchAll();
?>

<div class="container mt-4">
    <div class="mb-4 text-center">
        <h2 class="fw-bold text-dark">Configuración de Planes</h2>
        <p class="text-muted">Gestiona los precios y límites de dispositivos para tus estudiantes.</p>
    </div>

    <div class="row row-cols-1 row-cols-md-3 g-4 justify-content-center">
        <?php foreach ($planes as $p): ?>
        <div class="col">
            <div class="card h-100 shadow-sm border-0 hover-shadow transition-card">
                <div class="card-header py-3 text-center bg-white border-bottom-0">
                    <h4 class="my-0 fw-bold text-primary"><?php echo htmlspecialchars($p['nombre']); ?></h4>
                </div>
                
                <div class="card-body d-flex flex-column text-center">
                    <h1 class="card-title pricing-card-title mb-4">
                        $<?php echo number_format($p['precio'], 0); ?>
                        <small class="text-muted fw-light fs-5">/mes</small>
                    </h1>
                    
                    <ul class="list-unstyled mt-3 mb-4 flex-grow-1">
                        <li class="mb-3">
                            <span class="badge bg-primary bg-opacity-10 text-primary p-2 rounded-pill">
                                <i class="bi bi-laptop"></i> <?php echo $p['limite_sesiones']; ?> Dispositivo(s)
                            </span>
                        </li>
                        <li class="text-muted small px-3">
                            <?php echo htmlspecialchars($p['descripcion']); ?>
                        </li>
                    </ul>

                    <a href="plan_editar.php?id=<?php echo $p['id']; ?>" class="w-100 btn btn-outline-primary rounded-pill">
                        <i class="bi bi-pencil-fill"></i> Editar Plan
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>