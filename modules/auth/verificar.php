<?php
require_once '../../config/bd.php';

$mensaje = "";
$email_pre = $_GET['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = $_POST['codigo'];
    $email = $_POST['email'];

    // Buscar el token
    $sql = "SELECT t.usuario_id FROM verificacion_tokens t 
            JOIN usuarios u ON t.usuario_id = u.id 
            WHERE u.email = ? AND t.token = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$email, $codigo]);
    $row = $stmt->fetch();

    if ($row) {
        // Marcar como verificado
        $conexion->prepare("UPDATE usuarios SET verificado = 1 WHERE id = ?")->execute([$row['usuario_id']]);
        // Borrar el token usado
        $conexion->prepare("DELETE FROM verificacion_tokens WHERE usuario_id = ?")->execute([$row['usuario_id']]);
        
        echo "<script>alert('Cuenta verificada.'); window.location='login.php';</script>";
        exit;
    } else {
        $mensaje = "Código incorrecto.";
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Verificar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow p-4" style="max-width: 400px;">
        <h4 class="text-center mb-3">Verificación</h4>
        <?php if($mensaje): ?><div class="alert alert-danger"><?php echo $mensaje; ?></div><?php endif; ?>
        
        <form method="post">
            <div class="mb-3">
                <label>Correo</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email_pre); ?>" required>
            </div>
            <div class="mb-3">
                <label>Código recibido</label>
                <input type="text" name="codigo" class="form-control text-center" maxlength="6" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Verificar</button>
        </form>
    </div>
</body>
</html>