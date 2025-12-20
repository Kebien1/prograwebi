<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
require_once '../../includes/header.php';

$id_usuario = $_SESSION['usuario_id'];
$mensaje = "";
$tipo_mensaje = "";

// --- LÓGICA: ACTUALIZAR NOMBRE ---
if (isset($_POST['actualizar_datos'])) {
    $nuevo_nombre = trim($_POST['nombre']);
    if (!empty($nuevo_nombre)) {
        $sql = "UPDATE usuarios SET nombre = ? WHERE id = ?";
        $conexion->prepare($sql)->execute([$nuevo_nombre, $id_usuario]);
        $_SESSION['nombre'] = $nuevo_nombre; // Actualizar sesión
        $mensaje = "Nombre actualizado correctamente.";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "El nombre no puede estar vacío.";
        $tipo_mensaje = "danger";
    }
}

// --- LÓGICA: CAMBIAR CONTRASEÑA ---
if (isset($_POST['cambiar_pass'])) {
    $pass_actual = $_POST['pass_actual'];
    $pass_nueva = $_POST['pass_nueva'];
    $pass_confirmar = $_POST['pass_confirmar'];

    $stmt = $conexion->prepare("SELECT password FROM usuarios WHERE id = ?");
    $stmt->execute([$id_usuario]);
    $hash_actual = $stmt->fetchColumn();

    if (password_verify($pass_actual, $hash_actual)) {
        if ($pass_nueva === $pass_confirmar) {
            if (strlen($pass_nueva) >= 6) {
                $nuevo_hash = password_hash($pass_nueva, PASSWORD_DEFAULT);
                $conexion->prepare("UPDATE usuarios SET password = ? WHERE id = ?")->execute([$nuevo_hash, $id_usuario]);
                $mensaje = "Contraseña actualizada con éxito.";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "La nueva contraseña es muy corta (mínimo 6 caracteres).";
                $tipo_mensaje = "warning";
            }
        } else {
            $mensaje = "Las nuevas contraseñas no coinciden.";
            $tipo_mensaje = "danger";
        }
    } else {
        $mensaje = "La contraseña actual es incorrecta.";
        $tipo_mensaje = "danger";
    }
}

// Obtener datos frescos del usuario
$stmtUser = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmtUser->execute([$id_usuario]);
$u = $stmtUser->fetch();
?>

<div class="container py-5">
    
    <div class="mb-4">
        <h2 class="fw-bold text-dark">Mi Perfil</h2>
        <p class="text-muted">Gestiona tu información personal y seguridad.</p>
    </div>

    <?php if($mensaje): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i> <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle" style="width: 80px; height: 80px; font-size: 2rem;">
                            <?php echo strtoupper(substr($u['nombre'], 0, 1)); ?>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($u['nombre']); ?></h5>
                    <p class="text-muted small mb-3"><?php echo htmlspecialchars($u['email']); ?></p>
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">
                        <i class="bi bi-check-circle"></i> Cuenta Activa
                    </span>
                </div>
                <div class="card-footer bg-white border-top-0 p-4">
                    <form method="POST">
                        <label class="form-label small fw-bold text-secondary">NOMBRE COMPLETO</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person"></i></span>
                            <input type="text" name="nombre" class="form-control border-start-0 ps-0 bg-light" value="<?php echo htmlspecialchars($u['nombre']); ?>" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="actualizar_datos" class="btn btn-outline-primary rounded-pill btn-sm fw-bold">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-shield-lock"></i> Seguridad de la Cuenta</h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">CONTRASEÑA ACTUAL</label>
                            <input type="password" name="pass_actual" class="form-control" placeholder="Ingresa tu contraseña actual" required>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label text-muted small fw-bold">NUEVA CONTRASEÑA</label>
                                <input type="password" name="pass_nueva" class="form-control" placeholder="Mínimo 6 caracteres" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">CONFIRMAR NUEVA</label>
                                <input type="password" name="pass_confirmar" class="form-control" placeholder="Repite la nueva contraseña" required>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" name="cambiar_pass" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                Actualizar Contraseña
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>