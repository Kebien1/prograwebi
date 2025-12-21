<?php
session_start();
require_once '../../config/bd.php';
require_once '../../includes/header.php';

// Inicializar el carrito si no existe
$carrito = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : [];

// Calcular total
$total = 0;
foreach($carrito as $c) { 
    $total += $c['precio']; 
}
?>

<div class="container mt-5 mb-5">
    <h2 class="mb-4 fw-bold"><i class="bi bi-cart3"></i> Tu Carrito de Compras</h2>

    <?php if(empty($carrito)): ?>
        <div class="alert alert-info py-5 text-center shadow-sm rounded-4">
            <i class="bi bi-cart-x display-1 text-info mb-3"></i>
            <h4 class="fw-bold">Tu carrito está vacío</h4>
            <p class="text-muted">Parece que aún no has agregado ningún curso.</p>
            <a href="catalogo.php" class="btn btn-primary mt-3 rounded-pill px-4 fw-bold">
                <i class="bi bi-search"></i> Explorar Catálogo
            </a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="py-3 ps-4">Producto</th>
                                    <th scope="col" class="py-3">Instructor</th>
                                    <th scope="col" class="text-end py-3">Precio</th>
                                    <th scope="col" class="text-center py-3 pe-4">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($carrito as $c): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <?php if(!empty($c['imagen'])): ?>
                                                    <img src="../../uploads/cursos/<?php echo $c['imagen']; ?>" 
                                                         class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;" 
                                                         alt="Curso">
                                                <?php else: ?>
                                                    <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                        <i class="bi bi-book text-secondary"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($c['titulo']); ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-muted small">
                                            <?php echo htmlspecialchars($c['instructor']); ?>
                                        </td>
                                        <td class="text-end fw-bold">
                                            $<?php echo number_format($c['precio'], 2); ?>
                                        </td>
                                        <td class="text-center pe-4">
                                            <form action="carrito_acciones.php" method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="eliminar">
                                                <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm border-0" title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            
                            <a href="catalogo.php" class="btn btn-outline-secondary rounded-pill">
                                <i class="bi bi-arrow-left"></i> Seguir Comprando
                            </a>

                            <form action="carrito_acciones.php" method="POST" class="d-inline">
                                <input type="hidden" name="action" value="vaciar">
                                <button type="submit" class="btn btn-link text-danger text-decoration-none small">
                                    Vaciar Carrito
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-light">
                    <h4 class="fw-bold mb-4">Resumen</h4>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-bold">$<?php echo number_format($total, 2); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fs-5 fw-bold text-dark">Total</span>
                        <span class="fs-4 fw-bold text-primary">$<?php echo number_format($total, 2); ?></span>
                    </div>

                    <form action="pasarela_pago.php" method="POST">
                        <input type="hidden" name="origen" value="carrito">
                        <button type="submit" class="btn btn-success w-100 btn-lg rounded-pill shadow fw-bold">
                            Proceder al Pago <i class="bi bi-credit-card-2-front ms-2"></i>
                        </button>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <small class="text-muted"><i class="bi bi-shield-lock"></i> Compra Segura</small>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer_admin.php'; ?>