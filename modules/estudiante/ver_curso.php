<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(3); 
require_once '../../includes/header.php';

$id_curso = $_GET['id'] ?? 0;
if (!$id_curso) { header("Location: catalogo.php"); exit; }

$sql = "SELECT c.*, u.nombre_completo as nombre_docente FROM cursos c JOIN usuarios u ON c.docente_id = u.id WHERE c.id = ?";
$stmt = $conexion->prepare($sql);
$stmt->execute([$id_curso]);
$curso = $stmt->fetch();

if (!$curso) { echo "<script>window.location='catalogo.php';</script>"; exit; }

$check = $conexion->prepare("SELECT id FROM compras WHERE usuario_id = ? AND item_id = ? AND tipo_item = 'curso'");
$check->execute([$_SESSION['usuario_id'], $id_curso]);
$yaComprado = $check->rowCount() > 0;
?>

<div class="container mt-5">
    <a href="catalogo.php" class="text-decoration-none text-muted mb-3 d-inline-block"><i class="bi bi-arrow-left"></i> Volver</a>

    <div class="row g-5">
        <div class="col-lg-8">
            <h1 class="fw-bold display-5 mb-3"><?php echo htmlspecialchars($curso['titulo']); ?></h1>
            <div class="d-flex align-items-center mb-4">
                <div class="bg-light rounded-circle p-2 me-2 border"><i class="bi bi-person text-secondary"></i></div>
                <div><span class="text-muted small d-block">Docente:</span><span class="fw-bold"><?php echo htmlspecialchars($curso['nombre_docente']); ?></span></div>
                <div class="ms-4 border-start ps-4"><span class="text-muted small d-block">Fecha:</span><span><?php echo date('d/m/Y', strtotime($curso['fecha_creacion'])); ?></span></div>
            </div>
            <hr>
            <h4 class="fw-bold mb-3">Descripción</h4>
            <p class="text-secondary lh-lg"><?php echo nl2br(htmlspecialchars($curso['descripcion'])); ?></p>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 100px;">
                <div class="card-body p-4 text-center">
                    <h2 class="fw-bold text-success my-3">Gratis</h2>
                    <p class="text-muted">Acceso libre y completo.</p>
                    <div class="d-grid gap-2">
                        <?php if($yaComprado): ?>
                            <a href="aula.php?id=<?php echo $curso['id']; ?>" class="btn btn-success btn-lg fw-bold">Ir al Aula</a>
                        <?php else: ?>
                            <a href="procesar_compra.php?tipo=curso&id=<?php echo $curso['id']; ?>" class="btn btn-primary btn-lg fw-bold shadow-sm">Inscribirse Ahora</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>