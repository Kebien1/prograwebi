<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ajusta estas rutas si es necesario según donde tengas la carpeta phpmailer
require_once dirname(__DIR__) . '/phpmailer/PHPMailer.php';
require_once dirname(__DIR__) . '/phpmailer/SMTP.php';
require_once dirname(__DIR__) . '/phpmailer/Exception.php';

function crearMailer() {
    $mail = new PHPMailer(true);
    try {
        // Configuración del servidor Gmail
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        
        // --- TUS DATOS AQUÍ ---
        $mail->Username   = 'colombiamasterg@gmail.com'; 
        $mail->Password   = 'zzpz wehx kbnw mqek'; // <--- PEGA AQUÍ TU CONTRASEÑA DE APLICACIÓN
        // ----------------------

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // Quien envía el correo
        $mail->setFrom('colombiamasterg@gmail.com', 'Soporte EduPlatform');
        
        return $mail;
    } catch (Exception $e) {
        return null;
    }
}
?>