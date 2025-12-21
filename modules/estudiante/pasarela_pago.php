<?php
session_start();
require_once '../../includes/security.php';
require_once '../../config/bd.php';
verificarRol(3); // Solo estudiantes

// --- LÓGICA DE DETECCIÓN DE COMPRA ---

$es_carrito = false;
$nombre_item = "";
$precio = 0;
$tipo_compra = "";
$archivo_destino = ""; // A dónde se envían los datos al hacer clic en Pagar

// CASO 1: Viene del Carrito (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['origen']) && $_POST['origen'] == 'carrito') {
    $es_carrito = true;
    
    // Verificar que el carrito no esté vacío
    if (empty($_SESSION['carrito'])) {
        header("Location: carrito_ver.php");
        exit;
    }

    // Calcular el total de todo lo que hay en el carrito
    $total_carrito = 0;
    foreach ($_SESSION['carrito'] as $c) {
        $total_carrito += $c['precio'];
    }

    $precio = number_format($total_carrito, 2);
    $cantidad_cursos = count($_SESSION['carrito']);
    $nombre_item = "Compra de $cantidad_cursos curso(s)";
    $tipo_compra = 'carrito';
    
    // Al pagar carrito, vamos a checkout_procesar.php (que procesa múltiples ítems)
    $archivo_destino = "checkout_procesar.php";

} 
// CASO 2: Viene de Suscripción o Compra Individual (GET)
else {
    $tipo = $_GET['tipo'] ?? ''; 
    $id_item = $_GET['id'] ?? 0;
    $precio_raw = $_GET['precio'] ?? 0;
    $nombre_raw = $_GET['nombre'] ?? 'Producto Digital';

    // Validación básica
    if (!$tipo || !$id_item) {
        header("Location: dashboard.php");
        exit;
    }

    $precio = number_format($precio_raw, 2);
    $nombre_item = $nombre_raw;
    $tipo_compra = $tipo; // 'plan', 'curso', 'libro'
    
    // Al pagar suscripción/item individual, vamos a procesar_compra.php (que procesa un solo ítem)
    $archivo_destino = "procesar_compra.php";
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
        .credit-card { 
            background: linear-gradient(135deg, #212529, #495057); 
            color: white; 
            border-radius: 15px; 
            padding: 20px; 
            height: 200px; 
            position: relative; 
            box-shadow: 0 10px 20px rgba(0,0,0,0.2); 
        }
        .card-chip { 
            width: 50px; 
            height: 35px; 
            background: linear-gradient(to bottom right, #bf953f, #fcf6ba); 
            border-radius: 5px; 
            margin-bottom: 20px; 
        }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-white py-3 text-center border-bottom-0">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-wallet2 text-primary me-2"></i>Detalles de Pago
                    </h5>
                </div>
                <div class="card-body p-4">
                    
                    <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-light rounded-3">
                        <div>
                            <small class="text-muted d-block">Producto</small>
                            <span class="fw-bold text-dark"><?php echo htmlspecialchars($nombre_item); ?></span>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block">Total</small>
                            <span class="h4 fw-bold text-primary mb-0">$<?php echo $precio; ?></span>
                        </div>
                    </div>

                    <form action="<?php echo $archivo_destino; ?>" method="POST" id="paymentForm">
                        
                        <?php if($es_carrito): ?>
                            <input type="hidden" name="origen" value="carrito">
                        <?php else: ?>
                            <input type="hidden" name="tipo" value="<?php echo $tipo_compra; ?>">
                            <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>">
                        <?php endif; ?>

                        <div class="mb-4">
                            <div class="credit-card mb-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="card-chip"></div>
                                    <i class="bi bi-wifi fs-3 opacity-50"></i>
                                </div>
                                <h4 id="displayCardNumber" class="mb-4 text-center font-monospace" style="letter-spacing: 2px;">0000 0000 0000 0000</h4>
                                <div class="d-flex justify-content-between px-2">
                                    <div>
                                        <small class="d-block text-white-50" style="font-size: 0.6rem;">TITULAR</small>
                                        <span id="displayName" class="fw-bold text-uppercase small">NOMBRE APELLIDO</span>
                                    </div>
                                    <div>
                                        <small class="d-block text-white-50" style="font-size: 0.6rem;">EXPIRA</small>
                                        <span id="displayExpiry" class="fw-bold small">MM/YY</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Nombre en la tarjeta</label>
                            <input type="text" class="form-control" placeholder="Como aparece en el plástico" required 
                                   oninput="document.getElementById('displayName').innerText = this.value || 'NOMBRE APELLIDO'">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Número de Tarjeta</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-credit-card"></i></span>
                                <input type="text" class="form-control" placeholder="0000 0000 0000 0000" maxlength="19" required 
                                       oninput="document.getElementById('displayCardNumber').innerText = this.value || '0000 0000 0000 0000'">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-6">
                                <label class="form-label fw-bold small">Expiración</label>
                                <input type="text" class="form-control" placeholder="MM/YY" maxlength="5" required 
                                       oninput="document.getElementById('displayExpiry').innerText = this.value || 'MM/YY'">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small">CVV</label>
                                <input type="password" class="form-control" placeholder="123" maxlength="3" required>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm">
                                Pagar $<?php echo $precio; ?> <i class="bi bi-arrow-right-circle ms-2"></i>
                            </button>
                            <a href="javascript:history.back()" class="btn btn-outline-secondary border-0">Cancelar</a>
                        </div>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted" style="font-size: 0.75rem;">
                                <i class="bi bi-lock-fill"></i> Transacción encriptada y segura.
                            </small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Script para dar formato automático al número de tarjeta (espacios cada 4 dígitos)
document.querySelector('input[placeholder="0000 0000 0000 0000"]').addEventListener('input', function (e) {
    e.target.value = e.target.value.replace(/[^\d]/g, '').replace(/(.{4})/g, '$1 ').trim();
});
</script>

</body>
</html>