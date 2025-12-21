<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
// verificarRol(3); // Puedes descomentar esto si quieres forzar login para ver el catálogo
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
?>

<div class="container mt-4 mb-5">
    <div class="text-center mb-4">
        <h2 class="fw-bold text-dark">Catálogo de Aprendizaje</h2>
        <p class="text-muted">Explora nuestros cursos y recursos disponibles.</p>
    </div>

    <div class="row justify-content-center mb-5">
        <div class="col-md-6">
            <form action="" method="GET" class="d-flex gap-2">
                <input type="text" name="q" class="form-control" placeholder="Buscar..." value="<?php echo htmlspecialchars($busqueda); ?>">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
            </form>
        </div>
    </div>

    <h4 class="fw-bold mb-3 text-primary border-bottom pb-2"><i class="bi bi-camera-video"></i> Cursos Disponibles</h4>
    
    <div class="row row-cols-1 row-cols-md-3 g-4 mb-5">
        <?php foreach($cursos as $c): 
            $precio = isset($c['precio']) ? $c['precio'] : 0;
            $docente = isset($c['docente']) ? $c['docente'] : 'Instructor EduPlatform';
            // Si la consulta original no trae el nombre del docente (JOIN), usar un valor por defecto o hacer el JOIN arriba.
        ?>
            <div class="col">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary">Curso</span>
                            <span class="fw-bold text-success">$<?php echo number_format($precio, 2); ?></span>
                        </div>
                        <h5 class="card-title fw-bold text-truncate"><?php echo htmlspecialchars($c['titulo']); ?></h5>
                        <p class="card-text text-muted small text-truncate"><?php echo htmlspecialchars($c['descripcion']); ?></p>
                    </div>
                    
                    <div class="card-footer bg-white border-0 pb-4 pt-0">
                        <div class="d-grid gap-2">
                            <a href="ver_curso.php?id=<?php echo $c['id']; ?>" class="btn btn-light fw-bold text-primary border btn-sm">Ver Detalles</a>
                            
                            <form action="carrito_acciones.php" method="POST">
                                <input type="hidden" name="action" value="agregar">
                                <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                <input type="hidden" name="titulo" value="<?php echo htmlspecialchars($c['titulo']); ?>">
                                <input type="hidden" name="precio" value="<?php echo $precio; ?>">
                                <input type="hidden" name="instructor" value="<?php echo htmlspecialchars($docente); ?>">
                                <input type="hidden" name="imagen" value="<?php echo htmlspecialchars($c['imagen_portada'] ?? ''); ?>">
                                
                                <button type="submit" class="btn btn-primary w-100 shadow-sm btn-sm">
                                    <i class="bi bi-cart-plus"></i> Agregar al Carrito
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if(!empty($libros)): ?>
    <h4 class="fw-bold mb-3 text-success border-bottom pb-2"><i class="bi bi-book"></i> Libros Digitales</h4>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach($libros as $l): 
             $precioLibro = isset($l['precio']) ? $l['precio'] : 0;
        ?>
            <div class="col">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-success bg-opacity-10 text-success">E-Book</span>
                            <span class="fw-bold text-success">$<?php echo number_format($precioLibro, 2); ?></span>
                        </div>
                        <h5 class="card-title fw-bold text-truncate"><?php echo htmlspecialchars($l['titulo']); ?></h5>
                        <p class="small text-muted mb-0">Autor: <?php echo htmlspecialchars($l['autor']); ?></p>
                    </div>
                    <div class="card-footer bg-white border-0 pb-3">
                        <form action="carrito_acciones.php" method="POST">
                            <input type="hidden" name="action" value="agregar">
                            <input type="hidden" name="id" value="L-<?php echo $l['id']; ?>"> <input type="hidden" name="titulo" value="<?php echo htmlspecialchars($l['titulo']); ?>">
                            <input type="hidden" name="precio" value="<?php echo $precioLibro; ?>">
                            <input type="hidden" name="instructor" value="Autor: <?php echo htmlspecialchars($l['autor']); ?>">
                            <input type="hidden" name="imagen" value=""> <button type="submit" class="btn btn-outline-success w-100 rounded-pill btn-sm">
                                <i class="bi bi-cart-plus"></i> Agregar E-Book
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>
<?php 
require_once '../../includes/footer_admin.php'; 
?>