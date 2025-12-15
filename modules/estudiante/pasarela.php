<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(3); // Solo estudiantes
require_once '../../includes/header.php';

// 1. Recibimos qué quiere comprar el usuario
$tipo = $_GET['tipo'] ?? '';
$id = $_GET['id'] ?? 0;
$precio = $_GET['precio'] ?? 0;

// 2. Buscamos el nombre del producto para mostrarlo en el resumen
$nombreProducto = "Producto desconocido";
if ($tipo == 'curso') {
    $stmt = $conexion->prepare("SELECT titulo FROM cursos WHERE id = ?");
    $stmt->execute([$id]);
    $producto = $stmt->fetch();
    if ($producto) $nombreProducto = $producto['titulo'];
} elseif ($tipo == 'libro') {
    $stmt = $conexion->prepare("SELECT titulo FROM libros WHERE id = ?");
    $stmt->execute([$id]);
    $producto = $stmt->fetch();
    if ($producto) $nombreProducto = $producto['titulo'];
}

// Si faltan datos, lo regresamos al catálogo
if (!$id || !$tipo) {
    echo "<script>window.location='catalogo.php';</script>";
    exit;
}
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8">
            <h4 class="mb-4 fw-bold">Elige tu método de pago</h4>
            
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <ul class="nav nav-tabs card-header-tabs" id="misTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active fw-bold text-dark" data-bs-toggle="tab" data-bs-target="#tab-tarjeta" type="button">
                                💳 Tarjeta
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-bold text-dark" data-bs-toggle="tab" data-bs-target="#tab-paypal" type="button">
                                🅿️ PayPal
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-bold text-dark" data-bs-toggle="tab" data-bs-target="#tab-transferencia" type="button">
                                🏦 Transferencia
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-4">
                    <div class="tab-content">
                        
                        <div class="tab-pane fade show active" id="tab-tarjeta">
                            <form action="procesar_compra.php" method="POST" onsubmit="simularCarga(event, this)">
                                <input type="hidden" name="tipo" value="<?php echo $tipo; ?>">
                                <input type="hidden" name="id" value="<?php echo $id; ?>">
                                <input type="hidden" name="precio" value="<?php echo $precio; ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nombre en la tarjeta</label>
                                    <input type="text" class="form-control" placeholder="Ej: Juan Pérez" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Número de tarjeta</label>
                                    <input type="text" class="form-control" placeholder="0000 0000 0000 0000" required maxlength="19">
                                </div>
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label class="form-label fw-bold">Vencimiento</label>
                                        <input type="text" class="form-control" placeholder="MM/AA" required maxlength="5">
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label class="form-label fw-bold">CVV</label>
                                        <input type="password" class="form-control" placeholder="123" required maxlength="3">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success w-100 py-2 fw-bold btn-pagar">
                                    Pagar $<?php echo number_format($precio, 0); ?>
                                </button>
                            </form>
                        </div>

                        <div class="tab-pane fade text-center" id="tab-paypal">
                            <div class="py-4">
                                <h5 class="fw-bold mb-3">Pagar con PayPal</h5>
                                <p class="text-muted">Serás redirigido para completar el pago de forma segura.</p>
                                <form action="procesar_compra.php" method="POST" onsubmit="simularCarga(event, this)">
                                    <input type="hidden" name="tipo" value="<?php echo $tipo; ?>">
                                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                                    <input type="hidden" name="precio" value="<?php echo $precio; ?>">
                                    
                                    <button type="submit" class="btn btn-primary w-50 py-2 fw-bold btn-pagar">
                                        Ir a PayPal
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-transferencia">
                            <div class="alert alert-warning">
                                <strong>Instrucciones:</strong> Transfiere al Banco Ficticio, Cuenta: 123-456-7890.
                            </div>
                            <form action="procesar_compra.php" method="POST" onsubmit="simularCarga(event, this)">
                                <input type="hidden" name="tipo" value="<?php echo $tipo; ?>">
                                <input type="hidden" name="id" value="<?php echo $id; ?>">
                                <input type="hidden" name="precio" value="<?php echo $precio; ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Número de comprobante</label>
                                    <input type="text" class="form-control" placeholder="Ingresa el código de operación" required>
                                </div>
                                <button type="submit" class="btn btn-secondary w-100 py-2 fw-bold btn-pagar">
                                    Confirmar Transferencia
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <h4 class="mb-4 fw-bold text-muted">Resumen</h4>
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body">
                    <h5 class="card-title fw-bold text-primary"><?php echo htmlspecialchars($nombreProducto); ?></h5>
                    <span class="badge bg-secondary mb-3"><?php echo ucfirst($tipo); ?></span>
                    
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($precio, 0); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-success">Descuento:</span>
                        <span class="text-success">-$0.00</span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Total a Pagar:</span>
                        <span>$<?php echo number_format($precio, 0); ?></span>
                    </div>
                </div>
            </div>
            <div class="mt-3 text-center">
                <a href="catalogo.php" class="text-decoration-none text-muted small">Cancelar y volver</a>
            </div>
        </div>
    </div>
</div>

<script>
// Script sencillo para simular que está "pensando"
function simularCarga(e, form) {
    e.preventDefault(); // Detenemos el envío inmediato
    
    // Buscamos el botón dentro del formulario
    let boton = form.querySelector('.btn-pagar');
    let textoOriginal = boton.innerText;
    
    // Cambiamos apariencia
    boton.disabled = true;
    boton.innerText = "Procesando pago...";
    
    // Esperamos 2 segundos y enviamos
    setTimeout(function() {
        form.submit(); // Ahora sí enviamos de verdad
    }, 2000);
}
</script>

<?php require_once '../../includes/footer.php'; ?>