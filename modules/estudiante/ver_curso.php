<?php
session_start();
require_once '../../config/bd.php';
require_once '../../includes/header.php'; 

$id_curso = $_GET['id'] ?? 0;
if (!$id_curso) { 
    echo "<script>alert('Curso no válido'); window.location='catalogo.php';</script>"; 
    exit; 
}

// 1. Obtener datos del curso
$sql = "SELECT c.*, u.nombre_completo as nombre_docente 
        FROM cursos c 
        JOIN usuarios u ON c.docente_id = u.id 
        WHERE c.id = ?";
$stmt = $conexion->prepare($sql);
$stmt->execute([$id_curso]);
$curso = $stmt->fetch();

if (!$curso) { 
    echo "<div class='container mt-5'><div class='alert alert-danger'>Curso no encontrado.</div></div>"; 
    exit; 
}

// 2. Obtener el Temario (Lecciones)
$sqlLecciones = "SELECT * FROM lecciones WHERE curso_id = ? ORDER BY id ASC";
$stmtLecciones = $conexion->prepare($sqlLecciones);
$stmtLecciones->execute([$id_curso]);
$lecciones = $stmtLecciones->fetchAll();

// Verificar si hay clases gratis para el botón de "Vista Previa"
$hayGratis = false;
foreach($lecciones as $l) {
    if($l['es_gratis'] == 1) {
        $hayGratis = true; 
        break;
    }
}

// 3. Verificar si el usuario ya compró el curso
$yaComprado = false;
$esEstudiante = false;
$usuario_id = $_SESSION['usuario_id'] ?? 0;

if ($usuario_id > 0) {
    $rol_id = $_SESSION['rol_id'] ?? 0;
    
    if ($rol_id == 3) {
        $esEstudiante = true;
        $check = $conexion->prepare("SELECT id FROM compras WHERE usuario_id = ? AND item_id = ? AND tipo_item = 'curso'");
        $check->execute([$usuario_id, $id_curso]);
        $yaComprado = $check->rowCount() > 0;
    } elseif ($rol_id == 1 || $rol_id == 2) {
        // Admin o Docente - acceso total
        $yaComprado = true;
    }
}

// Determinar si el curso es gratis
$esGratis = ($curso['precio'] == 0 || empty($curso['precio']));
?>

<div class="container mt-5">
    <a href="javascript:history.back()" class="text-decoration-none text-muted mb-3 d-inline-block">
        <i class="bi bi-arrow-left"></i> Volver
    </a>

    <div class="row g-5">
        <div class="col-lg-8">
            <h1 class="fw-bold display-5 mb-3"><?php echo htmlspecialchars($curso['titulo']); ?></h1>
            
            <div class="d-flex flex-wrap gap-3 mb-4">
                <span class="badge bg-secondary fs-6">
                    <i class="bi bi-clock"></i> <?php echo htmlspecialchars($curso['duracion'] ?? 'N/A'); ?>
                </span>
                <span class="badge bg-info text-dark fs-6">
                    <i class="bi bi-bar-chart"></i> <?php echo htmlspecialchars($curso['nivel'] ?? 'General'); ?>
                </span>
                <?php if($esGratis): ?>
                    <span class="badge bg-success fs-6">
                        <i class="bi bi-gift"></i> GRATIS
                    </span>
                <?php endif; ?>
            </div>

            <div class="d-flex align-items-center mb-4">
                <div class="bg-light rounded-circle p-2 me-2 border">
                    <i class="bi bi-person text-secondary"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Instructor:</span>
                    <span class="fw-bold"><?php echo htmlspecialchars($curso['nombre_docente']); ?></span>
                </div>
                <div class="ms-4 border-start ps-4">
                    <span class="text-muted small d-block">Fecha:</span>
                    <span><?php echo date('d/m/Y', strtotime($curso['fecha_creacion'])); ?></span>
                </div>
            </div>
            
            <hr>
            
            <h4 class="fw-bold mb-3">Descripción</h4>
            <p class="text-secondary lh-lg mb-5">
                <?php echo nl2br(htmlspecialchars($curso['descripcion'])); ?>
            </p>

            <?php if(count($lecciones) > 0): ?>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h4 class="fw-bold mb-0">Contenido del Curso</h4>
                    <small class="text-muted"><?php echo count($lecciones); ?> lecciones</small>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach($lecciones as $index => $leccion): ?>
                        <?php 
                            // Lógica de acceso
                            $esLeccionGratis = $leccion['es_gratis'] == 1;
                            $acceso = $yaComprado || $esLeccionGratis; 
                        ?>

                        <?php if($acceso): ?>
                            <a href="aula.php?id=<?php echo $curso['id']; ?>&l=<?php echo $index; ?>" 
                               class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-play-circle-fill text-primary fs-4 me-3"></i>
                                    <div>
                                        <span class="fw-bold text-dark">
                                            <?php echo $index + 1 . ". " . htmlspecialchars($leccion['titulo']); ?>
                                        </span>
                                        <?php if($esLeccionGratis && !$yaComprado): ?>
                                            <span class="badge bg-success ms-2">Vista Previa Gratis</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span class="small text-muted">Ver clase <i class="bi bi-chevron-right"></i></span>
                            </a>

                        <?php else: ?>
                            <div class="list-group-item p-3 d-flex justify-content-between align-items-center bg-light text-muted">
                                <div class="d-flex align-items-center" style="opacity: 0.7;">
                                    <i class="bi bi-lock-fill text-secondary fs-4 me-3"></i>
                                    <div>
                                        <span class="fw-normal">
                                            <?php echo $index + 1 . ". " . htmlspecialchars($leccion['titulo']); ?>
                                        </span>
                                    </div>
                                </div>
                                <i class="bi bi-lock text-muted"></i>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- SIDEBAR DE COMPRA -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 100px; z-index: 10;">
                <?php 
                    $img = "../../uploads/cursos/" . $curso['imagen_portada'];
                    if(!empty($curso['imagen_portada']) && file_exists($img)): 
                ?>
                    <img src="<?php echo $img; ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                <?php endif; ?>
                
                <div class="card-body p-4 text-center">
                    
                    <?php if($esGratis): ?>
                        <h2 class="fw-bold text-success my-3">
                            <i class="bi bi-gift"></i> GRATIS
                        </h2>
                    <?php else: ?>
                        <h2 class="fw-bold text-dark my-3">
                            $<?php echo number_format($curso['precio'], 2); ?>
                        </h2>
                    <?php endif; ?>
                    
                    <div class="d-grid gap-2 mt-4">
                        <?php if(!$usuario_id): ?>
                            <!-- Usuario no logueado -->
                            <a href="../auth/login.php" class="btn btn-primary btn-lg fw-bold shadow-sm">
                                <i class="bi bi-box-arrow-in-right"></i> Ingresa para <?php echo $esGratis ? 'Inscribirte' : 'Comprar'; ?>
                            </a>
                            <?php if($hayGratis): ?>
                                <a href="../auth/login.php" class="btn btn-outline-secondary btn-sm mt-2">
                                    <i class="bi bi-play-circle"></i> Ver clases de prueba
                                </a>
                            <?php endif; ?>

                        <?php elseif($esEstudiante): ?>
                            <!-- Estudiante logueado -->
                            <?php if($yaComprado): ?>
                                <a href="aula.php?id=<?php echo $curso['id']; ?>" class="btn btn-success btn-lg fw-bold">
                                    <i class="bi bi-play-circle-fill"></i> Continuar Curso
                                </a>
                            <?php else: ?>
                                <?php if($esGratis): ?>
                                    <!-- Curso Gratis - Inscripción directa -->
                                    <form action="procesar_compra.php" method="POST">
                                        <input type="hidden" name="tipo" value="curso">
                                        <input type="hidden" name="id" value="<?php echo $curso['id']; ?>">
                                        <button type="submit" class="btn btn-success btn-lg fw-bold shadow-sm w-100">
                                            <i class="bi bi-check-circle"></i> Inscribirse Gratis
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <!-- Curso de Pago -->
                                    <form action="carrito_acciones.php" method="POST">
                                        <input type="hidden" name="action" value="agregar">
                                        <input type="hidden" name="tipo" value="curso">
                                        <input type="hidden" name="id" value="<?php echo $curso['id']; ?>">
                                        <input type="hidden" name="titulo" value="<?php echo htmlspecialchars($curso['titulo']); ?>">
                                        <input type="hidden" name="precio" value="<?php echo $curso['precio']; ?>">
                                        <input type="hidden" name="instructor" value="<?php echo htmlspecialchars($curso['nombre_docente']); ?>">
                                        <input type="hidden" name="imagen" value="<?php echo htmlspecialchars($curso['imagen_portada'] ?? ''); ?>">
                                        
                                        <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm w-100 mb-2">
                                            <i class="bi bi-cart-plus"></i> Agregar al Carrito
                                        </button>
                                    </form>

                                    <!-- Botón de compra directa -->
                                    <a href="pasarela_pago.php?tipo=curso&id=<?php echo $curso['id']; ?>&precio=<?php echo $curso['precio']; ?>&nombre=<?php echo urlencode($curso['titulo']); ?>" 
                                       class="btn btn-outline-primary fw-bold w-100">
                                        <i class="bi bi-lightning-charge"></i> Comprar Ahora
                                    </a>

                                    <?php if($hayGratis): ?>
                                        <a href="aula.php?id=<?php echo $curso['id']; ?>" class="btn btn-link text-decoration-none mt-2">
                                            <i class="bi bi-eye"></i> Ver Clases Gratis
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>

                        <?php else: ?>
                            <!-- Admin o Docente -->
                            <div class="alert alert-info">
                                <i class="bi bi-shield-check"></i> Tienes acceso completo como administrador
                            </div>
                            <a href="aula.php?id=<?php echo $curso['id']; ?>" class="btn btn-primary btn-lg fw-bold">
                                Ver Curso
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if(!$yaComprado && $esEstudiante): ?>
                    <hr class="my-4">
                    <div class="text-start">
                        <h6 class="fw-bold mb-3">Este curso incluye:</h6>
                        <ul class="list-unstyled small">
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Acceso de por vida</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Certificado de finalización</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Acceso en móvil y TV</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Material descargable</li>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
require_once '../../includes/footer_admin.php'; 
?>