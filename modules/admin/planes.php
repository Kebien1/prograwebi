<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(1); // Solo Admin
require_once '../../includes/header.php';

// Lógica para eliminar un plan
if (isset($_GET['borrar'])) {
    $id = $_GET['borrar'];
    // Evitamos borrar el plan 1 (Básico) por seguridad, ya que es el default
    if ($id != 1) {
        // Antes de borrar, pasamos a los usuarios de este plan al plan básico (1) para no dejarlos huérfanos
        $conexion->prepare("UPDATE usuarios SET plan_id = 1 WHERE plan_id = ?")->execute([$id]);
        
        // Ahora sí borramos el plan
        $conexion->prepare("DELETE FROM planes WHERE id = ?")->execute([$id]);
    }
    echo "<script>window.location='planes.php';</script>";
    exit;
}

// --- CONSULTA DE PLANES ---
// Corrección: Usamos una sola consulta y ordenamos por 'precio' (o 'id' si prefieres)
try {
    $planes = $conexion->query("SELECT * FROM planes ORDER BY precio ASC")->fetchAll();
} catch (Exception $e) {
    // Si falla (por ejemplo si la columna se llama diferente), mostramos el error
    die("Error al cargar planes: " . $e->getMessage());
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark"><i class="bi bi-credit-card-2-front text-primary"></i> Planes de Suscripción</h2>
        <a href="planes_editar.php" class="btn btn-primary rounded-pill">
            <i class="bi bi-plus-lg"></i> Nuevo Plan
        </a>
    </div>

    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($planes as $p): ?>
            <div class="col">
                <div class="card h-100 shadow-sm border-0 hover-scale">
                    <div class="card-header bg-white border-0 pt-4 text-center">
                        <h4 class="fw-bold mb-0 text-primary"><?php echo htmlspecialchars($p['nombre']); ?></h4>
                    </div>
                    <div class="card-body text-center">
                        <h2 class="display-4 fw-bold text-dark">$<?php echo number_format($p['precio'], 2); ?></h2>
                        <span class="text-muted text-uppercase small fw-bold">Mensual</span>
                        
                        <hr class="my-4">
                        
                        <ul class="list-unstyled mb-4">
                            <li class="mb-3">
                                <i class="bi bi-laptop text-success me-2"></i>
                                <strong><?php echo $p['limite_sesiones']; ?></strong> Dispositivo(s) simultáneo(s)
                            </li>
                            <li class="text-muted small px-3">
                                <?php echo htmlspecialchars($p['descripcion']); ?>
                            </li>
                        </ul>
                    </div>
                    <div class="card-footer bg-white border-0 pb-4 text-center">
                        <a href="planes_editar.php?id=<?php echo $p['id']; ?>" class="btn btn-outline-primary rounded-pill px-4">
                            Editar Plan
                        </a>
                        <?php if($p['id'] != 1): // Proteger el plan base ?>
                            <a href="planes.php?borrar=<?php echo $p['id']; ?>" class="btn btn-outline-danger rounded-pill px-3 ms-2" onclick="return confirm('¿Borrar plan? Los usuarios pasarán al Plan Básico.');">
                                <i class="bi bi-trash"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
