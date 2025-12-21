<?php
session_start();

// Inicializar
if (!isset($_SESSION['carrito'])) { $_SESSION['carrito'] = []; }

// 1. Agregar al Carrito
if (isset($_POST['accion']) && $_POST['accion'] == 'agregar') {
    $id = $_POST['id'];
    $titulo = $_POST['titulo'];
    $precio = $_POST['precio'];
    $tipo = $_POST['tipo']; 

    // Evitar duplicados
    $existe = false;
    foreach ($_SESSION['carrito'] as $item) {
        if ($item['id'] == $id && $item['tipo'] == $tipo) {
            $existe = true; break;
        }
    }

    if (!$existe) {
        $_SESSION['carrito'][] = [
            'id' => $id, 'titulo' => $titulo, 'precio' => $precio, 'tipo' => $tipo
        ];
    }
    
    // Volver a donde estaba
    header('Location: carrito_ver.php'); // Redirigir directo al carrito para confirmar
    exit;
}

// 2. Eliminar ítem
if (isset($_GET['eliminar_id'])) {
    $id_borrar = $_GET['eliminar_id'];
    foreach ($_SESSION['carrito'] as $k => $item) {
        if ($item['id'] == $id_borrar) { unset($_SESSION['carrito'][$k]); break; }
    }
    $_SESSION['carrito'] = array_values($_SESSION['carrito']); // Reordenar
    header('Location: carrito_ver.php'); exit;
}

// 3. Vaciar
if (isset($_GET['vaciar'])) {
    $_SESSION['carrito'] = [];
    header('Location: carrito_ver.php'); exit;
}
?>