<?php
// modules/auth/login.php
session_start();
require_once '../../config/bd.php';

// ==========================================
// CONFIGURACIÓN DE TU TABLA (¡VERIFICAR!)
// ==========================================
$tabla_sesiones = "sesiones_activas"; // Nombre de tu tabla en la BD
$col_usuario    = "usuario_id";       // Columna con el ID del usuario
$col_sesion     = "session_id";       // Columna con el ID de sesión
$col_tiempo     = "ultima_actividad"; // Columna de fecha/hora
// ==========================================

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // 1. Verificar usuario y obtener límite de su plan
    $sqlUser = "SELECT u.*, p.limite_sesiones 
                FROM usuarios u 
                LEFT JOIN planes p ON u.plan_id = p.id 
                WHERE u.email = ?";
    $stmt = $conexion->prepare($sqlUser);
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($password, $usuario['password'])) {
        
        // --- INICIO CONTROL DE SESIONES ---
        
        // A. Limpieza: Borrar sesiones "muertas" (más de 24h)
        $conexion->query("DELETE FROM $tabla_sesiones WHERE $col_tiempo < (NOW() - INTERVAL 1 DAY)");

        // B. Obtener límite (si es admin o no tiene plan, por defecto 1)
        $limite = $usuario['limite_sesiones'] ?? 1;

        // C. ¿Cuántas sesiones tiene abiertas este usuario AHORA?
        $sqlCount = "SELECT id FROM $tabla_sesiones WHERE $col_usuario = ? ORDER BY $col_tiempo ASC";
        $stmtCount = $conexion->prepare($sqlCount);
        $stmtCount->execute([$usuario['id']]);
        $sesiones = $stmtCount->fetchAll(PDO::FETCH_ASSOC);

        // D. Si supera el límite, borrar la MÁS ANTIGUA
        if (count($sesiones) >= $limite) {
            $a_borrar = (count($sesiones) - $limite) + 1;
            for ($i = 0; $i < $a_borrar; $i++) {
                // Borramos por ID de fila
                $conexion->prepare("DELETE FROM $tabla_sesiones WHERE id = ?")->execute([$sesiones[$i]['id']]);
            }
        }

        // E. Registrar la NUEVA sesión actual
        session_regenerate_id(true); // Generar nuevo ID limpio
        $session_id_actual = session_id();
        
        $sqlInsert = "INSERT INTO $tabla_sesiones ($col_usuario, $col_sesion, $col_tiempo) VALUES (?, ?, NOW())";
        $conexion->prepare($sqlInsert)->execute([$usuario['id'], $session_id_actual]);

        // --- FIN CONTROL DE SESIONES ---

        // Variables de sesión normales
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['rol_id'] = $usuario['rol_id'];
        $_SESSION['ultimo_acceso'] = time(); // Para la inactividad

        // Redirección
        if ($usuario['rol_id'] == 1) {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../estudiante/dashboard.php");
        }
        exit;
    } else {
        $error = "Correo o contraseña incorrectos.";
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Plataforma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0f111a; color: white; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .card { background: #1f212d; border: 1px solid #2d2f40; }
        .form-control { background: #161821; border: 1px solid #2d2f40; color: #fff; }
        .form-control:focus { background: #161821; color: #fff; border-color: #0d6efd; box-shadow: none; }
    </style>
</head>
<body>
    <div class="card p-4 shadow-lg" style="width: 100%; max-width: 400px;">
        <div class="text-center mb-4">
            <h3 class="fw-bold">Iniciar Sesión</h3>
        </div>
        <?php if(isset($error)): ?>
            <div class="alert alert-danger py-2 small border-0"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if(isset($_GET['mensaje']) && $_GET['mensaje'] == 'inactividad'): ?>
            <div class="alert alert-warning py-2 small border-0">Sesión cerrada por inactividad.</div>
        <?php endif; ?>
        <?php if(isset($_GET['mensaje']) && $_GET['mensaje'] == 'sesion_duplicada'): ?>
            <div class="alert alert-danger py-2 small border-0">Tu cuenta se abrió en otro dispositivo.</div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label small text-muted">Correo Electrónico</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label small text-muted">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-3">Ingresar</button>
            <div class="text-center">
                <a href="recuperar.php" class="text-decoration-none small text-muted">¿Olvidaste tu contraseña?</a>
                <br>
                <a href="registro.php" class="text-decoration-none small text-primary">Crear cuenta nueva</a>
            </div>
        </form>
    </div>
</body>
</html>