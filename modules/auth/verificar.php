<?php
require_once '../../config/bd.php';

$mensaje = "";
$tipo_mensaje = "";
$email_pre = $_GET['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = $_POST['codigo'];
    $email = $_POST['email'];

    // Verificar código y email
    $sql = "SELECT t.usuario_id 
            FROM verificacion_tokens t 
            JOIN usuarios u ON t.usuario_id = u.id 
            WHERE u.email = :email AND t.token = :token";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':email' => $email, ':token' => $codigo]);
    $row = $stmt->fetch();

    if ($row) {
        // Activar usuario
        $stmtUpd = $conexion->prepare("UPDATE usuarios SET verificado = 1, estado = 1 WHERE id = :uid");
        $stmtUpd->execute([':uid' => $row['usuario_id']]);
        
        // Borrar token usado
        $conexion->prepare("DELETE FROM verificacion_tokens WHERE usuario_id = ?")->execute([$row['usuario_id']]);
        
        // Éxito
        echo "<script>alert('¡Cuenta verificada! Inicia sesión.'); window.location='login.php';</script>";
        exit;
    } else {
        $mensaje = "El código es incorrecto o no coincide con el correo.";
        $tipo_mensaje = "danger";
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificar Cuenta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="card shadow border-0 p-4" style="max-width: 400px; width: 100%;">
        <div class="text-center mb-4">
            <h3>Verificación</h3>
            <p class="text-muted">Revisa tu correo e ingresa el código de 6 dígitos.</p>
        </div>
        
        <?php if($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?>"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label class="form-label fw-bold">Correo Electrónico</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email_pre); ?>" required>
            </div>
            <div class="mb-4">
                <label class="form-label fw-bold">Código de Verificación</label>
                <input type="text" name="codigo" class="form-control text-center fs-4 letter-spacing-2" placeholder="000000" maxlength="6" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">Verificar Cuenta</button>
        </form>
    </div>
</body>
</html>