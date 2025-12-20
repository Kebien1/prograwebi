<?php
// includes/security.php

// Aseguramos que la sesión PHP esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Conexión a BD: Usamos __DIR__ para que la ruta sea relativa a este archivo y no falle
require_once __DIR__ . '/../config/bd.php';

// 1. Verificación básica: ¿Existe la variable de sesión?
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit;
}

// 2. VERIFICACIÓN DE CONCURRENCIA (KICK-OUT)
$session_id_actual = session_id();

// Consultamos si este ID de sesión todavía está autorizado en la BD
$stmtCheck = $conexion->prepare("SELECT COUNT(*) FROM sesiones_activas WHERE session_id = ?");
$stmtCheck->execute([$session_id_actual]);
$existe = $stmtCheck->fetchColumn();

if ($existe == 0) {
    // Si no existe en BD, significa que fue borrada por un nuevo inicio de sesión
    session_destroy(); 
    
    // Redirigimos al login con una alerta usando JavaScript
    echo "<script>
        alert('Se ha detectado un inicio de sesión en otro dispositivo. Tu sesión ha sido cerrada.');
        window.location.href = '../../modules/auth/login.php';
    </script>";
    exit;
}

// 3. Actualizar "último acceso" para mantener esta sesión como la más reciente
$conexion->prepare("UPDATE sesiones_activas SET ultimo_acceso = NOW() WHERE session_id = ?")->execute([$session_id_actual]);


/**
 * Función para proteger áreas por rol
 */
function verificarRol($rol_requerido) {
    // Ajuste legacy: si piden rol 2 (docente), ahora exigimos 1 (admin)
    if ($rol_requerido == 2) {
        $rol_requerido = 1;
    }

    if ($_SESSION['rol_id'] != $rol_requerido) {
        if ($_SESSION['rol_id'] == 1) {
            header("Location: ../../modules/admin/dashboard.php");
        } else {
            header("Location: ../../modules/estudiante/dashboard.php");
        }
        exit;
    }
}
?>