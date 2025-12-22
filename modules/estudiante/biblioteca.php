<?php
// modules/estudiante/biblioteca.php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
// verificarRol(2); // Descomenta si quieres forzar que solo estudiantes entren
require_once '../../includes/header.php';
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-book"></i> Biblioteca Virtual</h2>
        </div>
    
    <div class="row">
        <?php
        try {
            // CONSULTA PDO
            $stmt = $conexion->query("SELECT * FROM biblioteca ORDER BY id DESC");
            $libros = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($libros) > 0) {
                foreach ($libros as $libro) {
        ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0 hover-shadow">
                        <div class="card-body text-center">
                            <div class="mb-3 text-danger">
                                <i class="bi bi-file-earmark-pdf" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="card-title fw-bold text-dark"><?php echo htmlspecialchars($libro['titulo']); ?></h5>
                            <p class="card-text text-muted small"><?php echo htmlspecialchars($libro['descripcion']); ?></p>
                        </div>
                        <div class="card-footer bg-white border-0 text-center pb-3">
                            <a href="../../uploads/biblioteca/<?php echo $libro['archivo']; ?>" target="_blank" class="btn btn-outline-primary w-75">
                                <i class="bi bi-download"></i> Descargar
                            </a>
                        </div>
                    </div>
                </div>
        <?php 
                }
            } else {
                echo "<div class='col-12'><div class='alert alert-info text-center'>El docente aún no ha subido materiales a la biblioteca.</div></div>";
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        ?>
    </div>
</div>

<style>
/* Efecto suave al pasar el mouse */
.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    transition: all 0.3s ease;
}
</style>

<?php 
require_once '../../includes/footer_admin.php'; 
?>