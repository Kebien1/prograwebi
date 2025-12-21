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

// Verificar lo que ya tiene
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
        <p class="text-muted">Todos nuestros recursos son de acceso libre.</p>
    </div>

    <div class="row justify-content-center mb-5">
        <div class="col-md-6">
            <form action="" method="GET" class="d-flex gap-2">
                <input type="text" name="q" class="form-control form-control-lg" 
                       placeholder="Buscar cursos..." 
                       value="<?php echo htmlspecialchars($busqueda); ?>">
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
                            <span class="badge bg-success">Gratis</span>
                        </div>
                        <h5 class="card-title fw-bold text-truncate"><?php echo htmlspecialchars($c['titulo']); ?></h5>
                        <p class="card-text text-muted small text-truncate"><?php echo htmlspecialchars($c['descripcion']); ?></p>
                    </div>
                    <div class="card-footer bg-white border-0 pb-4 pt-0">
                        <div class="d-grid gap-2">
                            <a href="ver_curso.php?id=<?php echo $c['id']; ?>" class="btn btn-light fw-bold text-primary border">Ver Detalles</a>
                            
                            <?php if($yaTiene): ?>
                                <a href="aula.php?id=<?php echo $c['id']; ?>" class="btn btn-success border-0">
                                    <i class="bi bi-play-circle"></i> Ir al Aula
                                </a>
                            <?php else: ?>
                                <a href="procesar_compra.php?tipo=curso&id=<?php echo $c['id']; ?>" class="btn btn-primary shadow-sm">
                                    Inscribirse Gratis
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <h4 class="fw-bold mb-3 text-success border-bottom pb-2"><i class="bi bi-file-earmark-pdf"></i> Libros Digitales</h4>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach($libros as $l): ?>
            <?php $yaTiene = isset($comprados['libro'][$l['id']]); ?>
            <div class="col">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-success bg-opacity-10 text-success">E-Book</span>
                            <span class="badge bg-success">Gratis</span>
                        </div>
                        <h5 class="card-title fw-bold text-truncate"><?php echo htmlspecialchars($l['titulo']); ?></h5>
                        <p class="small text-muted mb-0">Autor: <?php echo htmlspecialchars($l['autor']); ?></p>
                    </div>
                    <div class="card-footer bg-white border-0 pb-3">
                        <?php if($yaTiene): ?>
                            <a href="ver_archivo.php?id=<?php echo $l['id']; ?>" target="_blank" class="btn btn-secondary w-100 rounded-pill">
                                <i class="bi bi-book"></i> Leer Ahora
                            </a>
                        <?php else: ?>
                            <a href="procesar_compra.php?tipo=libro&id=<?php echo $l['id']; ?>" class="btn btn-outline-success w-100 rounded-pill">
                                Obtener PDF
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php 
require_once '../../includes/footer_admin.php'; 
?>