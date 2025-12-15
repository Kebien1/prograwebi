<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(3); 
require_once '../../includes/header.php';

// Obtener ID del estudiante
$uid = $_SESSION['usuario_id'];

// 1. Consultar Cursos Comprados
$sqlCursos = "SELECT c.id, c.titulo, c.descripcion, comp.fecha_compra 
              FROM compras comp 
              JOIN cursos c ON comp.item_id = c.id 
              WHERE comp.usuario_id = ? AND comp.tipo_item = 'curso'
              ORDER BY comp.fecha_compra DESC";
$listaCursos = $conexion->prepare($sqlCursos);
$listaCursos->execute([$uid]);
$misCursos = $listaCursos->fetchAll();

// 2. Consultar Libros Comprados
$sqlLibros = "SELECT l.id, l.titulo, l.archivo_pdf, comp.fecha_compra 
              FROM compras comp 
              JOIN libros l ON comp.item_id = l.id 
              WHERE comp.usuario_id = ? AND comp.tipo_item = 'libro'
              ORDER BY comp.fecha_compra DESC";
$listaLibros = $conexion->prepare($sqlLibros);
$listaLibros->execute([$uid]);
$misLibros = $listaLibros->fetchAll();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">Mis Adquisiciones</h2>
        <a href="catalogo.php" class="btn btn-outline-dark rounded-pill">
            <i class="bi bi-cart-plus"></i> Ir al Catálogo
        </a>
    </div>

    <?php if(isset($_GET['exito'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> ¡Compra exitosa! Ya tienes acceso a tu nuevo contenido.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="fw-bold text-primary mb-0"><i class="bi bi-laptop"></i> Mis Cursos Activos</h5>
                </div>
                <div class="card-body p-0">
                    <?php if(empty($misCursos)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox display-4 opacity-25"></i>
                            <p class="mt-2 small">No tienes cursos todavía.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach($misCursos as $c): ?>
                                <div class="list-group-item p-3 border-0 border-bottom">
                                    <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($c['titulo']); ?></h6>
                                    <div class="d-flex justify-content-between align-items-end mt-2">
                                        <small class="text-muted">
                                            <i class="bi bi-calendar3"></i> <?php echo date('d/m/Y', strtotime($c['fecha_compra'])); ?>
                                        </small>
                                        
                                        <a href="aula.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                                            <i class="bi bi-play-fill"></i> Ir al Aula
                                        </a>

                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="fw-bold text-success mb-0"><i class="bi bi-book"></i> Mi Biblioteca Digital</h5>
                </div>
                <div class="card-body p-0">
                    <?php if(empty($misLibros)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-journals display-4 opacity-25"></i>
                            <p class="mt-2 small">Tu biblioteca está vacía.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach($misLibros as $l): ?>
                                <div class="list-group-item p-3 border-0 border-bottom">
                                    <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($l['titulo']); ?></h6>
                                    <div class="d-flex justify-content-between align-items-end mt-2">
                                        <small class="text-muted">
                                            <i class="bi bi-calendar3"></i> <?php echo date('d/m/Y', strtotime($l['fecha_compra'])); ?>
                                        </small>
                                        <a href="../../uploads/libros/<?php echo $l['archivo_pdf']; ?>" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                            <i class="bi bi-download"></i> Descargar
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>