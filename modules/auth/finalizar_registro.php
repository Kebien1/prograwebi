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
    echo "<script>alert('El correo $email ya está registrado.'); window.location='registro.php';</script>";
    exit;
}

$passHash = password_hash($password, PASSWORD_BCRYPT);

try {
    $conexion->beginTransaction();

    // Insertar Usuario (rol_id 3 = estudiante)
    $sqlUser = "INSERT INTO usuarios (nombre_completo, email, password, rol_id, estado, verificado, fecha_registro) 
                VALUES (:nom, :email, :pass, 3, 1, 0, NOW())";
    $stmtInsert = $conexion->prepare($sqlUser);
    $stmtInsert->execute([
        ':nom' => $nombre,
        ':email' => $email,
        ':pass' => $passHash
    ]);
    $usuario_id = $conexion->lastInsertId();

    // Token
    $token = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $sqlToken = "INSERT INTO verificacion_tokens (usuario_id, token, creado_el) VALUES (?, ?, NOW())";
    $conexion->prepare($sqlToken)->execute([$usuario_id, $token]);

    // Enviar Correo
    $mail = crearMailer();
    if ($mail) {
        $mail->addAddress($email, $nombre);
        $mail->isHTML(true);
        $mail->Subject = 'Verifica tu cuenta - EduPlatform';
        $mail->Body = "<h3>Bienvenido a EduPlatform</h3><p>Tu código de verificación es: <b>$token</b></p>";
        $mail->send();
    }

    $conexion->commit();
    header("Location: verificar.php?email=" . urlencode($email));
    exit;

} catch (Exception $e) {
    $conexion->rollBack();
    die("Error al registrar: " . $e->getMessage());
}
?>