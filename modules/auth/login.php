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
        // (Borramos tantas como sean necesarias para dejar espacio a la nueva: 1)
        if ($sesionesActuales >= $limite) {
            // Cuántas borrar: (Actuales - Limite) + 1 (la nueva que entra)
            $aBorrar = ($sesionesActuales - $limite) + 1;
            
            for ($i = 0; $i < $aBorrar; $i++) {
                // Borramos la sesión más vieja (la primera del array ordenado por fecha ASC)
                $sid_borrar = $sesionesActivas[$i];
                $conexion->prepare("DELETE FROM sesiones_activas WHERE session_id = ?")->execute([$sid_borrar]);
                
                // Opcional: Si usamos archivos de sesión en disco, intentar forzar borrado
                // session_id($sid_borrar); session_destroy(); 
            }
        }

        // C. Registrar NUEVA sesión
        session_regenerate_id(true); // Seguridad: nuevo ID
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
        $mensaje = "<div class='alert alert-danger'>Credenciales incorrectas.</div>";
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Login Seguro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0f0f1a; color: white; }
        .card { background: #1a1a2e; border: 1px solid #2a2a40; color: white; }
        .form-control { background: #0f0f1a; border: 1px solid #2a2a40; color: white; }
        .form-control:focus { background: #151525; color: white; border-color: #0d6efd; box-shadow: none; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow p-4" style="max-width: 400px; width: 100%;">
        <h3 class="text-center mb-4 fw-bold">Iniciar Sesión</h3>
        <?php echo $mensaje; ?>
        <form method="post">
            <div class="mb-3">
                <label>Correo</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-bold">Entrar</button>
        </form>
        <div class="text-center mt-3">
            <a href="../../index.php" class="text-secondary text-decoration-none small">Volver al inicio</a>
        </div>
    </div>
</body>
</html>