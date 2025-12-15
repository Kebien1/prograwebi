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
                                💳 Tarjeta (Simulación)
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-bold text-dark" data-bs-toggle="tab" data-bs-target="#tab-paypal" type="button">
                                🅿️ PayPal
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
                                
                                <div class="text-center py-4">
                                    <i class="bi bi-credit-card-2-front display-1 text-muted opacity-25"></i>
                                    <p class="mt-3 text-muted">Haz clic en pagar para simular la transacción.</p>
                                </div>

                                <button type="submit" class="btn btn-success w-100 py-2 fw-bold btn-pagar">
                                    Confirmar Pago de $<?php echo number_format($precio, 0); ?>
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
function simularCarga(e, form) {
    e.preventDefault();
    let boton = form.querySelector('.btn-pagar');
    boton.disabled = true;
    boton.innerText = "Procesando pago...";
    setTimeout(function() {
        form.submit();
    }, 2000);
}
</script>

<?php require_once '../../includes/footer.php'; ?>