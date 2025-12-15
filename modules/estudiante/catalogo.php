<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(3); 
require_once '../../includes/header.php';

// --- LÓGICA DEL BUSCADOR ---
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

// --- LÓGICA DE LIBROS ---
$libros = $conexion->query("SELECT * FROM libros ORDER BY id DESC")->fetchAll();

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
        <p class="text-muted">Encuentra el curso perfecto para ti.</p>
    </div>

    <div class="row justify-content-center mb-5">
        <div class="col-md-6">
            <form action="" method="GET" class="d-flex gap-2">
                <input type="text" name="q" class="form-control form-control-lg" 
                       placeholder="Buscar cursos..." 
                       value="<?php echo htmlspecialchars($busqueda); ?>">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-search"></i>
                </button>
                <?php if(!empty($busqueda)): ?>
                    <a href="catalogo.php" class="btn btn-outline-secondary" title="Ver todos">
                        <i class="bi bi-x-lg"></i>
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <h4 class="fw-bold mb-3 text-primary border-bottom pb-2">
        <i class="bi bi-camera-video"></i> Cursos Disponibles
    </h4>
    
    <?php if(empty($cursos)): ?>
        <div class="alert alert-warning text-center border-0 shadow-sm py-4">
            <i class="bi bi-search display-4 d-block mb-3 opacity-50"></i>
            No encontramos cursos que coincidan con "<strong><?php echo htmlspecialchars($busqueda); ?></strong>".
            <div class="mt-3">
                <a href="catalogo.php" class="btn btn-outline-dark btn-sm">Ver todo el catálogo</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-3 g-4 mb-5">
            <?php foreach($cursos as $c): ?>
                <?php $yaTiene = isset($comprados['curso'][$c['id']]); ?>
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm transition-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-primary bg-opacity-10 text-primary">Curso</span>
                                <h4 class="fw-bold mb-0 text-primary">$<?php echo number_format($c['precio'], 0); ?></h4>
                            </div>
                            <h5 class="card-title fw-bold text-truncate" title="<?php echo htmlspecialchars($c['titulo']); ?>">
                                <?php echo htmlspecialchars($c['titulo']); ?>
                            </h5>
                            
                            <p class="card-text text-muted small text-truncate">
                                <?php echo htmlspecialchars($c['descripcion']); ?>
                            </p>
                        </div>
                        <div class="card-footer bg-white border-0 pb-4 pt-0">
                            <div class="d-grid gap-2">
                                <a href="ver_curso.php?id=<?php echo $c['id']; ?>" class="btn btn-light fw-bold text-primary border">
                                    Ver Detalles
                                </a>
                                
                                <?php if($yaTiene): ?>
                                    <button class="btn btn-success disabled border-0">
                                        <i class="bi bi-check-circle"></i> Comprado
                                    </button>
                                <?php else: ?>
                                    <a href="pasarela.php?tipo=curso&id=<?php echo $c['id']; ?>&precio=<?php echo $c['precio']; ?>" 
                                       class="btn btn-primary shadow-sm">
                                        Comprar Ahora
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <h4 class="fw-bold mb-3 text-success border-bottom pb-2">
        <i class="bi bi-file-earmark-pdf"></i> Libros Digitales
    </h4>
    <?php if(empty($libros)): ?>
        <div class="alert alert-info border-0 shadow-sm">No hay libros disponibles.</div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach($libros as $l): ?>
                <?php $yaTiene = isset($comprados['libro'][$l['id']]); ?>
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-success bg-opacity-10 text-success">E-Book</span>
                                <h4 class="fw-bold mb-0">$<?php echo number_format($l['precio'], 0); ?></h4>
                            </div>
                            <h5 class="card-title fw-bold text-truncate" title="<?php echo htmlspecialchars($l['titulo']); ?>">
                                <?php echo htmlspecialchars($l['titulo']); ?>
                            </h5>
                            <p class="small text-muted mb-0">Autor: <?php echo htmlspecialchars($l['autor']); ?></p>
                        </div>
                        <div class="card-footer bg-white border-0 pb-3">
                            <?php if($yaTiene): ?>
                                <button class="btn btn-secondary w-100 rounded-pill" disabled>
                                    <i class="bi bi-check-circle"></i> En tu Biblioteca
                                </button>
                            <?php else: ?>
                                <a href="pasarela.php?tipo=libro&id=<?php echo $l['id']; ?>&precio=<?php echo $l['precio']; ?>" 
                                    class="btn btn-outline-success w-100 rounded-pill">
                                    Adquirir PDF
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php require_once '../../includes/footer.php'; ?>