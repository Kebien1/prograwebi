<?php
session_start();

// Validar que se reciba una acción por POST
if (isset($_POST['action'])) {
    $action = $_POST['action'];

    // --- ACCIÓN 1: AGREGAR AL CARRITO ---
    if ($action == 'agregar') {
        // Recibir datos del formulario
        $id = $_POST['id'];
        $titulo = $_POST['titulo'];
        $precio = $_POST['precio'];
        $instructor = isset($_POST['instructor']) ? $_POST['instructor'] : '';
        $imagen = isset($_POST['imagen']) ? $_POST['imagen'] : '';

        // Crear el array del producto
        $nuevoCurso = array(
            'id' => $id,
            'titulo' => $titulo,
            'precio' => $precio,
            'instructor' => $instructor,
            'imagen' => $imagen,
            'cantidad' => 1
        );

        // Inicializar carrito si no existe
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = array();
        }

        // Verificar si el curso YA existe en el carrito para no duplicarlo
        $ids_en_carrito = array_column($_SESSION['carrito'], 'id');
        
        if (!in_array($id, $ids_en_carrito)) {
            // Si no está, lo agregamos
            $_SESSION['carrito'][] = $nuevoCurso;
        }

        // --- REDIRECCIÓN INTELIGENTE ---
        // Esto es lo que permite "Seguir Comprando".
        // Vuelve a la página anterior (Index o Catálogo) en lugar de ir al carrito.
        if(isset($_SERVER['HTTP_REFERER'])) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else {
            // Si por alguna razón no hay referencia, ir al catálogo por defecto
            header('Location: catalogo.php');
        }
        exit;
    }

    // --- ACCIÓN 2: ELIMINAR UN CURSO ---
    if ($action == 'eliminar') {
        $id = $_POST['id'];
        
        // Buscar y eliminar el producto
        foreach ($_SESSION['carrito'] as $indice => $curso) {
            if ($curso['id'] == $id) {
                unset($_SESSION['carrito'][$indice]);
                // Reorganizar los índices para que no queden huecos (0, 2, 3...)
                $_SESSION['carrito'] = array_values($_SESSION['carrito']);
                break;
            }
        }
        // Al eliminar, sí queremos quedarnos en la vista del carrito
        header('Location: carrito_ver.php');
        exit;
    }

    // --- ACCIÓN 3: VACIAR TODO ---
    if ($action == 'vaciar') {
        unset($_SESSION['carrito']);
        header('Location: carrito_ver.php');
        exit;
    }
} else {
    // Si alguien intenta entrar directo a este archivo sin enviar datos
    header('Location: carrito_ver.php');
    exit;
}
?>