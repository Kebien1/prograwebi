<?php
session_start();
require_once '../../config/bd.php';
require_once '../../includes/header.php';

$carrito = $_SESSION['carrito'] ?? [];
$total = 0;
foreach($carrito as $c) { $total += $c['precio']; }
?>

<div class="container mt-5 mb-5">
    <h2 class="mb-4"><i class="bi bi-cart"></i> Carrito de Compras</h2>

    <?php if(empty($carrito)): ?>
        <div class="alert alert-info py-5 text-center">
            <h4>Tu carrito está vacío</h4>
            <a href="catalogo.php" class="btn btn-primary mt-3">Ir al Catálogo</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-8">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr><th>Producto</th><th>Tipo</th><th class="text-end">Precio</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($carrito as $c): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($c['titulo']); ?></td>
                                <td><span class="badge bg-primary"><?php echo ucfirst($c['tipo']); ?></span></td>
                                <td class="text-end">$<?php echo number_format($c['precio'], 2); ?></td>
                                <td class="text-center">
                                    <a href="carrito_acciones.php?eliminar_id=<?php echo $c['id']; ?>" class="text-danger"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <a href="carrito_acciones.php?vaciar=1" class="text-danger small">Vaciar carrito</a>
            </div>
            <div class="col-md-4">
                <div class="card bg-light border-0 p-4">
                    <h4>Resumen</h4>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold">Total a Pagar:</span>
                        <span class="fw-bold fs-4 text-primary">$<?php echo number_format($total, 2); ?></span>
                    </div>
                    <form action="checkout_procesar.php" method="POST">
                        <button class="btn btn-success w-100 btn-lg shadow">Pagar Ahora</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer_admin.php'; ?>