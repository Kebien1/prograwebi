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

// 1. Obtener Datos del Plan Actual
$stmtPlan = $conexion->prepare("
    SELECT p.nombre, p.limite_sesiones 
    FROM usuarios u 
    JOIN planes p ON u.plan_id = p.id 
    WHERE u.id = ?
");
$stmtPlan->execute([$id_usuario]);
$mi_plan = $stmtPlan->fetch();

// 2. Obtener Mis Cursos (Compras)
$sql = "SELECT c.*, co.fecha_compra 
        FROM compras co 
        JOIN cursos c ON co.item_id = c.id 
        WHERE co.usuario_id = ? AND co.tipo_item = 'curso' 
        ORDER BY co.fecha_compra DESC";
$stmt = $conexion->prepare($sql);
$stmt->execute([$id_usuario]);
$cursos = $stmt->fetchAll();

// Curso destacado (El más reciente o uno aleatorio)
$destacado = count($cursos) > 0 ? $cursos[0] : null;

require_once '../../includes/header.php';
?>

<style>
    /* Forzamos el modo oscuro solo para el contenido principal si el header es claro */
    body { background-color: #141414 !important; color: #fff; }
    
    /* Hero Section (Banner Principal) */
    .hero-banner {
        background: linear-gradient(to right, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.4) 100%), url('<?php echo $destacado ? "../../assets/img/cursos/".$destacado['imagen'] : "https://via.placeholder.com/1200x500?text=Bienvenido"; ?>');
        background-size: cover;
        background-position: center;
        height: 400px;
        display: flex;
        align-items: center;
        border-radius: 0 0 20px 20px;
        margin-bottom: 40px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    }

    /* Tarjetas de Cursos */
    .course-card {
        background-color: #1f1f1f;
        border: none;
        transition: transform 0.3s, box-shadow 0.3s;
        border-radius: 8px;
        overflow: hidden;
    }
    .course-card:hover {
        transform: scale(1.05);
        box-shadow: 0 10px 20px rgba(0,0,0,0.5);
        z-index: 10;
        cursor: pointer;
    }
    .course-card img {
        height: 160px;
        object-fit: cover;
        opacity: 0.8;
        transition: opacity 0.3s;
    }
    .course-card:hover img { opacity: 1; }
    
    .card-body { padding: 15px; }
    .course-title { font-size: 1rem; font-weight: bold; color: white; margin-bottom: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .course-meta { font-size: 0.8rem; color: #aaa; }
    
    /* Botones y Badges */
    .btn-play { background-color: #e50914; color: white; border: none; font-weight: bold; padding: 10px 25px; border-radius: 4px; }
    .btn-play:hover { background-color: #f40612; color: white; }
    .badge-plan { background-color: rgba(255,255,255,0.2); backdrop-filter: blur(5px); border: 1px solid rgba(255,255,255,0.3); }
</style>

<div class="container-fluid p-0 mb-5">
    
    <?php if($destacado): ?>
    <div class="hero-banner px-4 px-md-5">
        <div class="container">
            <div class="col-md-6">
                <span class="badge bg-danger mb-3">Continuar Viendo</span>
                <h1 class="display-4 fw-bold text-white mb-3"><?php echo htmlspecialchars($destacado['titulo']); ?></h1>
                <p class="lead text-light mb-4" style="text-shadow: 1px 1px 2px black;">
                    <?php echo substr(htmlspecialchars($destacado['descripcion']), 0, 120); ?>...
                </p>
                <div class="d-flex gap-3">
                    <a href="aula.php?id=<?php echo $destacado['id']; ?>" class="btn btn-play btn-lg">
                        <i class="bi bi-play-fill"></i> Reproducir
                    </a>
                    <a href="ver_curso.php?id=<?php echo $destacado['id']; ?>" class="btn btn-secondary btn-lg bg-opacity-50 border-0">
                        <i class="bi bi-info-circle"></i> Detalles
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="hero-banner px-4 px-md-5" style="background: linear-gradient(to right, #141414, #2b2b2b);">
        <div class="container text-center">
            <h1 class="fw-bold">Bienvenido a EduPlatform</h1>
            <p class="lead text-muted">Explora nuestro catálogo y empieza a aprender hoy.</p>
            <a href="catalogo.php" class="btn btn-primary btn-lg mt-3">Explorar Catálogo</a>
        </div>
    </div>
    <?php endif; ?>

    <div class="container">
        
        <div class="row mb-5">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center bg-dark p-3 rounded border border-secondary border-opacity-25">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary p-2 rounded-circle text-white">
                            <i class="bi bi-gem"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-white">Tu Plan: <span class="text-primary fw-bold"><?php echo htmlspecialchars($mi_plan['nombre']); ?></span></h6>
                            <small class="text-muted">Dispositivos activos permitidos: <?php echo $mi_plan['limite_sesiones']; ?></small>
                        </div>
                    </div>
                    <a href="suscripcion.php" class="btn btn-outline-light btn-sm rounded-pill px-3">Gestionar Plan</a>
                </div>
            </div>
        </div>

        <h4 class="fw-bold text-white mb-4 ps-2 border-start border-4 border-danger">Mis Cursos</h4>
        
        <?php if(count($cursos) > 0): ?>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                <?php foreach($cursos as $c): ?>
                    <div class="col">
                        <a href="aula.php?id=<?php echo $c['id']; ?>" class="text-decoration-none">
                            <div class="card course-card h-100">
                                <img src="<?php echo file_exists('../../assets/img/cursos/'.$c['imagen']) ? '../../assets/img/cursos/'.$c['imagen'] : 'https://via.placeholder.com/300x160?text=Curso'; ?>" class="card-img-top" alt="Portada">
                                
                                <div class="card-body">
                                    <div class="course-title"><?php echo htmlspecialchars($c['titulo']); ?></div>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <small class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Activo</small>
                                        <div class="btn btn-sm btn-dark rounded-circle"><i class="bi bi-play-fill text-white"></i></div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-film display-1 text-secondary opacity-25"></i>
                <p class="mt-3 text-muted">No tienes cursos activos todavía.</p>
                <a href="catalogo.php" class="btn btn-outline-primary mt-2">Ir al Videoclub</a>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>