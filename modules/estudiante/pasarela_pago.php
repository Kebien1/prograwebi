<?php
session_start();
require_once '../../includes/security.php';
require_once '../../config/bd.php';
verificarRol(3); // Solo estudiantes

// Recibimos qué quiere comprar
$tipo = $_GET['tipo'] ?? ''; // 'curso', 'libro' o 'plan'
$id_item = $_GET['id'] ?? 0;
$precio = $_GET['precio'] ?? '0.00';
$nombre_item = $_GET['nombre'] ?? 'Producto Digital';

if (!$tipo || !$id_item) {
    header("Location: dashboard.php");
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Pasarela de Pago Segura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .credit-card { background: linear-gradient(135deg, #0d6efd, #0dcaf0); color: white; border-radius: 15px; padding: 20px; height: 200px; position: relative; box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
        .card-chip { width: 50px; height: 35px; background: #ffd700; border-radius: 5px; margin-bottom: 20px; opacity: 0.8; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-shield-lock-fill"></i> Checkout Seguro</h5>
                </div>
                <div class="card-body p-4">
                    
                    <div class="alert alert-light border d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Estás comprando:</small>
                            <h5 class="mb-0 fw-bold"><?php echo htmlspecialchars($nombre_item); ?></h5>
                            <span class="badge bg-info text-dark"><?php echo ucfirst($tipo); ?></span>
                        </div>
                        <div class="text-end">
                            <h3 class="mb-0 fw-bold text-success">$<?php echo $precio; ?></h3>
                        </div>
                    </div>

                    <form action="procesar_compra.php" method="POST" id="paymentForm">
                        <input type="hidden" name="tipo" value="<?php echo $tipo; ?>">
                        <input type="hidden" name="id" value="<?php echo $id_item; ?>">
                        
                        <div class="mb-4">
                            <div class="credit-card mb-3">
                                <div class="card-chip"></div>
                                <h4 id="displayCardNumber" class="mb-4" style="letter-spacing: 2px;">0000 0000 0000 0000</h4>
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <small class="d-block" style="font-size: 0.7rem;">TITULAR</small>
                                        <span id="displayName" class="fw-bold">NOMBRE APELLIDO</span>
                                    </div>
                                    <div>
                                        <small class="d-block" style="font-size: 0.7rem;">EXPIRA</small>
                                        <span id="displayExpiry" class="fw-bold">MM/YY</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Nombre en la tarjeta</label>
                            <input type="text" class="form-control" placeholder="Como aparece en el plástico" required oninput="document.getElementById('displayName').innerText = this.value || 'NOMBRE APELLIDO'">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Número de Tarjeta</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                                <input type="text" class="form-control" placeholder="0000 0000 0000 0000" maxlength="19" required oninput="document.getElementById('displayCardNumber').innerText = this.value || '0000 0000 0000 0000'">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-6">
                                <label class="form-label fw-bold small">Expiración (MM/YY)</label>
                                <input type="text" class="form-control" placeholder="MM/YY" maxlength="5" required oninput="document.getElementById('displayExpiry').innerText = this.value || 'MM/YY'">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small">CVV</label>
                                <input type="password" class="form-control" placeholder="123" maxlength="3" required>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold shadow">
                                Pagar Ahora <i class="bi bi-arrow-right-circle"></i>
                            </button>
                            <a href="javascript:history.back()" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                        <div class="text-center mt-3">
                            <small class="text-muted"><i class="bi bi-lock"></i> Pagos encriptados de extremo a extremo (Simulación)</small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>