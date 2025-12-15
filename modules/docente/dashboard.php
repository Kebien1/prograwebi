<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(2);
require_once '../../includes/header.php';

$docente_id = $_SESSION['usuario_id'];
$stmtCursos = $conexion->prepare("SELECT COUNT(*) FROM cursos WHERE docente_id = ?");
$stmtCursos->execute([$docente_id]);
$totalCursos = $stmtCursos->fetchColumn();

$sqlAlumnos = "SELECT COUNT(DISTINCT c.usuario_id) FROM compras c JOIN cursos cu ON c.item_id = cu.id WHERE c.tipo_item = 'curso' AND cu.docente_id = ?";
$stmtAlumnos = $conexion->prepare($sqlAlumnos);
$stmtAlumnos->execute([$docente_id]);
$totalAlumnos = $stmtAlumnos->fetchColumn();
?>

<div class="container mt-4">
    <h2 class="fw-bold text-dark mb-4">Panel Académico</h2>

    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><h6 class="text-uppercase text-muted small fw-bold">Mis Cursos</h6><h2 class="display-5 fw-bold text-primary mb-0"><?php echo $totalCursos; ?></h2></div>
                    <i class="bi bi-collection-play fs-1 text-primary opacity-25"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-info">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><h6 class="text-uppercase text-muted small fw-bold">Alumnos Totales</h6><h2 class="display-5 fw-bold text-info mb-0"><?php echo $totalAlumnos; ?></h2></div>
                    <i class="bi bi-people fs-1 text-info opacity-25"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <a href="mis_cursos.php" class="btn btn-primary w-100 py-3 fw-bold">Gestionar Cursos</a>
        </div>
        <div class="col-md-6">
            <a href="materiales.php" class="btn btn-success w-100 py-3 fw-bold">Subir Materiales</a>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>