<?php
include 'includes/bd.php';
require_once __DIR__ . '/includes/mail_config.php';

function asegurarTablaResets(PDO $conexion) {
    $conexion->exec("CREATE TABLE IF NOT EXISTS restablecer_contraseña (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token VARCHAR(255) NOT NULL,
        creado_el DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id),
        CONSTRAINT fk_pr_user FOREIGN KEY (user_id) REFERENCES usuario(ID) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

$mensaje = null;
$error = null;

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $Email = trim($_POST['Email'] ?? '');
    if($Email === ''){
        $error = 'Ingrese su correo.';
    } else {
        try {
            asegurarTablaResets($conexion);
            $stmt = $conexion->prepare('SELECT ID, Nick, Email FROM usuario WHERE Email = :Email LIMIT 1');
            $stmt->execute([':Email'=>$Email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            // Mensaje genérico por seguridad
            $mensaje = 'Si el correo existe, recibirás un enlace para restablecer tu contraseña.';
            if($user){
                $token = bin2hex(random_bytes(32));
                $ins = $conexion->prepare('INSERT INTO restablecer_contraseña (user_id, token) VALUES (:uid, :tok)');
                $ins->execute([':uid'=>$user['ID'], ':tok'=>$token]);

                $mail = crearMailer();
                $mail->addAddress($user['Email'], $user['Nick']);
                $mail->isHTML(true);
                $mail->Subject = 'Restablecer contraseña - PrograWeb I';
                $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/restablecer.php?token=' . urlencode($token);
                $mail->Body = '<p>Hola ' . htmlspecialchars($user['Nick']) . ',</p><p>Para restablecer tu contraseña haz clic en el siguiente enlace:</p><p><a href="' . $url . '">Restablecer contraseña</a></p><p>Si no solicitaste este cambio, ignora este mensaje.</p>';
                $mail->send();
            }
        } catch(Exception $e){
            $error = 'Ocurrió un error: ' . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Recuperar Contraseña</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <?php if($mensaje): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header text-center">¿Olvidaste tu contraseña?</div>
                <div class="card-body p-4">
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Correo electrónico</label>
                            <input type="email" name="Email" class="form-control" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" type="submit">Enviar enlace</button>
                        </div>
                        <div class="mt-3 text-center">
                            <a class="btn btn-link" href="index.php">Volver a iniciar sesión</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>