<?php
session_start();

// 1. Inicializar el carrito si aún no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// ==========================================
// ACCIÓN: AGREGAR AL CARRITO
// ==========================================
if (isset($_POST['accion']) && $_POST['accion'] == 'agregar') {
    
    // Recibir datos del formulario (que enviaremos desde el aula o catálogo)
    $id = $_POST['id'];
    $titulo = $_POST['titulo'];
    $precio = $_POST['precio'];
    $tipo = $_POST['tipo']; // 'leccion' o 'curso'

    // Validación básica: evitar duplicados
    // (No queremos que compren la misma clase dos veces en el mismo pedido)
    $ya_existe = false;
    foreach ($_SESSION['carrito'] as $item) {
        if ($item['id'] == $id && $item['tipo'] == $tipo) {
            $ya_existe = true;
            break;
        }
    }

    // Si no está repetido, lo guardamos
    if (!$ya_existe) {
        $_SESSION['carrito'][] = [
            'id' => $id,
            'titulo' => $titulo,
            'precio' => $precio,
            'tipo' => $tipo
        ];
    }
    
    // Redirigir al usuario
    // Lo devolvemos a la página donde estaba (HTTP_REFERER) para que siga navegando
    $url_retorno = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'catalogo.php';
    header('Location: ' . $url_retorno);
    exit;
}

// ==========================================
// ACCIÓN: ELIMINAR UN ÍTEM ESPECÍFICO
// ==========================================
if (isset($_GET['eliminar_id']) && isset($_GET['tipo'])) {
    $id_borrar = $_GET['eliminar_id'];
    $tipo_borrar = $_GET['tipo'];

    // Buscar y eliminar del array
    foreach ($_SESSION['carrito'] as $indice => $item) {
        if ($item['id'] == $id_borrar && $item['tipo'] == $tipo_borrar) {
            unset($_SESSION['carrito'][$indice]);
            break; 
        }
    }
    
    // Reordenar los índices del array para que no queden huecos
    $_SESSION['carrito'] = array_values($_SESSION['carrito']);

    // Volver al carrito
    header('Location: carrito_ver.php');
    exit;
}

// ==========================================
// ACCIÓN: VACIAR TODO EL CARRITO
// ==========================================
if (isset($_GET['vaciar'])) {
    $_SESSION['carrito'] = []; // Se borra todo
    header('Location: carrito_ver.php');
    exit;
}

// Si alguien intenta abrir este archivo directamente sin datos, lo sacamos
header('Location: catalogo.php');
exit;
?>