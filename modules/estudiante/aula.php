<?php
// modules/estudiante/aula.php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
require_once '../../includes/header.php';

if (!isset($_GET['id'])) { header("Location: dashboard.php"); exit; }

$id_curso = $_GET['id'];
$uid = $_SESSION['usuario_id'];
$rol = $_SESSION['rol_id'];

// 1. Datos del Curso
$stmt = $conexion->prepare("SELECT * FROM cursos WHERE id=?");
$stmt->execute([$id_curso]);
$curso = $stmt->fetch();
if(!$curso) die("Curso no existe");

// 2. Verificar Compra
$acceso_total = false;
if ($rol == 1 || $rol == 2) {
    $acceso_total = true; // Admin/Profe ven todo
} else {
    // Buscar si compró el curso
    $chk = $conexion->prepare("SELECT id FROM compras WHERE usuario_id=? AND item_id=? AND tipo_item='curso'");
    $chk->execute([$uid, $id_curso]);
    if ($chk->rowCount() > 0) $acceso_total = true;
}

// 3. Lecciones
$lecciones = $conexion->prepare("SELECT * FROM lecciones WHERE curso_id=? ORDER BY id ASC");
$lecciones->execute([$id_curso]);
$lista = $lecciones->fetchAll();

// 4. Lección actual
$idx = isset($_GET['l']) ? (int)$_GET['l'] : 0;
if ($idx < 0 || $idx >= count($lista)) $idx = 0;
$actual = $lista[$idx] ?? null;

// 5. Permiso para ver el video actual
$puede_ver = false;
if ($actual) {
    if ($acceso_total) { $puede_ver = true; } // Pagó todo
    elseif ($actual['es_gratis'] == 1) { $puede_ver = true; } // Es vista previa
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 bg-white border-end px-0" style="min-height:90vh;">
            <div class="p-3 border-bottom bg-light">
                <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($curso['titulo']); ?></h6>
                <?php if($acceso_total): ?>
                    <span class="badge bg-success mt-2">Curso Comprado</span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark mt-2">Vista Previa</span>
                <?php endif; ?>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach($lista as $i => $l): ?>
                    <?php 
                        $active = ($i === $idx) ? 'active' : '';
                        $bloq = (!$acceso_total && $l['es_gratis'] == 0); // Bloqueado si no pagó Y no es gratis
                        $icono = $bloq ? '<i class="bi bi-lock-fill text-muted"></i>' : '<i class="bi bi-play-circle-fill text-success"></i>';
                    ?>
                    <a href="aula.php?id=<?php echo $id_curso; ?>&l=<?php echo $i; ?>" class="list-group-item list-group-item-action d-flex justify-content-between <?php echo $active; ?>">
                        <span><?php echo $i+1 . ". " . htmlspecialchars($l['titulo']); ?></span>
                        <span><?php echo $icono; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-md-9 bg-light p-0">
            <?php if($actual): ?>
                
                <?php if($puede_ver): ?>
                    <div class="ratio ratio-16x9 bg-black">
                        <iframe src="<?php echo htmlspecialchars($actual['video_url']); ?>" allowfullscreen></iframe>
                    </div>
                    <div class="p-4 bg-white">
                        <h2><?php echo htmlspecialchars($actual['titulo']); ?></h2>
                        <p><?php echo nl2br(htmlspecialchars($actual['descripcion'])); ?></p>
                        
                        <?php if(!$acceso_total): ?>
                            <div class="alert alert-info d-flex justify-content-between align-items-center">
                                <span>¿Te gusta? Compra el curso completo por <strong>$<?php echo $curso['precio']; ?></strong></span>
                                <form action="carrito_acciones.php" method="POST">
                                    <input type="hidden" name="accion" value="agregar">
                                    <input type="hidden" name="tipo" value="curso">
                                    <input type="hidden" name="id" value="<?php echo $curso['id']; ?>">
                                    <input type="hidden" name="titulo" value="<?php echo htmlspecialchars($curso['titulo']); ?>">
                                    <input type="hidden" name="precio" value="<?php echo $curso['precio']; ?>">
                                    <button class="btn btn-primary">Comprar Ahora</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <div class="d-flex align-items-center justify-content-center" style="height:80vh;">
                        <div class="text-center bg-white p-5 shadow rounded">
                            <i class="bi bi-lock-fill text-danger display-1"></i>
                            <h2 class="mt-3">Contenido Bloqueado</h2>
                            <p class="text-muted">Esta lección es exclusiva para estudiantes inscritos.</p>
                            <h3 class="text-primary mb-4">$<?php echo number_format($curso['precio'], 2); ?></h3>
                            
                            <form action="carrito_acciones.php" method="POST">
                                <input type="hidden" name="accion" value="agregar">
                                <input type="hidden" name="tipo" value="curso">
                                <input type="hidden" name="id" value="<?php echo $curso['id']; ?>">
                                <input type="hidden" name="titulo" value="<?php echo htmlspecialchars($curso['titulo']); ?>">
                                <input type="hidden" name="precio" value="<?php echo $curso['precio']; ?>">
                                <button class="btn btn-success btn-lg w-100 shadow">Desbloquear Curso Completo</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="p-5 text-center">No hay clases aún.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer_admin.php'; ?>