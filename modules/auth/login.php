<?php
session_start();
require_once '../../config/bd.php';

$mensaje = "";
if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'registrado') {
    $mensaje = "<div class='alert alert-success'>¡Registro exitoso! Verifica tu cuenta.</div>";
}
if (isset($_GET['error']) && $_GET['error'] == 'expulsado') {
    $mensaje = "<div class='alert alert-warning'>Sesión cerrada.</div>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM usuarios WHERE email = :email AND estado = 1 LIMIT 1";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($password, $usuario['password'])) {
        
        if ($usuario['verificado'] == 0) {
            header("Location: verificar.php?email=" . urlencode($email));
            exit;
        } else {
            // LOGIN EXITOSO
            session_regenerate_id(true);
            $session_id = session_id();

            // Limpiar sesiones anteriores de este usuario para mantener limpia la tabla
            $conexion->prepare("DELETE FROM sesiones_activas WHERE usuario_id = ?")->execute([$usuario['id']]);

            $sqlSesion = "INSERT INTO sesiones_activas (session_id, usuario_id, ip_address, user_agent, ultimo_acceso) 
                          VALUES (:sid, :uid, :ip, :ua, NOW())";
            $conexion->prepare($sqlSesion)->execute([
                ':sid' => $session_id,
                ':uid' => $usuario['id'],
                ':ip' => $_SERVER['REMOTE_ADDR'],
                ':ua' => $_SERVER['HTTP_USER_AGENT']
            ]);

            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre_completo'];
            $_SESSION['rol_id'] = $usuario['rol_id'];
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); 

            if ($usuario['rol_id'] == 1) header("Location: ../admin/dashboard.php");
            elseif ($usuario['rol_id'] == 2) header("Location: ../docente/dashboard.php");
            else header("Location: ../estudiante/dashboard.php");
            exit;
        }

    } else {
        $mensaje = "<div class='alert alert-danger'>Credenciales incorrectas.</div>";
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - EduPlatform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-primary d-flex align-items-center justify-content-center vh-100">
    <div class="card shadow border-0 p-4" style="width: 100%; max-width: 400px;">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-primary">Bienvenido</h3>
            <p class="text-muted">Ingresa a tu cuenta</p>
        </div>
        
        <?php echo $mensaje; ?>
        
        <form method="post">
            <div class="mb-3">
                <label class="form-label fw-bold">Correo</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-key"></i></span>
                    <input type="password" name="password" class="form-control" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-3 fw-bold">Entrar</button>
        </form>
        
        <div class="text-center">
            <a href="recuperar.php" class="small text-decoration-none">Olvidé mi contraseña</a>
            <span class="mx-2 text-muted">|</span>
            <a href="registro.php" class="small text-decoration-none">Crear Cuenta</a>
            <br><br>
            <a href="../../index.php" class="text-secondary small text-decoration-none">
                <i class="bi bi-arrow-left"></i> Volver al inicio
            </a>
        </div>
    </div>
</body>
</html>