<?php
session_start();
require_once '../../config/bd.php';

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM usuarios WHERE email = :email AND estado = 1 LIMIT 1";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($password, $usuario['password'])) {
        
        // Verificar si confirmó el correo
        if ($usuario['verificado'] == 0) {
            header("Location: verificar.php?email=" . urlencode($email));
            exit;
        }

        // Login OK - Guardamos sesión simple
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre_completo'];
        $_SESSION['rol_id'] = $usuario['rol_id'];

        // Redirigir
        if ($usuario['rol_id'] == 1) header("Location: ../admin/dashboard.php");
        elseif ($usuario['rol_id'] == 2) header("Location: ../docente/dashboard.php");
        else header("Location: ../estudiante/dashboard.php");
        exit;

    } else {
        $mensaje = "<div class='alert alert-danger'>Datos incorrectos.</div>";
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-primary d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow p-4" style="max-width: 400px; width: 100%;">
        <h3 class="text-center text-primary mb-4">Bienvenido</h3>
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
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
        <div class="text-center mt-3">
            <a href="recuperar.php" class="small">Olvidé mi contraseña</a> | 
            <a href="registro.php" class="small">Crear Cuenta</a>
            <br><a href="../../index.php" class="small text-secondary">Volver al inicio</a>
        </div>
    </div>
</body>
</html>