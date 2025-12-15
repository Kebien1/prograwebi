<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(2); // Solo Docente
require_once '../../includes/header.php';

// Consultar cursos creados por ESTE docente
$sql = "SELECT * FROM cursos WHERE docente_id = ? ORDER BY id DESC";
$stmt = $conexion->prepare($sql);
$stmt->execute([$_SESSION['usuario_id']]);
$misCursos = $stmt->fetchAll();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark"><i class="bi bi-collection-play text-success"></i> Mis Cursos Impartidos</h2>
        <a href="crear_curso.php" class="btn btn-success rounded-pill">
            <i class="bi bi-plus-lg"></i> Publicar Nuevo
        </a>
    </div>

    <?php if(empty($misCursos)): ?>
        <div class="alert alert-info text-center py-5 border-0 shadow-sm">
            <i class="bi bi-inbox fs-1 d-block mb-3 text-muted"></i>
            <h4 class="fw-bold">Aún no tienes cursos publicados</h4>
            <p class="text-muted">¡Comparte tu conocimiento! Crea tu primer curso ahora.</p>
            <a href="crear_curso.php" class="btn btn-primary mt-3">Crear Curso</a>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach($misCursos as $c): ?>
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-white border-0 pt-3">
                        <span class="badge bg-success bg-opacity-10 text-success float-end fs-6">
                            $<?php echo number_format($c['precio'], 0); ?>
                        </span>
                        <h5 class="card-title fw-bold mb-0 text-dark"><?php echo htmlspecialchars($c['titulo']); ?></h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text text-muted small text-truncate">
                            <?php echo htmlspecialchars($c['descripcion']); ?>
                        </p>
                        <hr class="text-muted opacity-25">
                        <div class="d-flex justify-content-between align-items-center small text-muted">
                            <span><i class="bi bi-calendar3"></i> <?php echo date('d/m/Y', strtotime($c['fecha_creacion'] ?? 'now')); ?></span>
                            <span><i class="bi bi-person-video3"></i> Autor</span>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-white border-0 pb-4 pt-0">
                        <div class="d-grid gap-2">
                            <a href="gestionar_lecciones.php?id=<?php echo $c['id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill">
                                <i class="bi bi-collection-play"></i> Gestionar Lecciones
                            </a>
                        </div>
                    </div>
                    
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>