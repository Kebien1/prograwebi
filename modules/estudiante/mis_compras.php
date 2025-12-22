<?php
// modules/estudiante/mis_compras.php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(3); // Solo estudiantes
require_once '../../includes/header.php';

$usuario_id = $_SESSION['usuario_id'];

// Obtener todas las compras del estudiante con información completa
try {
    $sql = "SELECT 
                co.id as compra_id,
                co.item_id,
                co.tipo_item,
                co.monto_pagado,
                co.fecha_compra,
                co.factura_id,
                c.titulo as curso_titulo,
                c.descripcion as curso_descripcion,
                c.imagen_portada as curso_imagen,
                c.duracion as curso_duracion,
                c.nivel as curso_nivel,
                u.nombre_completo as instructor,
                f.codigo_factura,
                f.total as factura_total
            FROM compras co
            LEFT JOIN cursos c ON co.item_id = c.id AND co.tipo_item = 'curso'
            LEFT JOIN usuarios u ON c.docente_id = u.id
            LEFT JOIN facturas f ON co.factura_id = f.id
            WHERE co.usuario_id = :usuario_id
            ORDER BY co.fecha_compra DESC";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    $compras = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $compras = [];
    $error_mensaje = "Error al cargar tus compras: " . $e->getMessage();
}
?>

<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark">
                <i class="bi bi-bag-check-fill text-success"></i> Mis Compras
            </h2>
            <p class="text-muted">Historial completo de tus adquisiciones</p>
        </div>
        <a href="catalogo.php" class="btn btn-outline-primary rounded-pill">
            <i class="bi bi-plus-circle"></i> Comprar Más Cursos
        </a>
    </div>

    <?php if(isset($error_mensaje)): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i> <?php echo $error_mensaje; ?>
        </div>
    <?php endif; ?>

    <?php if(count($compras) > 0): ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach($compras as $compra): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border-0 hover-scale">
                        <?php 
                            // Determinar la imagen
                            $ruta_imagen = "../../uploads/cursos/" . $compra['curso_imagen'];
                            if(empty($compra['curso_imagen']) || !file_exists($ruta_imagen)) {
                                $ruta_imagen = "https://via.placeholder.com/400x225?text=Curso+Comprado";
                            }
                        ?>
                        
                        <div class="position-relative">
                            <img src="<?php echo htmlspecialchars($ruta_imagen); ?>" 
                                 class="card-img-top" 
                                 style="height: 200px; object-fit: cover;" 
                                 alt="Portada del curso">
                            
                            <span class="position-absolute top-0 end-0 m-2 badge rounded-pill bg-success shadow">
                                <i class="bi bi-check-circle-fill"></i> Comprado
                            </span>
                        </div>

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold text-dark mb-3">
                                <?php echo htmlspecialchars($compra['curso_titulo'] ?? 'Curso Digital'); ?>
                            </h5>
                            
                            <p class="card-text text-muted small flex-grow-1">
                                <?php 
                                    $desc = $compra['curso_descripcion'] ?? 'Contenido educativo digital';
                                    echo htmlspecialchars(substr($desc, 0, 100)) . '...'; 
                                ?>
                            </p>

                            <?php if($compra['tipo_item'] == 'curso'): ?>
                                <div class="mb-3">
                                    <span class="badge bg-primary bg-opacity-10 text-primary me-2">
                                        <i class="bi bi-bar-chart"></i> <?php echo htmlspecialchars($compra['curso_nivel'] ?? 'General'); ?>
                                    </span>
                                    <span class="badge bg-info bg-opacity-10 text-info">
                                        <i class="bi bi-clock"></i> <?php echo htmlspecialchars($compra['curso_duracion'] ?? 'Consultar'); ?>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <div class="border-top pt-3 mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar-check"></i> 
                                        <?php echo date('d/m/Y', strtotime($compra['fecha_compra'])); ?>
                                    </small>
                                    <span class="fw-bold text-success">
                                        $<?php echo number_format($compra['monto_pagado'], 2); ?>
                                    </span>
                                </div>

                                <?php if($compra['instructor']): ?>
                                    <small class="text-muted d-block mb-2">
                                        <i class="bi bi-person"></i> <?php echo htmlspecialchars($compra['instructor']); ?>
                                    </small>
                                <?php endif; ?>

                                <div class="d-grid gap-2">
                                    <?php if($compra['tipo_item'] == 'curso'): ?>
                                        <a href="aula.php?id=<?php echo $compra['item_id']; ?>" 
                                           class="btn btn-primary rounded-pill fw-bold">
                                            <i class="bi bi-play-circle-fill"></i> Ir al Curso
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if($compra['factura_id']): ?>
                                        <a href="ver_factura.php?id=<?php echo $compra['factura_id']; ?>" 
                                           class="btn btn-outline-secondary btn-sm rounded-pill" 
                                           target="_blank">
                                            <i class="bi bi-receipt"></i> Ver Recibo
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Resumen de Compras -->
        <div class="card border-0 shadow-sm mt-5">
            <div class="card-body">
                <h5 class="fw-bold mb-3">
                    <i class="bi bi-graph-up"></i> Resumen de Compras
                </h5>
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded">
                            <h3 class="fw-bold text-primary"><?php echo count($compras); ?></h3>
                            <small class="text-muted">Total de Compras</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded">
                            <h3 class="fw-bold text-success">
                                $<?php 
                                    $total_gastado = array_sum(array_column($compras, 'monto_pagado'));
                                    echo number_format($total_gastado, 2); 
                                ?>
                            </h3>
                            <small class="text-muted">Total Invertido</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded">
                            <h3 class="fw-bold text-info">
                                <?php 
                                    $cursos = array_filter($compras, function($c) { 
                                        return $c['tipo_item'] == 'curso'; 
                                    });
                                    echo count($cursos); 
                                ?>
                            </h3>
                            <small class="text-muted">Cursos Activos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-cart-x display-1 text-muted opacity-25"></i>
            </div>
            <h4 class="fw-bold text-dark mb-3">Aún no tienes compras</h4>
            <p class="text-muted mb-4">
                Explora nuestro catálogo y comienza tu viaje de aprendizaje hoy mismo.
            </p>
            <a href="catalogo.php" class="btn btn-primary btn-lg rounded-pill px-5">
                <i class="bi bi-search"></i> Explorar Catálogo
            </a>
        </div>
    <?php endif; ?>
</div>

<style>
.hover-scale {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.hover-scale:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}
</style>

<?php require_once '../../includes/footer_admin.php'; ?>