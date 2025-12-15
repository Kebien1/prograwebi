<?php
session_start();
require_once '../../config/bd.php';

// Si el usuario está logueado, borramos SU registro de sesión actual de la base de datos
if (isset($_SESSION['usuario_id'])) {
    $session_id_php = session_id();
    
    // Solo borramos la sesión actual del navegador donde dio click en "Salir"
    $stmt = $conexion->prepare("DELETE FROM sesiones_activas WHERE session_id = :sid");
    $stmt->execute([':sid' => $session_id_php]);
}

// Limpiar y destruir la sesión de PHP
session_unset();
session_destroy();

// Redirigir al inicio
header("Location: ../../index.php");
exit;
?>