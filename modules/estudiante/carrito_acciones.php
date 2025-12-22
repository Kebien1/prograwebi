<?php
// modules/estudiante/carrito_acciones.php
session_start();

// SEGURIDAD: Evitar que administradores usen el carrito
if (isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 1) {
    header("Location: ../admin/dashboard.php");
    exit;
}

// Validar que se reciba una acción por POST
if (isset($_POST['action'])) {
    $action = $_POST['action'];

    // --- ACCIÓN 1: AGREGAR AL CARRITO ---
    if ($action == 'agregar') {
        // Validar campos requeridos
        if(!isset($_POST['id']) || !isset($_POST['titulo']) || !isset($_POST['precio'])) {
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'catalogo.php'));
            exit;
        }

        // Recibir datos del formulario
        $id = intval($_POST['id']);
        $titulo = trim($_POST['titulo']);
        $precio = floatval($_POST['precio']);
        $instructor = isset($_POST['instructor']) ? trim($_POST['instructor']) : 'EduPlatform';
        $imagen = isset($_POST['imagen']) ? trim($_POST['imagen']) : '';
        
        // CAMPO CRÍTICO: tipo de ítem (curso, libro, etc.)
        $tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : 'curso';

        // Validación adicional
        if($id <= 0 || empty($titulo)) {
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'catalogo.php'));
            exit;
        }

        // Crear el array del producto completo
        $nuevoItem = array(
            'id' => $id,
            'titulo' => $titulo,
            'precio' => $precio,
            'instructor' => $instructor,
            'imagen' => $imagen,
            'tipo' => $tipo, // IMPORTANTE: Este campo es crucial
            'cantidad' => 1,
            'fecha_agregado' => date('Y-m-d H:i:s')
        );

        // Inicializar carrito si no existe
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = array();
        }

        // Verificar si el ítem YA existe en el carrito
        $existe = false;
        foreach ($_SESSION['carrito'] as $item) {
            if ($item['id'] == $id && $item['tipo'] == $tipo) {
                $existe = true;
                break;
            }
        }
        
        if (!$existe) {
            $_SESSION['carrito'][] = $nuevoItem;
            $_SESSION['mensaje_carrito'] = [
                'tipo' => 'success',
                'texto' => 'Producto agregado al carrito exitosamente'
            ];
        } else {
            $_SESSION['mensaje_carrito'] = [
                'tipo' => 'info',
                'texto' => 'Este producto ya está en tu carrito'
            ];
        }

        // Redirección inteligente
        if(isset($_SERVER['HTTP_REFERER'])) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else {
            header('Location: catalogo.php');
        }
        exit;
    }

    // --- ACCIÓN 2: ELIMINAR UN ÍTEM ---
    if ($action == 'eliminar') {
        if(!isset($_POST['id'])) {
            header('Location: carrito_ver.php');
            exit;
        }

        $id = intval($_POST['id']);
        $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : 'curso';
        
        // Buscar y eliminar el producto del array de sesión
        if(isset($_SESSION['carrito'])) {
            foreach ($_SESSION['carrito'] as $indice => $item) {
                if ($item['id'] == $id && $item['tipo'] == $tipo) {
                    unset($_SESSION['carrito'][$indice]);
                    // Reorganizar los índices
                    $_SESSION['carrito'] = array_values($_SESSION['carrito']);
                    
                    $_SESSION['mensaje_carrito'] = [
                        'tipo' => 'warning',
                        'texto' => 'Producto eliminado del carrito'
                    ];
                    break;
                }
            }
        }
        
        header('Location: carrito_ver.php');
        exit;
    }

    // --- ACCIÓN 3: VACIAR TODO ---
    if ($action == 'vaciar') {
        $_SESSION['carrito'] = [];
        $_SESSION['mensaje_carrito'] = [
            'tipo' => 'info',
            'texto' => 'Carrito vaciado completamente'
        ];
        
        header('Location: carrito_ver.php');
        exit;
    }

    // --- ACCIÓN 4: ACTUALIZAR CANTIDAD (OPCIONAL) ---
    if ($action == 'actualizar' && isset($_POST['id']) && isset($_POST['cantidad'])) {
        $id = intval($_POST['id']);
        $cantidad = max(1, intval($_POST['cantidad'])); // Mínimo 1
        
        foreach ($_SESSION['carrito'] as $indice => $item) {
            if ($item['id'] == $id) {
                $_SESSION['carrito'][$indice]['cantidad'] = $cantidad;
                break;
            }
        }
        
        header('Location: carrito_ver.php');
        exit;
    }
    
} else {
    // Si entran directo sin datos, mandar al carrito visual
    header('Location: carrito_ver.php');
    exit;
}