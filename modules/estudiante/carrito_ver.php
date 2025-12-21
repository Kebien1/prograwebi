<?php
session_start();
require_once '../../config/bd.php';
require_once '../../includes/header.php';

$carrito = $_SESSION['carrito'] ?? [];
$total = 0;
foreach($carrito as $c) { $total += $c['precio']; }
?>

<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0"><i class="bi bi-cart"></i> Carrito de Compras</h2>
        <a href="catalogo.php" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Seguir Comprando
        </a>
    </div>

    <?php if(empty($carrito)): ?>
        <div class="alert alert-info py-5 text-center">
            <h4>Tu carrito está vacío</h4>
            <p class="text-muted">Parece que aún no has agregado ningún curso.</p>
            <a href="catalogo.php" class="btn btn-primary mt-3">Ir al Catálogo</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-8">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th>Tipo</th>
                                <th class="text-end">Precio</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($carrito as $c): ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold"><?php echo htmlspecialchars($c['titulo']); ?></span>
                                    </td>
                                    <td><span class="badge bg-secondary"><?php echo ucfirst($c['tipo']); ?></span></td>
                                    <td class="text-end">$<?php echo number_format($c['precio'], 2); ?></td>
                                    <td class="text-center">
                                        <a href="carrito_acciones.php?eliminar_id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-end mt-3">
                    <a href="carrito_acciones.php?vaciar=1" class="text-danger small text-decoration-none">
                        <i class="bi bi-x-circle"></i> Vaciar carrito
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light border-0 p-4 rounded-4 shadow-sm">
                    <h4 class="fw-bold">Resumen</h4>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold fs-5">Total a Pagar:</span>
                        <span class="fw-bold fs-4 text-primary">$<?php echo number_format($total, 2); ?></span>
                    </div>
                    <form action="checkout_procesar.php" method="POST">
                        <button class="btn btn-success w-100 btn-lg shadow fw-bold">
                            <i class="bi bi-credit-card"></i> Pagar Ahora
                        </button>
                    </form>
                    <div class="text-center mt-3">
                        <small class="text-muted"><i class="bi bi-shield-lock"></i> Pago 100% Seguro</small>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer_admin.php'; ?>