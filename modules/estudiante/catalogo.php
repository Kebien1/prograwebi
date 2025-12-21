<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(3); 
require_once '../../includes/header.php';

// Buscador
$busqueda = $_GET['q'] ?? ''; 
$sql = "SELECT * FROM cursos";
$params = [];
if (!empty($busqueda)) {
    $sql .= " WHERE titulo LIKE :texto OR descripcion LIKE :texto";
    $params[':texto'] = "%$busqueda%"; 
}
$sql .= " ORDER BY id DESC";
$stmt = $conexion->prepare($sql);
$stmt->execute($params);
$cursos = $stmt->fetchAll();

// Libros
$libros = $conexion->query("SELECT * FROM libros ORDER BY id DESC")->fetchAll();

// Verificar comprados
$mis_compras = $conexion->prepare("SELECT item_id, tipo_item FROM compras WHERE usuario_id = ?");
$mis_compras->execute([$_SESSION['usuario_id']]);
$comprados_raw = $mis_compras->fetchAll();
$comprados = [];
foreach ($comprados_raw as $c) {
    $comprados[$c['tipo_item']][$c['item_id']] = true;
}
?>

<div class="container mt-4">
    <div class="text-center mb-4">
        <h2 class="fw-bold text-dark">Catálogo de Aprendizaje</h2>
        <p class="text-muted">Explora nuestros cursos y recursos.</p>
    </div>

    <div class="row justify-content-center mb-5">
        <div class="col-md-6">
            <form action="" method="GET" class="d-flex gap-2">
                <input type="text" name="q" class="form-control form-control-lg" placeholder="Buscar..." value="<?php echo htmlspecialchars($busqueda); ?>">
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-search"></i></button>
            </form>
        </div>
    </div>

    <h4 class="fw-bold mb-3 text-primary border-bottom pb-2"><i class="bi bi-camera-video"></i> Cursos Disponibles</h4>
    
    <div class="row row-cols-1 row-cols-md-3 g-4 mb-5">
        <?php foreach($cursos as $c): ?>
            <?php $yaTiene = isset($comprados['curso'][$c['id']]); ?>
            <div class="col">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary">Curso</span>
                            <?php if($c['precio'] > 0): ?>
                                <span class="badge bg-dark">$<?php echo number_format($c['precio'], 2); ?></span>
                            <?php else: ?>
                                <span class="badge bg-success">Gratis</span>
                            <?php endif; ?>
                        </div>
                        <h5 class="card-title fw-bold text-truncate"><?php echo htmlspecialchars($c['titulo']); ?></h5>
                        <p class="card-text text-muted small text-truncate"><?php echo htmlspecialchars($c['descripcion']); ?></p>
                    </div>
                    <div class="card-footer bg-white border-0 pb-4 pt-0">
                        <div class="d-flex justify-content-between gap-2">
                            <a href="ver_curso.php?id=<?php echo $c['id']; ?>" class="btn btn-light fw-bold text-primary border flex-grow-1">Ver</a>
                            
                            <?php if($yaTiene): ?>
                                <a href="aula.php?id=<?php echo $c['id']; ?>" class="btn btn-success border-0 flex-grow-1">Ir al Aula</a>
                            <?php else: ?>
                                <?php if($c['precio'] > 0): ?>
                                    <form action="carrito_acciones.php" method="POST" class="d-flex flex-grow-1">
                                        <input type="hidden" name="accion" value="agregar">
                                        <input type="hidden" name="tipo" value="curso">
                                        <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                        <input type="hidden" name="titulo" value="<?php echo htmlspecialchars($c['titulo']); ?>">
                                        <input type="hidden" name="precio" value="<?php echo $c['precio']; ?>">
                                        <button type="submit" class="btn btn-primary w-100 shadow-sm" title="Agregar al carrito">
                                            <i class="bi bi-cart-plus"></i> Comprar
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <a href="procesar_compra.php?tipo=curso&id=<?php echo $c['id']; ?>" class="btn btn-outline-success flex-grow-1 shadow-sm">Inscribirse</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    </div>
<?php require_once '../../includes/footer_admin.php'; ?>