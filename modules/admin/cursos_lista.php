<?php
// modules/admin/cursos_lista.php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(1); // Solo Administrador
require_once '../../includes/header.php';

// Lógica para eliminar un curso
if (isset($_GET['borrar'])) {
    $id_borrar = $_GET['borrar'];
    // Al borrar el curso, se podrían borrar lecciones en cascada si la BD está configurada así,
    // o puedes añadir aquí la lógica para borrar archivos de imagen antes de eliminar el registro.
    $stmtDelete = $conexion->prepare("DELETE FROM cursos WHERE id = ?");
    $stmtDelete->execute([$id_borrar]);
    echo "<script>window.location='cursos_lista.php';</script>";
}

// Consultar TODOS los cursos de la plataforma (Ya no filtramos por docente_id)
$sql = "SELECT c.*, u.nombre_completo as autor 
        FROM cursos c 
        LEFT JOIN usuarios u ON c.docente_id = u.id 
        ORDER BY c.id DESC";
$cursos = $conexion->query($sql)->fetchAll();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">
            <i class="bi bi-collection-play text-primary"></i> Gestión de Cursos
        </h2>
        <a href="cursos_crear.php" class="btn btn-primary rounded-pill">
            <i class="bi bi-plus-lg"></i> Crear Nuevo Curso
        </a>
    </div>

    <?php if(empty($cursos)): ?>
        <div class="alert alert-info text-center py-5 border-0 shadow-sm">
            <i class="bi bi-folder2-open fs-1 d-block mb-3 text-muted"></i>
            <h4 class="fw-bold">No hay cursos registrados</h4>
            <p class="text-muted">Comienza subiendo el primer curso para tus estudiantes.</p>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Curso</th>
                                <th>Nivel / Duración</th>
                                <th>Autor Original</th>
                                <th class="text-end pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($cursos as $c): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <?php if($c['imagen_portada']): ?>
                                            <img src="../../uploads/cursos/<?php echo $c['imagen_portada']; ?>" 
                                                 class="rounded me-3" width="50" height="35" style="object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-secondary rounded me-3 d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 35px;">
                                                <i class="bi bi-image text-white small"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($c['titulo']); ?></div>
                                            <div class="small text-muted">ID: #<?php echo $c['id']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark"><?php echo htmlspecialchars($c['nivel']); ?></span>
                                    <div class="small text-muted mt-1"><i class="bi bi-clock"></i> <?php echo htmlspecialchars($c['duracion']); ?></div>
                                </td>
                                <td>
                                    <div class="small"><?php echo htmlspecialchars($c['autor'] ?? 'Sistema'); ?></div>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group shadow-sm rounded-pill bg-white border">
                                        <a href="cursos_lecciones.php?id=<?php echo $c['id']; ?>" 
                                           class="btn btn-sm btn-light border-0" title="Ver Clases">
                                            <i class="bi bi-camera-video text-primary"></i>
                                        </a>
                                        <a href="cursos_lista.php?borrar=<?php echo $c['id']; ?>" 
                                           class="btn btn-sm btn-light border-0" 
                                           onclick="return confirm('¿Estás seguro de eliminar este curso y todo su contenido?');" title="Eliminar">
                                            <i class="bi bi-trash text-danger"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php 
require_once '../../includes/footer_admin.php'; 
?>