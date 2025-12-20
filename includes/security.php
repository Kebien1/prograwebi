<?php
// includes/security.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Si no hay usuario logueado, lo manda a la portada
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit;
}

/**
 * Función para proteger áreas de gestión.
 * Ahora, las áreas que antes eran para docentes (Rol 2) 
 * serán validadas exclusivamente para el Administrador (Rol 1).
 */
function verificarRol($rol_requerido) {
    // Si el rol requerido es 2 (Docente), ahora exigimos que sea 1 (Admin)
    if ($rol_requerido == 2) {
        $rol_requerido = 1;
    }

    if ($_SESSION['rol_id'] != $rol_requerido) {
        // Redirección según el rol que tenga la sesión actual
        if ($_SESSION['rol_id'] == 1) {
            header("Location: ../../modules/admin/dashboard.php");
        } else {
            // Estudiantes o cualquier otro rol van a su panel
            header("Location: ../../modules/estudiante/dashboard.php");
        }
        exit;
    }
}
?>