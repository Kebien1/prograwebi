<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
require_once '../../includes/header.php';

// Validar que se reciba un ID de curso
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$id_curso = $_GET['id'];
$usuario_id = $_SESSION['usuario_id'];
$rol_id = $_SESSION['rol_id'];

// 1. Obtener información del curso
$stmtCurso = $conexion->prepare("SELECT * FROM cursos WHERE id = ?");
$stmtCurso->execute([$id_curso]);
$curso = $stmtCurso->fetch();

if (!$curso) {
    echo "<div class='container mt-5'>Curso no encontrado.</div>";
    require_once '../../includes/footer_admin.php';
    exit;
}

// 2. Obtener lecciones
$stmtLecciones = $conexion->prepare("SELECT * FROM lecciones WHERE curso_id = ? ORDER BY orden ASC, id ASC");
$stmtLecciones->execute([$id_curso]);
$lecciones = $stmtLecciones->fetchAll();

// 3. Determinar qué lección ver
$leccion_actual_index = isset($_GET['l']) ? (int)$_GET['l'] : 0;

// Validación básica de índice
if (empty($lecciones)) {
    echo "<div class='container mt-5 alert alert-warning'>Este curso aún no tiene contenido.</div>";
    require_once '../../includes/footer_admin.php';
    exit;
}
if ($leccion_actual_index < 0 || $leccion_actual_index >= count($lecciones)) {
    $leccion_actual_index = 0;
}

$clase_actual = $lecciones[$leccion_actual_index];

// =================================================================================
// LÓGICA DE CONTROL DE ACCESO (EL CANDADO 🔒)
// =================================================================================
$acceso_permitido = false;

// CASO 1: Admin o Docente siempre tienen acceso
if ($rol_id == 1 || $rol_id == 2) {
    $acceso_permitido = true;
} 
// CASO 2: La lección es GRATIS
elseif ($clase_actual['es_de_pago'] == 0) {
    $acceso_permitido = true;
} 
// CASO 3: Verificar compras (Si es estudiante y la lección es de pago)
else {
    // A. ¿Compró el CURSO completo?
    $checkCurso = $conexion->prepare("SELECT id FROM compras WHERE usuario_id = ? AND item_id = ? AND tipo_item = 'curso'");
    $checkCurso->execute([$usuario_id, $id_curso]);
    
    if ($checkCurso->rowCount() > 0) {
        $acceso_permitido = true;
    } else {
        // B. ¿Compró ESTA lección individualmente?
        $checkLeccion = $conexion->prepare("SELECT id FROM compras WHERE usuario_id = ? AND item_id = ? AND tipo_item = 'leccion'");
        $checkLeccion->execute([$usuario_id, $clase_actual['id']]);
        
        if ($checkLeccion->rowCount() > 0) {
            $acceso_permitido = true;
        }
    }
}
// =================================================================================
?>

<div class="container-fluid">
    <div class="row">
        
        <div class="col-md-3 bg-white border-end" style="min-height: 90vh;">
            <div class="p-3 border-bottom">
                <h5 class="fw-bold text-primary mb-0"><?php echo htmlspecialchars($curso['titulo']); ?></h5>
                <small class="text-muted">Progreso del curso</small>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach($lecciones as $index => $lec): ?>
                    <?php 
                        $activo = ($index === $leccion_actual_index) ? 'active' : ''; 
                        // Icono según si es gratis o de pago
                        $icono_estado = ($lec['es_de_pago'] == 1) ? '<i class="bi bi-currency-dollar text-warning"></i>' : '<i class="bi bi-unlock text-success"></i>';
                    ?>
                    <a href="aula.php?id=<?php echo $id_curso; ?>&l=<?php echo $index; ?>" 
                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo $activo; ?>">
                        <div class="small">
                            <span class="fw-bold text-muted me-2"><?php echo $index + 1; ?>.</span>
                            <?php echo htmlspecialchars($lec['titulo']); ?>
                        </div>
                        <span class="small"><?php echo $icono_estado; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-md-9 bg-light p-4">
            
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    
                    <?php if ($acceso_permitido): ?>
                        <div class="ratio ratio-16x9 bg-black">
                            <iframe src="<?php echo htmlspecialchars($clase_actual['video_url']); ?>" allowfullscreen></iframe>
                        </div>
                        <div class="p-4">
                            <h2 class="fw-bold"><?php echo htmlspecialchars($clase_actual['titulo']); ?></h2>
                            <p class="text-muted mt-3"><?php echo nl2br(htmlspecialchars($clase_actual['descripcion'])); ?></p>
                        </div>

                    <?php else: ?>
                        <div class="text-center py-5 bg-white">
                            <div class="py-5">
                                <i class="bi bi-lock-fill text-warning display-1 mb-3"></i>
                                <h2 class="fw-bold text-dark">Contenido Premium</h2>
                                <p class="text-muted fs-5 mb-4" style="max-width: 600px; margin: 0 auto;">
                                    Esta clase es exclusiva. Para verla, necesitas adquirirla individualmente o comprar el curso completo.
                                </p>
                                
                                <div class="d-flex justify-content-center gap-3 align-items-center flex-wrap">
                                    <form action="carrito_acciones.php" method="POST">
                                        <input type="hidden" name="accion" value="agregar">
                                        <input type="hidden" name="tipo" value="leccion">
                                        <input type="hidden" name="id" value="<?php echo $clase_actual['id']; ?>">
                                        <input type="hidden" name="titulo" value="Clase: <?php echo htmlspecialchars($clase_actual['titulo']); ?>">
                                        <input type="hidden" name="precio" value="<?php echo $clase_actual['precio']; ?>">
                                        
                                        <button type="submit" class="btn btn-success btn-lg rounded-pill px-5 shadow fw-bold">
                                            <i class="bi bi-cart-plus"></i> Comprar Clase ($<?php echo number_format($clase_actual['precio'], 2); ?>)
                                        </button>
                                    </form>

                                    <form action="carrito_acciones.php" method="POST">
                                        <input type="hidden" name="accion" value="agregar">
                                        <input type="hidden" name="tipo" value="curso">
                                        <input type="hidden" name="id" value="<?php echo $curso['id']; ?>">
                                        <input type="hidden" name="titulo" value="Curso: <?php echo htmlspecialchars($curso['titulo']); ?>">
                                        <input type="hidden" name="precio" value="50.00"> <button type="submit" class="btn btn-outline-primary btn-lg rounded-pill px-4 fw-bold">
                                            Comprar Curso Completo
                                        </button>
                                    </form>
                                </div>
                                <div class="mt-4">
                                    <small class="text-muted">¿Ya lo compraste? <a href="mis_compras.php">Revisa tus compras</a></small>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
            
        </div>
    </div>
</div>

<?php require_once '../../includes/footer_admin.php'; ?>