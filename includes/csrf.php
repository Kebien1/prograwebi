<?php
// includes/csrf.php

if (session_status() === PHP_SESSION_NONE) session_start();

// 1. Generar token si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 2. Función para poner el campo oculto en el formulario HTML
function csrf_campo() {
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

// 3. Función para verificar el token al recibir datos POST
function verificar_csrf() {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("<h1>Error de Seguridad</h1><p>La solicitud fue bloqueada por seguridad. Recarga la página.</p>");
    }
}
?>