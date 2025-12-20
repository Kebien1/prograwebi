<?php
// includes/security.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// CONFIGURACIÓN (Debe ser igual al login)
// ==========================================
$tabla_sesiones = "sesiones_activas"; 
$col_usuario    = "usuario_id";       
$col_sesion     = "session_id";       
$col_tiempo     = "ultima_actividad"; 
$tiempo_maximo  = 1800; // 30 minutos en segundos
// ==========================================

// 1. Verificar si hay usuario logueado en PHP
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../modules/auth/login.php");
    exit;
}

// 2. CONTROL DE INACTIVIDAD (Timeout)
if (isset($_SESSION['ultimo_acceso'])) {
    $inactivo = time() - $_SESSION['ultimo_acceso'];
    if ($inactivo >= $tiempo_maximo) {
        // Se acabó el tiempo
        session_unset();
        session_destroy();
        header("Location: ../../modules/auth/login.php?mensaje=inactividad");
        exit;
    }
}
// Reseteamos el reloj de inactividad
$_SESSION['ultimo_acceso'] = time();


// 3. CONTROL DE DISPOSITIVOS (Consulta a Base de Datos)
// Usamos __DIR__ para encontrar bd.php sin importar desde dónde se llame este archivo
require_once __DIR__ . '/../config/bd.php'; 

try {
    $sessionId = session_id();
    
    // Verificamos si esta sesión específica existe en la BD
    $sqlCheck = "SELECT id FROM $tabla_sesiones WHERE $col_sesion = ? AND $col_usuario = ?";
    $stmtCheck = $conexion->prepare($sqlCheck);
    $stmtCheck->execute([$sessionId, $_SESSION['usuario_id']]);
    
    if ($stmtCheck->rowCount() == 0) {
        // ¡ALERTA! No existo en la BD. Significa que iniciaron sesión en otro lado y me borraron.
        session_unset();
        session_destroy();
        header("Location: ../../modules/auth/login.php?mensaje=sesion_duplicada");
        exit;
    } else {
        // Todo bien, actualizo mi hora en la BD para mantenerme "vivo"
        $sqlUpdate = "UPDATE $tabla_sesiones SET $col_tiempo = NOW() WHERE $col_sesion = ?";
        $conexion->prepare($sqlUpdate)->execute([$sessionId]);
    }

} catch (Exception $e) {
    // Si la base de datos falla momentáneamente, no bloqueamos al usuario, solo seguimos.
}

/**
 * Función auxiliar para verificar roles (Admin vs Estudiante)
 */
function verificarRol($rol_requerido) {
    if ($_SESSION['rol_id'] != $rol_requerido) {
        // Redirigir al inicio si no tiene permisos
        header("Location: ../../index.php");
        exit;
    }
}
?>