<?php
session_start();
require_once '../../config/bd.php';
require_once '../../config/mail_config.php';

if (!isset($_SESSION['temp_registro'])) {
    header("Location: registro.php");
    exit;
}

$datos = $_SESSION['temp_registro'];
$nombre = $datos['nombre'];
$email = $datos['email'];
$plan_id = $datos['plan_id'];
$passHash = password_hash($datos['password'], PASSWORD_BCRYPT);

try {
    $conexion->beginTransaction();

    // 1. Insertar Usuario
    $sqlUser = "INSERT INTO usuarios (nombre_completo, email, password, rol_id, plan_id, estado, verificado, fecha_registro) 
                VALUES (:nom, :email, :pass, 3, :plan, 1, 0, NOW())";
    $stmtInsert = $conexion->prepare($sqlUser);
    $stmtInsert->execute([
        ':nom' => $nombre,
        ':email' => $email,
        ':pass' => $passHash,
        ':plan' => $plan_id
    ]);
    $usuario_id = $conexion->lastInsertId();

    // 2. Generar Código de 6 Dígitos (OTP)
    // str_pad asegura que si sale "50", se guarde como "000050"
    $token = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    
    $sqlToken = "INSERT INTO verificacion_tokens (usuario_id, token, creado_el) VALUES (?, ?, NOW())";
    $conexion->prepare($sqlToken)->execute([$usuario_id, $token]);

    // 3. Enviar Correo con el Número
    $mail = crearMailer();
    if ($mail) {
        $mail->addAddress($email, $nombre);
        $mail->isHTML(true);
        $mail->Subject = 'Tu código de verificación - EduPlatform';
        
        $mail->Body = "
            <div style='font-family: sans-serif; padding: 20px; border: 1px solid #ddd; max-width: 500px; text-align: center;'>
                <h2 style='color: #0d6efd;'>Bienvenido a EduPlatform</h2>
                <p>Hola <strong>$nombre</strong>,</p>
                <p>Para activar tu cuenta, ingresa el siguiente código de verificación:</p>
                <div style='font-size: 32px; font-weight: bold; letter-spacing: 5px; margin: 20px 0; color: #333;'>
                    $token
                </div>
                <p>Este código es válido por 24 horas.</p>
            </div>
        ";
        $mail->send();
    }

    $conexion->commit();
    unset($_SESSION['temp_registro']);
    
    // Redirigir a la pantalla de poner el código (llevamos el email en la URL para facilitar)
    header("Location: verificar.php?email=" . urlencode($email));
    exit;

} catch (Exception $e) {
    $conexion->rollBack();
    die("Error al registrar: " . $e->getMessage());
}
?>