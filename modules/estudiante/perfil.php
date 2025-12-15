<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(3); // Solo estudiantes
require_once '../../includes/header.php';

$uid = $_SESSION['usuario_id'];
$mensaje = "";
$tipo_mensaje = "";

// PROCESAR FORMULARIO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. ACTUALIZAR FOTO
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $permitidos = ['image/jpeg', 'image/png', 'image/jpg'];
        $nombre_archivo = $_FILES['foto']['name'];
        $tipo_archivo = $_FILES['foto']['type'];
        $tmp_nombre = $_FILES['foto']['tmp_name'];
        
        if (in_array($tipo_archivo, $permitidos)) {
            // Crear carpeta si no existe
            $directorio = "../../uploads/perfiles/";
            if (!file_exists($directorio)) {
                mkdir($directorio, 0777, true);
            }
            
            // Nombre único para evitar duplicados
            $nuevo_nombre = "user_" . $uid . "_" . time() . ".jpg";
            $ruta_final = $directorio . $nuevo_nombre;
            
            if (move_uploaded_file($tmp_nombre, $ruta_final)) {
                $sqlFoto = "UPDATE usuarios SET foto_perfil = ? WHERE id = ?";
                $conexion->prepare($sqlFoto)->execute([$nuevo_nombre, $uid]);
                $mensaje = "Foto actualizada con éxito.";
                $tipo_mensaje = "success";
            }
        } else {
            $mensaje = "Formato de imagen no válido (solo JPG/PNG).";
            $tipo_mensaje = "danger";
        }
    }

    // 2. ACTUALIZAR NOMBRE Y PASSWORD
    if (isset($_POST['nombre'])) {
        $nuevo_nombre = trim($_POST['nombre']);
        $pass_actual = $_POST['pass_actual'];
        $pass_nueva = $_POST['pass_nueva'];
        
        if (!empty($nuevo_nombre)) {
            // Verificar contraseña actual para permitir cambios
            $stmt = $conexion->prepare("SELECT password FROM usuarios WHERE id = ?");
            $stmt->execute([$uid]);
            $user = $stmt->fetch();
            
            if (password_verify($pass_actual, $user['password'])) {
                // Si puso nueva contraseña, la actualizamos
                if (!empty($pass_nueva)) {
                    if (strlen($pass_nueva) >= 6) {
                        $hash = password_hash($pass_nueva, PASSWORD_BCRYPT);
                        $sqlUpdate = "UPDATE usuarios SET nombre_completo = ?, password = ? WHERE id = ?";
                        $conexion->prepare($sqlUpdate)->execute([$nuevo_nombre, $hash, $uid]);
                        $mensaje = "Datos y contraseña actualizados.";
                    } else {
                        $mensaje = "La nueva contraseña debe tener al menos 6 caracteres.";
                        $tipo_mensaje = "warning";
                    }
                } else {
                    // Solo actualizamos el nombre
                    $sqlUpdate = "UPDATE usuarios SET nombre_completo = ? WHERE id = ?";
                    $conexion->prepare($sqlUpdate)->execute([$nuevo_nombre, $uid]);
                    $mensaje = "Nombre actualizado correctamente.";
                }
                
                // Actualizar variable de sesión
                $_SESSION['nombre'] = $nuevo_nombre;
                $tipo_mensaje = "success";
                
            } else {
                $mensaje = "La contraseña actual es incorrecta. No se guardaron cambios.";
                $tipo_mensaje = "danger";
            }
        }
    }
}

// OBTENER DATOS ACTUALES
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$uid]);
$u = $stmt->fetch();

// Definir imagen a mostrar
$foto_url = $u['foto_perfil'] 
    ? "../../uploads/perfiles/" . $u['foto_perfil'] 
    : "https://ui-avatars.com/api/?name=" . urlencode($u['nombre_completo']) . "&background=random";
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            <?php if($mensaje): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                    <?php echo $mensaje; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0 overflow-hidden">
                <div class="card-header bg-primary text-white py-4 text-center border-0">
                    <div class="position-relative d-inline-block">
                        <img src="<?php echo htmlspecialchars($foto_url); ?>" 
                             class="rounded-circle border border-4 border-white shadow-sm" 
                             width="120" height="120" style="object-fit: cover;">
                        <label for="inputFoto" class="position-absolute bottom-0 end-0 bg-dark text-white rounded-circle p-2 shadow cursor-pointer" style="cursor: pointer;" title="Cambiar foto">
                            <i class="bi bi-camera-fill"></i>
                        </label>
                    </div>
                    <h3 class="mt-3 fw-bold"><?php echo htmlspecialchars($u['nombre_completo']); ?></h3>
                    <p class="mb-0 opacity-75"><?php echo htmlspecialchars($u['email']); ?></p>
                </div>

                <div class="card-body p-4">
                    <form method="post" enctype="multipart/form-data">
                        <input type="file" name="foto" id="inputFoto" class="d-none" onchange="this.form.submit()">

                        <h5 class="fw-bold mb-3 text-secondary">Editar Información</h5>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($u['nombre_completo']); ?>" required>
                        </div>

                        <hr class="my-4">

                        <h5 class="fw-bold mb-3 text-secondary">Seguridad</h5>
                        <div class="alert alert-light border small text-muted">
                            <i class="bi bi-info-circle"></i> Para guardar cualquier cambio (nombre o nueva contraseña), debes ingresar tu contraseña actual por seguridad.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nueva Contraseña (Opcional)</label>
                                <input type="password" name="pass_nueva" class="form-control" placeholder="Dejar en blanco para no cambiar">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-danger">Contraseña Actual (Requerido)</label>
                                <input type="password" name="pass_actual" class="form-control" required>
                            </div>
                        </div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary btn-lg">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>