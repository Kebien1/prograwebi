<?php
// modules/estudiante/perfil.php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
require_once '../../includes/header.php';

$id_usuario = $_SESSION['usuario_id'];
$mensaje = "";
$tipo_mensaje = "";

// --- LÓGICA 1: ACTUALIZAR DATOS PERSONALES ---
if (isset($_POST['actualizar_datos'])) {
    $nuevo_nombre = trim($_POST['nombre']);
    
    if (!empty($nuevo_nombre)) {
        $sql = "UPDATE usuarios SET nombre = ? WHERE id = ?";
        $conexion->prepare($sql)->execute([$nuevo_nombre, $id_usuario]);
        $_SESSION['usuario_nombre'] = $nuevo_nombre; // Actualizar sesión
        
        $mensaje = "Datos actualizados correctamente.";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "El nombre no puede estar vacío.";
        $tipo_mensaje = "danger";
    }
}

// --- LÓGICA 2: CAMBIAR CONTRASEÑA ---
if (isset($_POST['cambiar_pass'])) {
    $pass_actual = $_POST['pass_actual'];
    $pass_nueva = $_POST['pass_nueva'];
    $pass_confirmar = $_POST['pass_confirmar'];

    // Buscar contraseña actual
    $stmt = $conexion->prepare("SELECT password FROM usuarios WHERE id = ?");
    $stmt->execute([$id_usuario]);
    $user_pass = $stmt->fetchColumn();

    if (password_verify($pass_actual, $user_pass)) {
        if ($pass_nueva === $pass_confirmar) {
            if (strlen($pass_nueva) >= 6) {
                $hash = password_hash($pass_nueva, PASSWORD_DEFAULT);
                $conexion->prepare("UPDATE usuarios SET password = ? WHERE id = ?")->execute([$hash, $id_usuario]);
                $mensaje = "Contraseña cambiada con éxito.";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "La nueva contraseña debe tener al menos 6 caracteres.";
                $tipo_mensaje = "warning";
            }
        } else {
            $mensaje = "las nuevas contraseñas no coinciden.";
            $tipo_mensaje = "danger";
        }
    } else {
        $mensaje = "La contraseña actual es incorrecta.";
        $tipo_mensaje = "danger";
    }
}

// --- OBTENER DATOS DEL USUARIO Y SU PLAN ---
$sqlUser = "SELECT u.*, p.nombre as nombre_plan, p.precio 
            FROM usuarios u 
            LEFT JOIN planes p ON u.plan_id = p.id 
            WHERE u.id = ?";
$stmt = $conexion->prepare($sqlUser);
$stmt->execute([$id_usuario]);
$usuario = $stmt->fetch();

// Generar avatar con iniciales (API externa simple)
$avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($usuario['nombre']) . "&background=0D6EFD&color=fff&size=128";
?>

<div class="container mt-5 mb-5">
    
    <?php if($mensaje): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show shadow-sm" role="alert">
            <?php if($tipo_mensaje == 'success') echo '<i class="bi bi-check-circle-fill me-2"></i>'; ?>
            <?php if($tipo_mensaje == 'danger') echo '<i class="bi bi-exclamation-triangle-fill me-2"></i>'; ?>
            <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row gx-5">
        
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm text-center h-100" style="background: #1f212d; color: white;">
                <div class="card-body py-5">
                    <img src="<?php echo $avatar_url; ?>" class="rounded-circle mb-3 border border-4 border-primary shadow" alt="Avatar">
                    <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($usuario['nombre']); ?></h4>
                    <p class="text-muted small mb-4"><?php echo htmlspecialchars($usuario['email']); ?></p>

                    <div class="d-grid gap-2 mb-4">
                        <div class="p-3 rounded bg-dark border border-secondary border-opacity-25">
                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Plan Actual</small>
                            <h5 class="text-primary mb-0 fw-bold">
                                <i class="bi bi-star-fill text-warning me-1"></i> 
                                <?php echo htmlspecialchars($usuario['nombre_plan'] ?? 'Básico'); ?>
                            </h5>
                        </div>
                    </div>
                    
                    <div class="text-start px-3">
                        <small class="text-muted d-block mb-2"><i class="bi bi-calendar3 me-2"></i> Miembro desde: <span class="text-white"><?php echo isset($usuario['fecha_registro']) ? date('d/m/Y', strtotime($usuario['fecha_registro'])) : 'Reciente'; ?></span></small>
                        <small class="text-muted d-block"><i class="bi bi-geo-alt me-2"></i> Estado: <span class="text-success">Activo</span></small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            
            <div class="card border-0 shadow-sm mb-4" style="background: #ffffff;">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-person-lines-fill text-primary me-2"></i> Información Personal</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold">Correo Electrónico</label>
                            <input type="email" class="form-control bg-light" value="<?php echo htmlspecialchars($usuario['email']); ?>" disabled readonly>
                            <div class="form-text">Por seguridad, el correo no se puede cambiar directamente. Contacta a soporte.</div>
                        </div>
                        <div class="text-end">
                            <button type="submit" name="actualizar_datos" class="btn btn-primary px-4 rounded-pill">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="background: #ffffff;">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-shield-lock-fill text-danger me-2"></i> Seguridad</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-secondary small fw-bold">Contraseña Actual</label>
                                <input type="password" name="pass_actual" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-secondary small fw-bold">Nueva Contraseña</label>
                                <input type="password" name="pass_nueva" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-secondary small fw-bold">Confirmar Nueva</label>
                                <input type="password" name="pass_confirmar" class="form-control" required>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" name="cambiar_pass" class="btn btn-outline-danger px-4 rounded-pill">Actualizar Contraseña</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>