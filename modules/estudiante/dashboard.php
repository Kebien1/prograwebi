<?php
session_start();
require_once '../../config/bd.php';
require_once '../../includes/security.php';

// Verificar Rol Estudiante
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 3) {
    header("Location: ../../index.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];

// 1. Obtener Datos del Plan
$stmtPlan = $conexion->prepare("SELECT p.nombre, p.limite_sesiones FROM usuarios u JOIN planes p ON u.plan_id = p.id WHERE u.id = ?");
$stmtPlan->execute([$id_usuario]);
$mi_plan = $stmtPlan->fetch();

// 2. Obtener Mis Cursos
$sql = "SELECT c.*, co.fecha_compra FROM compras co JOIN cursos c ON co.item_id = c.id WHERE co.usuario_id = ? AND co.tipo_item = 'curso' ORDER BY co.fecha_compra DESC";
$stmt = $conexion->prepare($sql);
$stmt->execute([$id_usuario]);
$cursos = $stmt->fetchAll();

// Curso destacado
$destacado = count($cursos) > 0 ? $cursos[0] : null;

require_once '../../includes/header.php';
?>

<div class="container-fluid p-0 mb-5">
    <?php if($destacado): ?>
        <div class="p-5 text-white bg-dark shadow rounded-bottom" 
             style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('<?php echo $destacado ? "../../assets/img/cursos/".$destacado['imagen_portada'] : "https://via.placeholder.com/1200x500"; ?>'); background-size: cover; background-position: center;">
            <div class="container py-4">
                <span class="badge bg-warning text-dark mb-2">Continuar Aprendiendo</span>
                <h1 class="display-5 fw-bold"><?php echo htmlspecialchars($destacado['titulo']); ?></h1>
                <p class="lead mb-4"><?php echo substr(htmlspecialchars($destacado['descripcion']), 0, 100); ?>...</p>
                <a href="aula.php?id=<?php echo $destacado['id']; ?>" class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm">
                    <i class="bi bi-play-fill"></i> Reproducir
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-primary text-white p-5 text-center rounded-bottom shadow">
            <h1 class="fw-bold">Bienvenido a EduPlatform</h1>
            <p class="lead">Explora nuestro catálogo y empieza a aprender hoy.</p>
            <a href="catalogo.php" class="btn btn-light text-primary fw-bold rounded-pill mt-3 px-4">Explorar Catálogo</a>
        </div>
    <?php endif; ?>

    <div class="container mt-5">
        
        <div class="card border-0 shadow-sm bg-white mb-5">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                        <i class="bi bi-gem fs-4"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">Tu Plan: <span class="text-primary"><?php echo htmlspecialchars($mi_plan['nombre']); ?></span></h5>
                        <small class="text-muted">Dispositivos activos permitidos: <?php echo $mi_plan['limite_sesiones']; ?></small>
                    </div>
                </div>
                <a href="suscripcion.php" class="btn btn-outline-primary rounded-pill px-4">Gestionar Plan</a>
            </div>
        </div>

        <h4 class="fw-bold text-dark mb-4 pb-2 border-bottom">Mis Cursos</h4>
        
        <?php if(count($cursos) > 0): ?>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                <?php foreach($cursos as $c): ?>
                    <div class="col">
                        <a href="aula.php?id=<?php echo $c['id']; ?>" class="text-decoration-none text-dark">
                            <div class="card h-100 border-0 shadow-sm hover-scale">
                                <?php 
                                    $img = "../../uploads/cursos/" . $c['imagen_portada'];
                                    if(empty($c['imagen_portada']) || !file_exists($img)) {
                                        $img = "https://via.placeholder.com/300x160?text=Curso";
                                    }
                                ?>
                                <img src="<?php echo $img; ?>" class="card-img-top" alt="Portada" style="height: 160px; object-fit: cover;">
                                
                                <div class="card-body">
                                    <h6 class="fw-bold mb-2 text-truncate"><?php echo htmlspecialchars($c['titulo']); ?></h6>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">
                                            <i class="bi bi-check-circle-fill"></i> Activo
                                        </span>
                                        <button class="btn btn-sm btn-light rounded-circle shadow-sm text-primary">
                                            <i class="bi bi-play-fill"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5 bg-white rounded shadow-sm">
                <i class="bi bi-journal-x display-1 text-muted opacity-25"></i>
                <h5 class="mt-3 text-muted">No tienes cursos activos todavía.</h5>
                <a href="catalogo.php" class="btn btn-primary rounded-pill mt-3 px-4">Ir al Catálogo</a>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>