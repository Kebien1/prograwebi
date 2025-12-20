<?php
session_start();
require_once '../../config/bd.php';

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 1. Obtener usuario y su límite de sesiones según el plan
    $sql = "SELECT u.*, p.limite_sesiones 
            FROM usuarios u 
            JOIN planes p ON u.plan_id = p.id 
            WHERE u.email = :email AND u.estado = 1 LIMIT 1";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($password, $usuario['password'])) {
        if ($usuario['verificado'] == 0) {
            header("Location: verificar.php?email=" . urlencode($email));
            exit;
        }

        // --- CONTROL DE SESIONES INTELIGENTE ---
        
        // A. Obtener sesiones actuales
        $stmtSesiones = $conexion->prepare("SELECT session_id FROM sesiones_activas WHERE usuario_id = ? ORDER BY ultimo_acceso ASC");
        $stmtSesiones->execute([$usuario['id']]);
        $sesionesActivas = $stmtSesiones->fetchAll(PDO::FETCH_COLUMN);
        
        $limite = $usuario['limite_sesiones'];
        $sesionesActuales = count($sesionesActivas);

        // B. Si alcanzamos o superamos el límite, borrar las más antiguas
        if ($sesionesActuales >= $limite) {
            $aBorrar = ($sesionesActuales - $limite) + 1;
            for ($i = 0; $i < $aBorrar; $i++) {
                $sid_borrar = $sesionesActivas[$i];
                $conexion->prepare("DELETE FROM sesiones_activas WHERE session_id = ?")->execute([$sid_borrar]);
            }
        }

        // C. Registrar NUEVA sesión
        session_regenerate_id(true); 
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre_completo'];
        $_SESSION['rol_id'] = $usuario['rol_id'];
        $_SESSION['plan_id'] = $usuario['plan_id'];

        $new_sid = session_id();
        $ip = $_SERVER['REMOTE_ADDR'];
        $ua = $_SERVER['HTTP_USER_AGENT'];

        $sqlInsertSession = "INSERT INTO sesiones_activas (session_id, usuario_id, ip_address, user_agent, ultimo_acceso) VALUES (?, ?, ?, ?, NOW())";
        $conexion->prepare($sqlInsertSession)->execute([$new_sid, $usuario['id'], $ip, $ua]);

        // --- REDIRECCIÓN ---
        if ($usuario['rol_id'] == 1) {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../estudiante/dashboard.php");
        }
        exit;
    } else {
        $mensaje = "<div class='alert alert-danger border-0 bg-danger bg-opacity-25 text-danger'><small><i class='bi bi-exclamation-circle'></i> Credenciales incorrectas.</small></div>";
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Iniciar Sesión | EduPlatform</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { 
            background: #0f0f1a; 
            color: #e0e0e0; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card { 
            background: #1a1a2e; 
            border: 1px solid #2a2a40; 
            color: white; 
            border-radius: 12px;
        }
        .form-control { 
            background: #0f0f1a; 
            border: 1px solid #2a2a40; 
            color: white; 
            padding: 12px;
        }
        .form-control:focus { 
            background: #151525; 
            color: white; 
            border-color: #0d6efd; 
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15); 
        }
        .btn-primary {
            padding: 10px;
            font-weight: 600;
        }
        .link-custom {
            color: #6c757d;
            transition: 0.3s;
        }
        .link-custom:hover {
            color: #0d6efd;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 p-3">
    
    <div class="card shadow-lg p-4 w-100" style="max-width: 400px;">
        <div class="text-center mb-4">
            <h1 class="h3 fw-bold mb-1 text-white">¡Bienvenido!</h1>
            <p class="text-muted small">Ingresa a tu cuenta para continuar</p>
        </div>

        <?php echo $mensaje; ?>

        <form method="post">
            <div class="mb-3">
                <label class="form-label text-secondary small fw-bold">CORREO ELECTRÓNICO</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary border-opacity-25 text-secondary"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="nombre@correo.com" required>
                </div>
            </div>
            
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label text-secondary small fw-bold mb-0">CONTRASEÑA</label>
                    <a href="recuperar.php" class="text-decoration-none small text-info">¿Olvidaste tu clave?</a>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary border-opacity-25 text-secondary"><i class="bi bi-key"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <div class="d-grid mb-4">
                <button type="submit" class="btn btn-primary rounded-pill">Iniciar Sesión</button>
            </div>
        </form>

        <div class="text-center pt-3 border-top border-secondary border-opacity-25">
            <p class="mb-2 text-muted small">¿Aún no tienes una cuenta?</p>
            <a href="registro.php" class="btn btn-outline-light w-100 btn-sm rounded-pill fw-bold">
                Crear cuenta gratis
            </a>
            <div class="mt-3">
                <a href="../../index.php" class="link-custom text-decoration-none small">
                    <i class="bi bi-arrow-left"></i> Volver al inicio
                </a>
            </div>
        </div>
    </div>

</body>
</html>