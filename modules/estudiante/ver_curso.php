<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(3); // Solo estudiantes
require_once '../../includes/header.php';

// 1. Validar que recibimos un ID
$id_curso = $_GET['id'] ?? 0;

if (!$id_curso) {
    header("Location: catalogo.php");
    exit;
}

// 2. Consultar la información del curso y del docente
// Hacemos un JOIN para traer el nombre del profesor en una sola consulta
$sql = "SELECT c.*, u.nombre_completo as nombre_docente 
        FROM cursos c 
        JOIN usuarios u ON c.docente_id = u.id 
        WHERE c.id = ?";
$stmt = $conexion->prepare($sql);
$stmt->execute([$id_curso]);
$curso = $stmt->fetch();

// Si el curso no existe, volver al catálogo
if (!$curso) {
    echo "<script>alert('Curso no encontrado'); window.location='catalogo.php';</script>";
    exit;
}

// 3. Verificar si el estudiante ya compró este curso
$sqlCompra = "SELECT id FROM compras WHERE usuario_id = ? AND item_id = ? AND tipo_item = 'curso'";
$check = $conexion->prepare($sqlCompra);
$check->execute([$_SESSION['usuario_id'], $id_curso]);
$yaComprado = $check->rowCount() > 0;

?>

<div class="container mt-5">
    <a href="catalogo.php" class="text-decoration-none text-muted mb-3 d-inline-block">
        <i class="bi bi-arrow-left"></i> Volver al Catálogo
    </a>

    <div class="row g-5">
        <div class="col-lg-8">
            <h1 class="fw-bold display-5 mb-3"><?php echo htmlspecialchars($curso['titulo']); ?></h1>
            
            <div class="d-flex align-items-center mb-4">
                <div class="bg-light rounded-circle p-2 me-2 border">
                    <i class="bi bi-person text-secondary"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Impartido por:</span>
                    <span class="fw-bold"><?php echo htmlspecialchars($curso['nombre_docente']); ?></span>
                </div>
                <div class="ms-4 border-start ps-4">
                    <span class="text-muted small d-block">Fecha de publicación:</span>
                    <span><?php echo date('d/m/Y', strtotime($curso['fecha_creacion'])); ?></span>
                </div>
            </div>

            <hr>

            <h4 class="fw-bold mb-3">Descripción del Curso</h4>
            <p class="text-secondary lh-lg">
                <?php echo nl2br(htmlspecialchars($curso['descripcion'])); ?>
            </p>

            <div class="mt-5 p-4 bg-light rounded border">
                <h5 class="fw-bold"><i class="bi bi-info-circle text-primary"></i> ¿Qué aprenderás?</h5>
                <ul class="list-unstyled mt-3 mb-0">
                    <li class="mb-2"><i class="bi bi-check-lg text-success me-2"></i> Dominarás los fundamentos del tema.</li>
                    <li class="mb-2"><i class="bi bi-check-lg text-success me-2"></i> Realizarás ejercicios prácticos.</li>
                    <li class="mb-0"><i class="bi bi-check-lg text-success me-2"></i> Acceso de por vida al contenido.</li>
                </ul>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 100px;">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <span class="text-muted text-uppercase small fw-bold">Precio del Curso</span>
                        <h2 class="display-4 fw-bold text-primary my-2">
                            $<?php echo number_format($curso['precio'], 0); ?>
                        </h2>
                    </div>

                    <div class="d-grid gap-2">
                        <?php if($yaComprado): ?>
                            <div class="alert alert-success text-center mb-0 border-0">
                                <i class="bi bi-check-circle-fill"></i> Ya tienes este curso
                            </div>
                            <a href="mis_compras.php" class="btn btn-outline-success fw-bold py-2">
                                Ir a mis clases
                            </a>
                        <?php else: ?>
                            <a href="pasarela.php?tipo=curso&id=<?php echo $curso['id']; ?>&precio=<?php echo $curso['precio']; ?>" 
                               class="btn btn-primary btn-lg fw-bold shadow-sm">
                                Comprar Ahora
                            </a>
                            <p class="text-center text-muted small mt-3 mb-0">
                                <i class="bi bi-shield-lock"></i> Pago 100% Seguro
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>