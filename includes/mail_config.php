
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '../../phpmailer/PHPMailer.php';
require_once __DIR__ . '../../phpmailer/SMTP.php';
require_once __DIR__ . '../../phpmailer/Exception.php';
function crearMailer(): PHPMailer {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    
    $mail->Username = 'colombiamasterg@gmail.com'; 
    $mail->Password = 'zzpz wehx kbnw mqek';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';
    
    $mail->setFrom('colombiamasterg@gmail.com', 'PrograWeb I');
    
    return $mail;
}
