<?php
session_start();
require_once '../../config/bd.php';

// Si no vienen datos por POST, regresar al registro
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: registro.php");
    exit;
}

// 1. Recibir datos del formulario
$nombre = trim($_POST['nombre']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$plan_id = $_POST['plan_id'];

// 2. Validar si el correo ya existe antes de cobrar
$stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->rowCount() > 0) {
    // Si ya existe, regresamos al registro con error
    echo "<script>
            alert('El correo $email ya está registrado. Intenta iniciar sesión.');
            window.location='registro.php';
          </script>";
    exit;
}

// 3. Obtener información del Plan elegido (Precio y Nombre)
$stmtPlan = $conexion->prepare("SELECT * FROM planes WHERE id = ?");
$stmtPlan->execute([$plan_id]);
$plan = $stmtPlan->fetch();

if (!$plan) {
    header("Location: registro.php");
    exit;
}

// 4. Guardar datos en SESIÓN temporalmente (para usarlos después de pagar)
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
                <p class="text-muted">Estás a un paso de acceder al plan <strong><?php echo htmlspecialchars($plan['nombre']); ?></strong>.</p>
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
                                        <div class="mb-3">
                                            <label class="form-label small text-muted">Titular de la tarjeta</label>
                                            <input type="text" class="form-control" placeholder="Nombre completo" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small text-muted">Número de tarjeta</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white"><i class="bi bi-credit-card-2-front"></i></span>
                                                <input type="text" class="form-control" placeholder="0000 0000 0000 0000" maxlength="19" required>
                                            </div>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <input type="text" class="form-control" placeholder="MM/AA" maxlength="5" required>
                                            </div>
                                            <div class="col-6">
                                                <input type="password" class="form-control" placeholder="CVV" maxlength="3" required>
                                            </div>
                                        </div>
                                        <hr>
                                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold btn-pagar">
                                            Pagar $<?php echo number_format($plan['precio'], 0); ?>
                                        </button>
                                    </form>
                                </div>

                                <div class="tab-pane fade text-center py-3" id="pills-paypal">
                                    <i class="bi bi-paypal display-4 text-primary mb-3"></i>
                                    <p class="small">Serás redirigido a PayPal para autorizar el pago de tu suscripción mensual.</p>
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
                            <h5 class="fw-bold mb-3 text-secondary">Resumen del Pedido</h5>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                                <div>
                                    <h6 class="mb-0 fw-bold">Plan <?php echo htmlspecialchars($plan['nombre']); ?></h6>
                                    <small class="text-muted">Facturación mensual</small>
                                </div>
                                <span class="fw-bold fs-5">$<?php echo number_format($plan['precio'], 0); ?></span>
                            </div>

                            <ul class="list-unstyled small text-muted mb-4 ps-2">
                                <li class="mb-2"><i class="bi bi-check-lg text-success me-2"></i> Acceso a cursos</li>
                                <li class="mb-2"><i class="bi bi-check-lg text-success me-2"></i> Libros descargables</li>
                                <li class="mb-2"><i class="bi bi-check-lg text-success me-2"></i> <?php echo $plan['limite_sesiones']; ?> Dispositivos simultáneos</li>
                            </ul>

                            <hr>
                            <div class="d-flex justify-content-between fw-bold fs-4">
                                <span>Total:</span>
                                <span>$<?php echo number_format($plan['precio'], 0); ?></span>
                            </div>
                            
                            <div class="mt-4 text-center">
                                <a href="registro.php" class="text-decoration-none small text-danger">
                                    <i class="bi bi-arrow-left"></i> Cancelar y volver
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function procesarPago(e, form) {
    e.preventDefault();
    let btn = form.querySelector('.btn-pagar');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Procesando...';
    
    // Simular tiempo de banco
    setTimeout(() => {
        form.submit();
    }, 2000);
}
</script>

</body>
</html>