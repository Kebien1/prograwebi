<?php
session_start();
require_once '../../config/bd.php';
require_once '../../includes/header.php';

// 1. Obtener la lista de cursos disponibles
$stmt = $conexion->prepare("SELECT * FROM cursos");
$stmt->execute();
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Obtener los cursos que el usuario YA compró
$ids_comprados = [];
if (isset($_SESSION['usuario_id'])) {
    $uid = $_SESSION['usuario_id'];
    // Buscamos en la tabla 'compras' solo los que sean tipo 'curso'
    $stmtCompras = $conexion->prepare("SELECT item_id FROM compras WHERE usuario_id = ? AND tipo_item = 'curso'");
    $stmtCompras->execute([$uid]);
    // Guardamos los IDs en un array simple (ej: [1, 5, 8])
    $ids_comprados = $stmtCompras->fetchAll(PDO::FETCH_COLUMN);
}

?>

<div class="container mt-5 mb-5">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h2 class="fw-bold"><i class="bi bi-grid-fill text-primary"></i> Catálogo de Cursos</h2>
            <p class="text-muted">Explora y aprende nuevas habilidades hoy.</p>
        </div>
        <div class="col-auto">
            <form class="d-flex" role="search">
                <input class="form-control me-2 rounded-pill" type="search" placeholder="Buscar curso..." aria-label="Search">
                <button class="btn btn-outline-primary rounded-circle" type="submit"><i class="bi bi-search"></i></button>
            </form>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($cursos as $curso): ?>
            <?php 
                // Verificar si este curso específico ya fue comprado
                $yaComprado = in_array($curso['id'], $ids_comprados);
            ?>
            <div class="col">
                <div class="card h-100 shadow-sm border-0 rounded-4 transition-hover">
                    <div class="position-relative">
                        <?php if (!empty($curso['imagen'])): ?>
                            <img src="../../uploads/cursos/<?php echo htmlspecialchars($curso['imagen']); ?>" 
                                 class="card-img-top rounded-top-4" style="height: 200px; object-fit: cover;" alt="Curso">
                        <?php else: ?>
                            <div class="bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center rounded-top-4" style="height: 200px;">
                                <i class="bi bi-book display-1 text-secondary opacity-25"></i>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($yaComprado): ?>
                            <span class="position-absolute top-0 end-0 m-3 badge rounded-pill bg-success shadow">
                                <i class="bi bi-check-circle-fill"></i> Inscrito
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold text-dark"><?php echo htmlspecialchars($curso['titulo']); ?></h5>
                        <p class="card-text text-muted small flex-grow-1">
                            <?php echo htmlspecialchars(substr($curso['descripcion'] ?? 'Sin descripción', 0, 100)) . '...'; ?>
                        </p>
                        
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-person-circle text-secondary me-2"></i>
                                <small class="text-muted"><?php echo htmlspecialchars($curso['instructor'] ?? 'EduPlatform'); ?></small>
                            </div>
                            <span class="fw-bold text-primary fs-5">$<?php echo number_format($curso['precio'], 2); ?></span>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-0 pb-4 pt-0">
                        <div class="d-grid gap-2">
                            <?php if ($yaComprado): ?>
                                <a href="aula.php?id=<?php echo $curso['id']; ?>" class="btn btn-outline-success rounded-pill fw-bold">
                                    <i class="bi bi-play-circle-fill me-2"></i> Ir al Aula
                                </a>
                            <?php else: ?>
                                <form action="carrito_acciones.php" method="POST">
                                    <input type="hidden" name="action" value="agregar">
                                    <input type="hidden" name="id" value="<?php echo $curso['id']; ?>">
                                    <input type="hidden" name="titulo" value="<?php echo htmlspecialchars($curso['titulo']); ?>">
                                    <input type="hidden" name="precio" value="<?php echo $curso['precio']; ?>">
                                    <input type="hidden" name="instructor" value="<?php echo htmlspecialchars($curso['instructor'] ?? ''); ?>">
                                    <input type="hidden" name="imagen" value="<?php echo htmlspecialchars($curso['imagen'] ?? ''); ?>">
                                    <input type="hidden" name="tipo" value="curso"> <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">
                                        <i class="bi bi-cart-plus me-2"></i> Agregar al Carrito
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <div class="text-center mt-2">
                            <a href="ver_curso.php?id=<?php echo $curso['id']; ?>" class="text-decoration-none small text-muted">
                                Ver detalles <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
    .transition-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
        transition: all 0.3s ease;
    }
</style>

<?php 
require_once '../../includes/footer_admin.php'; 
?>