<?php
// modules/auth/logout.php
session_start();
require_once '../../config/bd.php';

// ==========================================
// CONFIGURACIÓN
// ==========================================
$tabla_sesiones = "sesiones_activas";
$col_sesion     = "session_id";
// ==========================================

if (isset($_SESSION['usuario_id'])) {
    // Borrar la sesión de la base de datos para liberar el cupo
    $sessionId = session_id();
    try {
        $sql = "DELETE FROM $tabla_sesiones WHERE $col_sesion = ?";
        $conexion->prepare($sql)->execute([$sessionId]);
    } catch (Exception $e) {
        // Ignoramos errores de BD al salir
    }
}

// Destruir sesión PHP
session_unset();
session_destroy();

header("Location: login.php");
exit;
?>