<?php
include("bd.php");
require_once __DIR__ . '/mail_config.php';

function asegurarTablas(PDO $conexion) {
    $conexion->exec("CREATE TABLE IF NOT EXISTS verificacion_email (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token VARCHAR(255) NOT NULL,
        creado_el DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id),
        CONSTRAINT fk_ev_user FOREIGN KEY (user_id) REFERENCES usuario(ID) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

$mensaje = null;
$error = null;

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $Nick = trim($_POST['Nick'] ?? '');
    $Email = trim($_POST['Email'] ?? '');
    $Password = $_POST['Password'] ?? '';
    $IdRol = 2; 

    if($Nick === '' || $Email === '' || $Password === ''){
        $error = 'Todos los campos son obligatorios.';
    } else if(!filter_var($Email, FILTER_VALIDATE_EMAIL)){
        $error = 'Correo electrónico inválido.';
    } else {
        try {
            asegurarTablas($conexion);
            
            $stmt = $conexion->prepare('SELECT 1 FROM usuario WHERE Nick = :Nick OR Email = :Email LIMIT 1');
            $stmt->execute([':Nick'=>$Nick, ':Email'=>$Email]);
            if($stmt->fetch()){
                $error = 'El usuario o correo ya existe.';
            } else {
                $hash = password_hash($Password, PASSWORD_BCRYPT);
                $estado = 1; 
                $verificado = 0; 
                $stmt = $conexion->prepare('INSERT INTO usuario (Nick, Email, Password, Estado, Verificado, IdRol) VALUES (:Nick,:Email,:Password,:Estado,:Verificado,:IdRol)');
                $stmt->execute([
                    ':Nick'=>$Nick,
                    ':Email'=>$Email,
                    ':Password'=>$hash,
                    ':Estado'=>$estado,
                    ':Verificado'=>$verificado,
                    ':IdRol'=>$IdRol
                ]);
                $userId = (int)$conexion->lastInsertId();

                
                $token = bin2hex(random_bytes(32));
                $stmt = $conexion->prepare('INSERT INTO verificacion_email (user_id, token) VALUES (:uid, :tok)');
                $stmt->execute([':uid'=>$userId, ':tok'=>$token]);

                
                $mail = crearMailer();
                $mail->addAddress($Email, $Nick);
                $mail->isHTML(true);
                $mail->Subject = 'Verifica tu correo - PrograWeb I';
                $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/verificar.php?token=' . urlencode($token);
                $mail->Body = '<p>Hola ' . htmlspecialchars($Nick) . ',</p><p>Por favor verifica tu correo haciendo clic en el siguiente enlace:</p><p><a href="' . $url . '">Verificar cuenta</a></p><p>Si no solicitaste esta cuenta, ignora este mensaje.</p>';
                $mail->send();

                $mensaje = 'Registro exitoso. Te enviamos un correo para verificar tu cuenta.';
            }
        } catch(Exception $e){
            $error = 'Error al registrar: ' . $e->getMessage();
        }
    }
}


?>
<div class="container mt-5">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
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
            <div class="card">
                <div class="card-header">Crear cuenta</div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Usuario</label>
                            <input type="text" name="Nick" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo electrónico</label>
                            <input type="email" name="Email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="Password" class="form-control" required>
                        </div>
                        <button class="btn btn-primary" type="submit">Registrarme</button>
                        <a class="btn btn-link" href="index.php">¿Ya tienes cuenta? Inicia sesión</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
