<?php
session_start();
require_once '../../config/bd.php';
require_once '../../config/mail_config.php';

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    $stmt = $conexion->prepare("SELECT id, nombre_completo FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user) {
        // Generar Código Numérico 6 dígitos
        $token = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $conexion->prepare("DELETE FROM recuperacion_tokens WHERE usuario_id = ?")->execute([$user['id']]);
        
        $sql = "INSERT INTO recuperacion_tokens (usuario_id, token, expira_el) VALUES (:uid, :tok, :exp)";
        $conexion->prepare($sql)->execute([':uid' => $user['id'], ':tok' => $token, ':exp' => $expira]);

        // Enviar Correo
        $mail = crearMailer();
        if ($mail) {
            $mail->addAddress($email, $user['nombre_completo']);
            $mail->isHTML(true);
            $mail->Subject = 'Código para restablecer contraseña';
            
            $mail->Body = "
                <div style='font-family: sans-serif; padding: 20px; text-align: center; border: 1px solid #eee;'>
                    <h3>Recuperación de Cuenta</h3>
                    <p>Usa el siguiente código para cambiar tu contraseña:</p>
                    <div style='font-size: 32px; font-weight: bold; color: #0d6efd; margin: 20px 0;'>$token</div>
                    <p style='color:#777;'>Si no fuiste tú, ignora este mensaje.</p>
                </div>
            ";
            $mail->send();
            
            // Redirigir al formulario de restablecer
            header("Location: restablecer.php?email=" . urlencode($email));
            exit;
        }
    } else {
        $mensaje = "<div class='alert alert-info'>Si el correo existe, enviamos el código.</div>";
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recuperar Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="card shadow border-0 p-4" style="max-width: 400px; width: 100%;">
        <div class="text-center mb-4">
            <h4 class="fw-bold">Recuperar Contraseña</h4>
            <p class="text-muted small">Te enviaremos un código a tu correo.</p>
        </div>
        <?php echo $mensaje; ?>
        <form method="post">
            <div class="mb-3">
                <label class="form-label fw-bold">Correo Electrónico</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Enviar Código</button>
        </form>
        <div class="text-center mt-3"><a href="login.php">Volver</a></div>
    </div>
</body>
</html>