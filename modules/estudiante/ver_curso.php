<?php
session_start();
require_once '../../config/bd.php';
require_once '../../includes/header.php'; 

$id_curso = $_GET['id'] ?? 0;
if (!$id_curso) { echo "<script>window.location='../../index.php';</script>"; exit; }

// 1. Obtener datos del curso
$sql = "SELECT c.*, u.nombre_completo as nombre_docente 
        FROM cursos c 
        JOIN usuarios u ON c.docente_id = u.id 
        WHERE c.id = ?";
$stmt = $conexion->prepare($sql);
$stmt->execute([$id_curso]);
$curso = $stmt->fetch();

if (!$curso) { echo "<div class='container mt-5'>Curso no encontrado.</div>"; exit; }

// 2. NUEVO: Obtener lecciones del curso para el temario
$sqlLecciones = "SELECT * FROM lecciones WHERE curso_id = ? ORDER BY id ASC";
$stmtLecciones = $conexion->prepare($sqlLecciones);
$stmtLecciones->execute([$id_curso]);
$lecciones = $stmtLecciones->fetchAll();

// Verificar si hay alguna clase gratis
$hayGratis = false;
foreach($lecciones as $l) {
    if($l['es_gratis'] == 1) {
        $hayGratis = true; 
        break;
    }
}

// 3. Verificar estado del usuario
$yaComprado = false;
$esEstudiante = false;

if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol_id'] == 3) {
        $esEstudiante = true;
        // Verificamos si ya lo compró o se inscribió
        $check = $conexion->prepare("SELECT id FROM compras WHERE usuario_id = ? AND item_id = ? AND tipo_item = 'curso'");
        $check->execute([$_SESSION['usuario_id'], $id_curso]);
        $yaComprado = $check->rowCount() > 0;
    }
}
?>

<div class="container mt-5">
    <a href="javascript:history.back()" class="text-decoration-none text-muted mb-3 d-inline-block"><i class="bi bi-arrow-left"></i> Volver</a>

    <div class="row g-5">
        <div class="col-lg-8">
            <h1 class="fw-bold display-5 mb-3"><?php echo htmlspecialchars($curso['titulo']); ?></h1>
            
            <div class="d-flex flex-wrap gap-3 mb-4">
                <span class="badge bg-secondary fs-6"><i class="bi bi-clock"></i> <?php echo htmlspecialchars($curso['duracion'] ?? 'N/A'); ?></span>
                <span class="badge bg-info text-dark fs-6"><i class="bi bi-bar-chart"></i> <?php echo htmlspecialchars($curso['nivel'] ?? 'General'); ?></span>
            </div>

            <div class="d-flex align-items-center mb-4">
                <div class="bg-light rounded-circle p-2 me-2 border"><i class="bi bi-person text-secondary"></i></div>
                <div><span class="text-muted small d-block">Docente:</span><span class="fw-bold"><?php echo htmlspecialchars($curso['nombre_docente']); ?></span></div>
                <div class="ms-4 border-start ps-4"><span class="text-muted small d-block">Fecha:</span><span><?php echo date('d/m/Y', strtotime($curso['fecha_creacion'])); ?></span></div>
            </div>
            
            <hr>
            
            <h4 class="fw-bold mb-3">Descripción</h4>
            <p class="text-secondary lh-lg mb-5"><?php echo nl2br(htmlspecialchars($curso['descripcion'])); ?></p>

            <h4 class="fw-bold mb-4">Contenido del Curso</h4>
            <div class="list-group mb-5">
                <?php if(count($lecciones) > 0): ?>
                    <?php foreach($lecciones as $index => $leccion): ?>
                        <?php 
                            // Lógica para mostrar candado o play
                            $esGratis = $leccion['es_gratis'] == 1;
                            $acceso = $yaComprado || $esGratis; 
                            
                            $icono = $acceso ? '<i class="bi bi-play-circle-fill text-primary fs-5"></i>' : '<i class="bi bi-lock-fill text-muted fs-5"></i>';
                            $claseTexto = $acceso ? 'text-dark' : 'text-muted';
                            // Etiqueta de "Gratis" si no ha comprado y es gratis
                            $badge = ($esGratis && !$yaComprado) ? '<span class="badge bg-success ms-2">Gratis</span>' : '';
                        ?>

                        <a href="<?php echo $acceso ? 'aula.php?id='.$curso['id'].'&l='.$index : '#'; ?>" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3 <?php echo !$acceso ? 'disabled bg-light' : ''; ?>">
                            
                            <div class="d-flex align-items-center">
                                <span class="me-3"><?php echo $icono; ?></span>
                                <div>
                                    <span class="fw-bold <?php echo $claseTexto; ?>">
                                        <?php echo $index + 1 . ". " . htmlspecialchars($leccion['titulo']); ?>
                                    </span>
                                    <?php echo $badge; ?>
                                </div>
                            </div>
                            
                            <span class="small text-muted">Video</span>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info">El docente aún no ha subido contenido a este curso.</div>
                <?php endif; ?>
            </div>

        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 100px;">
                <?php 
                    $img = "../../uploads/cursos/" . $curso['imagen_portada'];
                    if(file_exists($img) && !empty($curso['imagen_portada'])): 
                ?>
                    <img src="<?php echo $img; ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                <?php endif; ?>
                
                <div class="card-body p-4 text-center">
                    
                    <?php if($curso['precio'] > 0): ?>
                        <h2 class="fw-bold text-dark my-3">$<?php echo number_format($curso['precio'], 2); ?></h2>
                        <p class="text-muted small">Acceso completo al curso</p>
                    <?php else: ?>
                        <h2 class="fw-bold text-success my-3">Gratis</h2>
                    <?php endif; ?>
                    
                    <div class="d-grid gap-2">
                        <?php if(!isset($_SESSION['usuario_id'])): ?>
                            <a href="../../modules/auth/login.php" class="btn btn-primary btn-lg fw-bold shadow-sm">
                                <i class="bi bi-box-arrow-in-right"></i> Ingresa para Inscribirte
                            </a>
                            <?php if($hayGratis): ?>
                                <a href="../../modules/auth/login.php" class="btn btn-outline-secondary btn-sm mt-2">
                                    <i class="bi bi-play-circle"></i> Inicia sesión para ver la muestra gratis
                                </a>
                            <?php endif; ?>
                            <p class="small text-muted mt-2">¿No tienes cuenta? <a href="../../modules/auth/registro.php">Regístrate gratis</a></p>

                        <?php elseif($esEstudiante): ?>
                            <?php if($yaComprado): ?>
                                <a href="aula.php?id=<?php echo $curso['id']; ?>" class="btn btn-success btn-lg fw-bold">Ir al Aula</a>
                            
                            <?php else: ?>
                                <?php if($curso['precio'] > 0): ?>
                                    <form action="carrito_acciones.php" method="POST">
                                        <input type="hidden" name="accion" value="agregar">
                                        <input type="hidden" name="tipo" value="curso"> 
                                        <input type="hidden" name="id" value="<?php echo $curso['id']; ?>">
                                        <input type="hidden" name="titulo" value="<?php echo htmlspecialchars($curso['titulo']); ?>">
                                        <input type="hidden" name="precio" value="<?php echo $curso['precio']; ?>">
                                        
                                        <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm w-100 mb-2">
                                            <i class="bi bi-cart-plus"></i> Comprar Curso Completo
                                        </button>
                                    </form>

                                    <?php if($hayGratis): ?>
                                        <a href="aula.php?id=<?php echo $curso['id']; ?>" class="btn btn-outline-success fw-bold">
                                            <i class="bi bi-eye"></i> Ver Vista Previa Gratis
                                        </a>
                                        <p class="text-center small text-muted mt-2">Prueba las clases gratuitas antes de comprar.</p>
                                    <?php endif; ?>

                                <?php else: ?>
                                    <a href="procesar_compra.php?tipo=curso&id=<?php echo $curso['id']; ?>" class="btn btn-outline-primary btn-lg fw-bold shadow-sm">
                                        Inscribirse Gratis
                                    </a>
                                <?php endif; ?>

                            <?php endif; ?>

                        <?php else: ?>
                            <div class="alert alert-secondary">Vista previa como Docente/Admin</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php 
require_once '../../includes/footer_admin.php'; 
?>