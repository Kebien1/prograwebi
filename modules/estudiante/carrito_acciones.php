<?php
session_start();

// 1. SEGURIDAD: Evitar que administradores usen el carrito
// Si el rol es 1 (Admin), lo redirigimos a su panel y detenemos el script.
if (isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 1) {
    header("Location: ../admin/dashboard.php");
    exit;
}

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
        
        // CORRECCIÓN CRÍTICA: Recibir y guardar el TIPO de ítem.
        // Si el formulario no envía 'tipo', asumimos que es un 'curso'.
        $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : 'curso';

        // Crear el array del producto con todos los datos necesarios
        $nuevoItem = array(
            'id' => $id,
            'titulo' => $titulo,
            'precio' => $precio,
            'instructor' => $instructor,
            'imagen' => $imagen,
            'tipo' => $tipo, // <--- ESTO FALTABA: Sin esto, la inscripción fallaba.
            'cantidad' => 1
        );

        // Inicializar carrito si no existe
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = array();
        }

        // Verificar si el ítem YA existe en el carrito para no duplicarlo
        // Usamos un identificador único combinado (id + tipo) por seguridad
        $existe = false;
        foreach ($_SESSION['carrito'] as $item) {
            if ($item['id'] == $id && $item['tipo'] == $tipo) {
                $existe = true;
                break;
            }
        }
        
        if (!$existe) {
            $_SESSION['carrito'][] = $nuevoItem;
        }

        // --- REDIRECCIÓN INTELIGENTE ---
        // Vuelve a la página donde estaba el usuario (Catálogo, Ver Curso, etc.)
        if(isset($_SERVER['HTTP_REFERER'])) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else {
            header('Location: catalogo.php');
        }
        exit;
    }

    // --- ACCIÓN 2: ELIMINAR UN ÍTEM ---
    if ($action == 'eliminar') {
        $id = $_POST['id'];
        
        // Buscar y eliminar el producto del array de sesión
        foreach ($_SESSION['carrito'] as $indice => $curso) {
            if ($curso['id'] == $id) {
                unset($_SESSION['carrito'][$indice]);
                // Reorganizar los índices para evitar errores
                $_SESSION['carrito'] = array_values($_SESSION['carrito']);
                break;
            }
        }
        // Nos quedamos en el carrito
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
    // Si entran directo sin datos, mandar al carrito visual
    header('Location: carrito_ver.php');
    exit;
}
?>