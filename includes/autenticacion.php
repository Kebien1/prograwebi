<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    // Redirige al index.php en la carpeta raíz
    header('Location: ../index.php');
    exit;
}
?>