<?php
session_start();
require_once '../../config/bd.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: registro.php");
    exit;
}

$nombre = trim($_POST['nombre']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$plan_id = $_POST['plan_id'];

// Validar correo
$stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->rowCount() > 0) {
    echo "<script>alert('El correo $email ya está registrado.'); window.location='registro.php';</script>";
    exit;
}

$stmtPlan = $conexion->prepare("SELECT * FROM planes WHERE id = ?");
$stmtPlan->execute([$plan_id]);
$plan = $stmtPlan->fetch();

if (!$plan) {
    header("Location: registro.php");
    exit;
}

$_SESSION['temp_registro'] = [
    'nombre' => $nombre,
    'email' => $email,
    'password' => $password,
    'plan_id' => $plan_id
];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pago de Suscripción - EduPlatform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="text-center mb-4">
                <h2 class="fw-bold">Finalizar Suscripción</h2>
                <p class="text-muted">Plan seleccionado: <strong><?php echo htmlspecialchars($plan['nombre']); ?></strong>.</p>
            </div>

            <div class="alert alert-warning text-center">
                <i class="bi bi-shield-lock"></i> <strong>Aviso:</strong> No ingreses datos reales de tarjetas. Este es un entorno de pruebas.
            </div>

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-credit-card"></i> Método de Pago</h5>
                        </div>
                        <div class="card-body p-4">
                            <ul class="nav nav-pills mb-3 nav-fill" id="pills-tab" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#pills-card" type="button">Tarjeta</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-paypal" type="button">PayPal</button>
                                </li>
                            </ul>

                            <div class="tab-content" id="pills-tabContent">
                                <div class="tab-pane fade show active" id="pills-card">
                                    <form action="finalizar_registro.php" method="POST" onsubmit="procesarPago(event, this)">
                                        <div class="text-center py-3">
                                            <p class="text-muted">Simulación de pago con tarjeta.</p>
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold btn-pagar">
                                            Confirmar Suscripción ($<?php echo number_format($plan['precio'], 0); ?>)
                                        </button>
                                    </form>
                                </div>

                                <div class="tab-pane fade text-center py-3" id="pills-paypal">
                                    <i class="bi bi-paypal display-4 text-primary mb-3"></i>
                                    <form action="finalizar_registro.php" method="POST" onsubmit="procesarPago(event, this)">
                                        <button type="submit" class="btn btn-warning w-100 fw-bold text-white btn-pagar">
                                            Pagar con PayPal
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card shadow-sm border-0 bg-white">
                        <div class="card-body">
                            <h5 class="fw-bold mb-3 text-secondary">Resumen</h5>
                            <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                                <div>
                                    <h6 class="mb-0 fw-bold">Plan <?php echo htmlspecialchars($plan['nombre']); ?></h6>
                                    <small class="text-muted">Mensual</small>
                                </div>
                                <span class="fw-bold fs-5">$<?php echo number_format($plan['precio'], 0); ?></span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold fs-4 mt-3">
                                <span>Total:</span>
                                <span>$<?php echo number_format($plan['precio'], 0); ?></span>
                            </div>
                            <div class="mt-4 text-center">
                                <a href="registro.php" class="text-decoration-none small text-danger">Cancelar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function procesarPago(e, form) {
    e.preventDefault();
    let btn = form.querySelector('.btn-pagar');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Procesando...';
    setTimeout(() => { form.submit(); }, 2000);
}
</script>

</body>
</html>