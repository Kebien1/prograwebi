<?php
session_start();
require_once '../../config/bd.php';
require_once '../../includes/security.php';

// Verificamos que sea estudiante (Rol 3)
// Si quisieras que admins también prueben, cambia a: if (!isset($_SESSION['usuario_id'])) ...
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 3) {
    header("Location: ../../index.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$mensaje = "";

// --- LÓGICA: CAMBIAR PLAN (Simulación de Compra) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan_id'])) {
    $nuevo_plan_id = intval($_POST['plan_id']);
    
    // Validamos que el plan exista
    $stmtCheck = $conexion->prepare("SELECT id, nombre FROM planes WHERE id = ?");
    $stmtCheck->execute([$nuevo_plan_id]);
    $planNuevo = $stmtCheck->fetch();

    if ($planNuevo) {
        // Actualizamos el usuario
        $update = $conexion->prepare("UPDATE usuarios SET plan_id = ? WHERE id = ?");
        if ($update->execute([$nuevo_plan_id, $usuario_id])) {
            // Actualizamos la variable de sesión también
            $_SESSION['plan_id'] = $nuevo_plan_id;
            $mensaje = "<div class='alert alert-success'>¡Felicidades! Te has cambiado al plan <strong>" . htmlspecialchars($planNuevo['nombre']) . "</strong> exitosamente.</div>";
            
            // Opcional: Limpiar sesiones antiguas si bajó de plan (se hará automático al próximo login, pero podemos forzarlo aquí si quieres)
        } else {
            $mensaje = "<div class='alert alert-danger'>Error al cambiar el plan.</div>";
        }
    }
}

// --- OBTENER DATOS ---

// 1. Obtener Plan Actual del Usuario
$stmtUser = $conexion->prepare("
    SELECT u.plan_id, p.nombre as nombre_plan, p.limite_sesiones 
    FROM usuarios u 
    JOIN planes p ON u.plan_id = p.id 
    WHERE u.id = ?
");
$stmtUser->execute([$usuario_id]);
$mi_suscripcion = $stmtUser->fetch();

// 2. Obtener Lista de Planes Disponibles
$planes = $conexion->query("SELECT * FROM planes ORDER BY precio ASC")->fetchAll();

require_once '../../includes/header.php';
?>

<div class="container py-5">
    
    <div class="text-center mb-5">
        <h1 class="fw-bold">Mis Suscripciones</h1>
        <p class="text-muted">Gestiona tu nivel de acceso y dispositivos simultáneos</p>
    </div>

    <?php echo $mensaje; ?>

    <div class="card bg-light border-primary mb-5 shadow-sm">
        <div class="card-body d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <h5 class="mb-0 text-primary fw-bold">Plan Actual: <?php echo htmlspecialchars($mi_suscripcion['nombre_plan']); ?></h5>
                <small class="text-muted">
                    Tienes acceso a <strong class="text-dark"><?php echo $mi_suscripcion['limite_sesiones']; ?> dispositivos</strong> simultáneos.
                </small>
            </div>
            <div class="badge bg-primary fs-6 px-3 py-2 rounded-pill">Activo</div>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($planes as $p): ?>
            <?php 
                $esActual = ($p['id'] == $mi_suscripcion['plan_id']); 
                $cardClass = $esActual ? 'border-primary shadow' : 'border-0 shadow-sm';
                $btnClass = $esActual ? 'btn-secondary disabled' : 'btn-outline-primary';
                $btnText = $esActual ? 'Plan Actual' : 'Seleccionar Plan';
            ?>
            <div class="col">
                <div class="card h-100 <?php echo $cardClass; ?> hover-scale">
                    <div class="card-header bg-white border-0 pt-4 text-center">
                        <h4 class="fw-bold mb-0"><?php echo htmlspecialchars($p['nombre']); ?></h4>
                    </div>
                    <div class="card-body text-center">
                        <h2 class="display-5 fw-bold">$<?php echo number_format($p['precio'], 2); ?></h2>
                        <span class="text-muted text-uppercase small">al mes</span>
                        
                        <hr class="my-4">
                        
                        <ul class="list-unstyled mb-4 text-start mx-auto" style="max-width: 200px;">
                            <li class="mb-2">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <strong><?php echo $p['limite_sesiones']; ?></strong> Dispositivo(s)
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Acceso al contenido
                            </li>
                            <li class="text-muted small mt-3 fst-italic">
                                <?php echo htmlspecialchars($p['descripcion']); ?>
                            </li>
                        </ul>
                    </div>
                    <div class="card-footer bg-white border-0 pb-4 text-center">
                        <?php if($esActual): ?>
                            <button class="btn btn-secondary w-100 rounded-pill" disabled>Plan Actual</button>
                        <?php else: ?>
                            <a href="pasarela_pago.php?tipo=plan&id=<?php echo $p['id']; ?>&precio=<?php echo $p['precio']; ?>&nombre=Plan <?php echo $p['nombre']; ?>" 
   class="btn btn-primary w-100 rounded-pill fw-bold">
    Seleccionar <?php echo htmlspecialchars($p['nombre']); ?>
</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>