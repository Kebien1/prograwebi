<?php
// includes/security.php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// 1. VERIFICACIÓN BÁSICA: ¿Está logueado?
if (!isset($_SESSION['usuario_id'])) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

// 2. VERIFICACIÓN INTELIGENTE: ¿Su sesión sigue viva en la BD?
// Si el usuario entró en otro dispositivo, el login.php borró esta sesión de la BD.
$session_id_php = session_id();
$stmtCheck = $conexion->prepare("SELECT session_id FROM sesiones_activas WHERE session_id = :sid AND usuario_id = :uid");
$stmtCheck->execute([
    ':sid' => $session_id_php,
    ':uid' => $_SESSION['usuario_id']
]);

if ($stmtCheck->rowCount() === 0) {
    // ¡ALERTA! La sesión ya no existe en la BD (fue expulsado por otro dispositivo)
    session_unset();
    session_destroy();
    // Lo mandamos al login con un mensaje de error
    header("Location: " . BASE_URL . "modules/auth/login.php?error=expulsado");
    exit;
} else {
    // Si todo está bien, actualizamos la hora para que sepamos que está activo
    $stmtUpd = $conexion->prepare("UPDATE sesiones_activas SET ultimo_acceso = NOW() WHERE session_id = :sid");
    $stmtUpd->execute([':sid' => $session_id_php]);
}

// 3. FUNCIÓN PARA PROTEGER PÁGINAS POR ROL
function verificarRol($rol_requerido) {
    if ($_SESSION['rol_id'] != $rol_requerido) {
        // Si un estudiante intenta entrar al panel de admin, lo devolvemos a su lugar
        if ($_SESSION['rol_id'] == 1) {
            header("Location: " . BASE_URL . "modules/admin/dashboard.php");
        } elseif ($_SESSION['rol_id'] == 2) {
            header("Location: " . BASE_URL . "modules/docente/dashboard.php");
        } else {
            header("Location: " . BASE_URL . "modules/estudiante/dashboard.php");
        }
        exit;
    }
}
?>