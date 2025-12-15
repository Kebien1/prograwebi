<?php
session_start();
require_once '../../config/bd.php';

$mensaje = "";
$email_pre = $_GET['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $codigo = $_POST['codigo'];
    $pass1 = $_POST['pass1'];
    $pass2 = $_POST['pass2'];

    if ($pass1 !== $pass2) {
        $mensaje = "<div class='alert alert-warning'>Las contraseñas no coinciden.</div>";
    } else {
        // Verificar código
        $sql = "SELECT t.usuario_id 
                FROM recuperacion_tokens t 
                JOIN usuarios u ON t.usuario_id = u.id 
                WHERE u.email = :email AND t.token = :token AND t.expira_el > NOW()";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':email' => $email, ':token' => $codigo]);
        $row = $stmt->fetch();

        if ($row) {
            // Cambiar contraseña
            $hash = password_hash($pass1, PASSWORD_BCRYPT);
            $conexion->prepare("UPDATE usuarios SET password = :p WHERE id = :uid")
                     ->execute([':p' => $hash, ':uid' => $row['usuario_id']]);
            
            // Borrar token
            $conexion->prepare("DELETE FROM recuperacion_tokens WHERE usuario_id = ?")->execute([$row['usuario_id']]);

            echo "<script>alert('¡Contraseña cambiada! Inicia sesión.'); window.location='login.php';</script>";
            exit;
        } else {
            $mensaje = "<div class='alert alert-danger'>Código inválido o expirado.</div>";
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nueva Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="card shadow p-4" style="max-width: 400px; width: 100%;">
        <div class="text-center mb-3">
            <h4 class="fw-bold">Establecer Nueva Clave</h4>
        </div>
        <?php echo $mensaje; ?>
        
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Correo</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email_pre); ?>" readonly style="background-color: #e9ecef;">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold text-primary">Código Recibido (6 dígitos)</label>
                <input type="text" name="codigo" class="form-control text-center fw-bold fs-5" placeholder="000000" maxlength="6" required>
            </div>
            <hr>
            <div class="mb-3">
                <label class="form-label">Nueva Contraseña</label>
                <input type="password" name="pass1" class="form-control" required minlength="6">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirmar Contraseña</label>
                <input type="password" name="pass2" class="form-control" required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary w-100">Guardar Cambios</button>
        </form>
    </div>
</body>
</html>