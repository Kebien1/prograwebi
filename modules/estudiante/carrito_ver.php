<?php
// modules/estudiante/carrito_ver.php
session_start();
require_once '../../config/bd.php';
require_once '../../includes/header.php';

// SEGURIDAD: Bloquear acceso a Administradores
if (isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 1) {
    echo '<div class="container mt-5"><div class="alert alert-danger text-center shadow p-5">
            <h1 class="display-1"><i class="bi bi-exclamation-octagon-fill"></i></h1>
            <h4 class="fw-bold mt-3">Acceso Restringido</h4>
            <p class="lead">Esta sección es exclusiva para estudiantes.</p>
            <a href="../admin/dashboard.php" class="btn btn-dark btn-lg mt-3">Volver al Panel</a>
          </div></div>';
    require_once '../../includes/footer_admin.php'; 
    exit;
}

// Obtener mensaje si existe
$mensaje = '';
if(isset($_SESSION['mensaje_carrito'])) {
    $msg = $_SESSION['mensaje_carrito'];
    $mensaje = '<div class="alert alert-'.$msg['tipo'].' alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle"></i> '.$msg['texto'].'
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
    unset($_SESSION['mensaje_carrito']);
}

// Inicializar el carrito
$carrito = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : [];

// Calcular total
$total = 0;
foreach($carrito as $c) { 
    $total += ($c['precio'] * ($c['cantidad'] ?? 1)); 
}
?>

<div class="container mt-5 mb-5">
    
    <?php echo $mensaje; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">
                <i class="bi bi-cart3"></i> Tu Carrito de Compras
            </h2>
            <p class="text-muted mb-0">
                <?php echo count($carrito); ?> producto(s) en tu carrito
            </p>
        </div>
        
        <?php if(!empty($carrito)): ?>
            <form action="carrito_acciones.php" method="POST" 
                  onsubmit="return confirm('¿Vaciar todo el carrito?');">
                <input type="hidden" name="action" value="vaciar">
                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill">
                    <i class="bi bi-trash"></i> Vaciar Carrito
                </button>
            </form>
        <?php endif; ?>
    </div>

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
            <!-- COLUMNA IZQUIERDA: Productos -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="py-3 ps-4">Producto</th>
                                    <th scope="col" class="py-3 text-center">Cantidad</th>
                                    <th scope="col" class="text-end py-3">Precio Unit.</th>
                                    <th scope="col" class="text-end py-3">Subtotal</th>
                                    <th scope="col" class="text-center py-3 pe-4">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($carrito as $index => $c): ?>
                                    <?php 
                                        $cantidad = $c['cantidad'] ?? 1;
                                        $subtotal = $c['precio'] * $cantidad;
                                    ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <?php if(!empty($c['imagen'])): ?>
                                                    <img src="../../uploads/cursos/<?php echo $c['imagen']; ?>" 
                                                         class="rounded me-3" 
                                                         style="width: 60px; height: 60px; object-fit: cover;" 
                                                         alt="Img">
                                                <?php else: ?>
                                                    <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                                         style="width: 60px; height: 60px;">
                                                        <i class="bi bi-journal-bookmark text-secondary fs-4"></i>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div>
                                                    <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($c['titulo']); ?></h6>
                                                    <small class="text-muted">
                                                        <i class="bi bi-person"></i> 
                                                        <?php echo htmlspecialchars($c['instructor'] ?? 'EduPlatform'); ?>
                                                    </small>
                                                    <br>
                                                    <span class="badge bg-light text-secondary border" style="font-size: 0.65rem;">
                                                        <?php echo strtoupper($c['tipo'] ?? 'curso'); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary"><?php echo $cantidad; ?></span>
                                        </td>
                                        <td class="text-end fw-bold">
                                            $<?php echo number_format($c['precio'], 2); ?>
                                        </td>
                                        <td class="text-end fw-bold text-primary">
                                            $<?php echo number_format($subtotal, 2); ?>
                                        </td>
                                        <td class="text-center pe-4">
                                            <form action="carrito_acciones.php" method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="eliminar">
                                                <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                                <input type="hidden" name="tipo" value="<?php echo $c['tipo'] ?? 'curso'; ?>">
                                                <button type="submit" 
                                                        class="btn btn-outline-danger btn-sm border-0" 
                                                        title="Eliminar"
                                                        onclick="return confirm('¿Eliminar este producto?');">
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
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA: Resumen -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-light sticky-top" style="top: 100px;">
                    <h4 class="fw-bold mb-4">
                        <i class="bi bi-clipboard-check"></i> Resumen de Compra
                    </h4>
                    
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Productos</span>
                            <span class="fw-bold"><?php echo count($carrito); ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-bold">$<?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mb-4 pb-3 border-bottom">
                        <span class="fs-5 fw-bold text-dark">Total a Pagar</span>
                        <span class="fs-4 fw-bold text-primary">$<?php echo number_format($total, 2); ?></span>
                    </div>

                    <form action="pasarela_pago.php" method="POST">
                        <input type="hidden" name="origen" value="carrito">
                        <button type="submit" class="btn btn-success w-100 btn-lg rounded-pill shadow fw-bold mb-3">
                            Proceder al Pago <i class="bi bi-credit-card-2-front ms-2"></i>
                        </button>
                    </form>
                    
                    <div class="text-center">
                        <small class="text-muted">
                            <i class="bi bi-shield-check"></i> Pago 100% Seguro
                        </small>
                    </div>

                    <hr class="my-4">

                    <div class="bg-white p-3 rounded">
                        <h6 class="fw-bold mb-2">
                            <i class="bi bi-gift"></i> Ventajas de comprar
                        </h6>
                        <ul class="small mb-0 ps-3">
                            <li>Acceso inmediato</li>
                            <li>Certificado incluido</li>
                            <li>Soporte 24/7</li>
                            <li>Actualizaciones gratis</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.sticky-top {
    z-index: 1020;
}
</style>

<?php require_once '../../includes/footer.php'; ?>