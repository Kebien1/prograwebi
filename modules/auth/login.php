<?php
session_start();
require_once '../../config/bd.php';

// Si llega mensaje de registro
$mensaje = "";
if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'registrado') {
    $mensaje = "<div class='alert alert-success'>¡Registro exitoso! Te hemos enviado un código a tu correo.</div>";
}
if (isset($_GET['error']) && $_GET['error'] == 'expulsado') {
    $mensaje = "<div class='alert alert-warning'><strong>Sesión cerrada.</strong> Se ha abierto tu cuenta en otro dispositivo.</div>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 1. Obtener datos del usuario
    $sql = "SELECT u.*, p.limite_sesiones, p.nombre as plan_nombre 
            FROM usuarios u 
            JOIN planes p ON u.plan_id = p.id 
            WHERE u.email = :email AND u.estado = 1 LIMIT 1";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($password, $usuario['password'])) {
        
        // 2. VERIFICACIÓN DE CUENTA (CAMBIO CLAVE AQUÍ)
        if ($usuario['verificado'] == 0) {
            // Si no está verificado, le damos el link para que vaya a poner el código
            $link = "verificar.php?email=" . urlencode($email);
            $mensaje = "<div class='alert alert-warning'>
                            <i class='bi bi-exclamation-triangle'></i> Tu cuenta no está verificada.<br>
                            <a href='$link' class='fw-bold text-dark text-decoration-underline'>Haz clic aquí para ingresar tu código</a>
                        </div>";
        } else {
            // --- CONTROL DE SESIONES (Igual que antes) ---
            
            // A. Contar sesiones activas
            $stmtCount = $conexion->prepare("SELECT COUNT(*) FROM sesiones_activas WHERE usuario_id = ?");
            $stmtCount->execute([$usuario['id']]);
            $sesiones_actuales = $stmtCount->fetchColumn();

            // B. Borrar antigua si supera límite
            if ($sesiones_actuales >= $usuario['limite_sesiones']) {
                $borrar = ($sesiones_actuales - $usuario['limite_sesiones']) + 1;
                $sqlDel = "DELETE FROM sesiones_activas WHERE usuario_id = :uid ORDER BY ultimo_acceso ASC LIMIT $borrar";
                $conexion->prepare($sqlDel)->execute([':uid' => $usuario['id']]);
            }

            // C. Registrar nueva sesión
            session_regenerate_id(true);
            $session_id = session_id();
            $ip = $_SERVER['REMOTE_ADDR'];
            $ua = $_SERVER['HTTP_USER_AGENT'];

            $sqlSesion = "INSERT INTO sesiones_activas (session_id, usuario_id, ip_address, user_agent, ultimo_acceso) 
                          VALUES (:sid, :uid, :ip, :ua, NOW())";
            $conexion->prepare($sqlSesion)->execute([
                ':sid' => $session_id,
                ':uid' => $usuario['id'],
                ':ip' => $ip,
                ':ua' => $ua
            ]);

            // D. Variables de sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre_completo'];
            $_SESSION['rol_id'] = $usuario['rol_id'];
            $_SESSION['plan_nombre'] = $usuario['plan_nombre'];
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Inicializamos seguridad CSRF

            // Redirección
            if ($usuario['rol_id'] == 1) header("Location: ../admin/dashboard.php");
            elseif ($usuario['rol_id'] == 2) header("Location: ../docente/dashboard.php");
            else header("Location: ../estudiante/dashboard.php");
            exit;
        }

    } else {
        $mensaje = "<div class='alert alert-danger'>Correo o contraseña incorrectos.</div>";
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