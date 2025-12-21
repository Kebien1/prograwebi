<?php
session_start();
require_once '../../config/bd.php';
require_once '../../includes/header.php'; // Usa tu header existente

// Recuperamos el carrito de la sesión. Si no existe, usamos un array vacío.
$carrito = $_SESSION['carrito'] ?? [];
$total = 0;

// Calculamos el total sumando el precio de cada ítem
foreach($carrito as $item) { 
    $total += $item['precio']; 
}
?>

<div class="container mt-5 mb-5">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold"><i class="bi bi-cart3"></i> Tu Carrito de Compras</h2>
            <p class="text-muted">Revisa tus cursos y clases antes de confirmar.</p>
        </div>
    </div>

    <?php if(empty($carrito)): ?>
        <div class="text-center py-5 bg-light rounded shadow-sm">
            <i class="bi bi-basket2 display-1 text-muted opacity-25"></i>
            <h4 class="mt-4 text-muted">Tu carrito está vacío</h4>
            <p>Parece que aún no has agregado clases o cursos.</p>
            <a href="catalogo.php" class="btn btn-primary rounded-pill px-4 mt-2">Ir al Catálogo</a>
        </div>

    <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3">Producto</th>
                                    <th>Tipo</th>
                                    <th class="text-end">Precio</th>
                                    <th class="text-end pe-4">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($carrito as $c): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark">
                                            <?php echo htmlspecialchars($c['titulo']); ?>
                                        </td>
                                        <td>
                                            <?php if($c['tipo'] == 'curso'): ?>
                                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">Curso Completo</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning bg-opacity-10 text-dark rounded-pill">Clase Individual</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end fw-bold">
                                            $<?php echo number_format($c['precio'], 2); ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="carrito_acciones.php?eliminar_id=<?php echo $c['id']; ?>&tipo=<?php echo $c['tipo']; ?>" 
                                               class="btn btn-sm btn-outline-danger border-0" 
                                               title="Eliminar del carrito">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="catalogo.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Seguir viendo cursos
                    </a>
                    <a href="carrito_acciones.php?vaciar=1" class="btn btn-link text-danger text-decoration-none">
                        Vaciar Carrito
                    </a>
                </div>
            </div>

            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Resumen del Pedido</h5>
                        
                        <div class="d-flex justify-content-between mb-2 text-muted">
                            <span>Subtotal</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold fs-5">Total a Pagar</span>
                            <span class="fw-bold fs-4 text-primary">$<?php echo number_format($total, 2); ?></span>
                        </div>

                        <form action="checkout_procesar.php" method="POST">
                            <button type="submit" class="btn btn-success w-100 btn-lg rounded shadow-sm fw-bold">
                                Confirmar y Pagar <i class="bi bi-credit-card ms-2"></i>
                            </button>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <small class="text-muted"><i class="bi bi-shield-lock"></i> Pago 100% seguro</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer_admin.php'; ?>