<?php
session_start();
require_once '../../config/bd.php';
require_once '../../config/mail_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: registro.php");
    exit;
}

$nombre = trim($_POST['nombre']);
$email = trim($_POST['email']);
$password = $_POST['password'];

// Validar correo
$stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->rowCount() > 0) {
    echo "<script>alert('El correo ya existe.'); window.location='registro.php';</script>";
    exit;
}

$passHash = password_hash($password, PASSWORD_BCRYPT);

try {
    $conexion->beginTransaction();

    // 1. Insertar Usuario (Estado 1=Activo, Verificado 0=No)
    $sqlUser = "INSERT INTO usuarios (nombre_completo, email, password, rol_id, estado, verificado, fecha_registro) 
                VALUES (?, ?, ?, 3, 1, 0, NOW())";
    $conexion->prepare($sqlUser)->execute([$nombre, $email, $passHash]);
    $usuario_id = $conexion->lastInsertId();

    // 2. Generar Código
    $token = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $sqlToken = "INSERT INTO verificacion_tokens (usuario_id, token, creado_el) VALUES (?, ?, NOW())";
    $conexion->prepare($sqlToken)->execute([$usuario_id, $token]);

    // 3. Enviar Correo
    $mail = crearMailer();
    if ($mail) {
        $mail->addAddress($email, $nombre);
        $mail->isHTML(true);
        $mail->Subject = 'Verifica tu cuenta';
        $mail->Body = "<h3>Hola $nombre</h3><p>Tu código de verificación es: <b>$token</b></p>";
        $mail->send();
    }

    $conexion->commit();
    
    // Mandar a verificar
    header("Location: verificar.php?email=" . urlencode($email));
    exit;

} catch (Exception $e) {
    $conexion->rollBack();
    die("Error: " . $e->getMessage());
}
?>