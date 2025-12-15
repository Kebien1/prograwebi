<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(2); // Solo Docentes (ID 2)
require_once '../../includes/header.php';

$docente_id = $_SESSION['usuario_id'];
$nombreDocente = $_SESSION['nombre'];

// --- CÁLCULO DE ESTADÍSTICAS REALES ---
try {
    // 1. Total de Cursos creados por este docente
    $stmtCursos = $conexion->prepare("SELECT COUNT(*) FROM cursos WHERE docente_id = ?");
    $stmtCursos->execute([$docente_id]);
    $totalCursos = $stmtCursos->fetchColumn();

    // 2. Total de Alumnos (Estudiantes únicos que han comprado sus cursos)
    $sqlAlumnos = "SELECT COUNT(DISTINCT c.usuario_id) 
                   FROM compras c 
                   JOIN cursos cu ON c.item_id = cu.id 
                   WHERE c.tipo_item = 'curso' AND cu.docente_id = ?";
    $stmtAlumnos = $conexion->prepare($sqlAlumnos);
    $stmtAlumnos->execute([$docente_id]);
    $totalAlumnos = $stmtAlumnos->fetchColumn();

    // 3. Ganancias Totales (Suma de las ventas de sus cursos)
    $sqlGanancias = "SELECT SUM(c.monto_pagado) 
                     FROM compras c 
                     JOIN cursos cu ON c.item_id = cu.id 
                     WHERE c.tipo_item = 'curso' AND cu.docente_id = ?";
    $stmtGanancias = $conexion->prepare($sqlGanancias);
    $stmtGanancias->execute([$docente_id]);
    $totalGanancias = $stmtGanancias->fetchColumn() ?: 0;

} catch (Exception $e) {
    $totalCursos = 0; $totalAlumnos = 0; $totalGanancias = 0;
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-0">Panel Académico</h2>
            <p class="text-muted mb-0">Bienvenido, Prof. <?php echo htmlspecialchars($nombreDocente); ?></p>
        </div>
        <a href="crear_curso.php" class="btn btn-success rounded-pill shadow-sm">
            <i class="bi bi-plus-lg"></i> Crear Nuevo Curso
        </a>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted small fw-bold">Mis Cursos</h6>
                            <h2 class="display-5 fw-bold text-primary mb-0"><?php echo $totalCursos; ?></h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                            <i class="bi bi-collection-play fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted small fw-bold">Alumnos Totales</h6>
                            <h2 class="display-5 fw-bold text-info mb-0"><?php echo $totalAlumnos; ?></h2>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded-circle text-info">
                            <i class="bi bi-people fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted small fw-bold">Ingresos Generados</h6>
                            <h2 class="display-5 fw-bold text-success mb-0">$<?php echo number_format($totalGanancias, 0); ?></h2>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
                            <i class="bi bi-currency-dollar fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h4 class="fw-bold mb-3 text-secondary border-bottom pb-2">Gestión de Contenido</h4>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <div class="col">
            <div class="card h-100 border-0 shadow-sm text-center py-4">
                <div class="card-body">
                    <div class="mb-3 text-primary"><i class="bi bi-journal-bookmark-fill display-4"></i></div>
                    <h5 class="fw-bold">Mis Cursos</h5>
                    <p class="text-muted small">Edita y gestiona el contenido de tus clases.</p>
                    <a href="mis_cursos.php" class="btn btn-outline-primary rounded-pill stretched-link">Ver Listado</a>
                </div>
            </div>
        </div>
        
        <div class="col">
            <div class="card h-100 border-0 shadow-sm text-center py-4">
                <div class="card-body">
    <div class="mb-3 text-success"><i class="bi bi-file-earmark-pdf-fill display-4"></i></div>
    <h5 class="fw-bold">Material de Apoyo</h5>
    <p class="text-muted small">Sube libros o guías PDF para tus estudiantes.</p>
    <a href="materiales.php" class="btn btn-outline-success rounded-pill stretched-link">Gestionar Archivos</a>
</div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>