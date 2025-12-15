<?php
// includes/security.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Si no hay usuario logueado, lo manda a la portada
if (!isset($_SESSION['usuario_id'])) {
    // Ajusta la ruta si es necesario según donde esté tu index
    header("Location: ../../index.php");
    exit;
}

// Función simple para proteger por rol (Admin, Docente, Estudiante)
function verificarRol($rol_requerido) {
    if ($_SESSION['rol_id'] != $rol_requerido) {
        // Si no tiene el rol, lo manda a su dashboard correspondiente
        if ($_SESSION['rol_id'] == 1) {
            header("Location: ../../modules/admin/dashboard.php");
        } elseif ($_SESSION['rol_id'] == 2) {
            header("Location: ../../modules/docente/dashboard.php");
        } else {
            header("Location: ../../modules/estudiante/dashboard.php");
        }
        exit;
    }
}
?>