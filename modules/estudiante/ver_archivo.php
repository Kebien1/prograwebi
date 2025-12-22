<?php
session_start();
require_once '../../config/bd.php';

// 1. Validar sesión
if (!isset($_SESSION['usuario_id'])) {
    die("Acceso denegado. Debes iniciar sesión.");
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
// Si no especifican tipo, asumimos que es 'material' (recurso de lección)
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'material'; 
$usuario_id = $_SESSION['usuario_id'];

// === CASO A: MATERIAL DE LECCIÓN ===
if ($tipo == 'material') {
    // Buscar en tabla 'materiales'
    $stmt = $conexion->prepare("SELECT archivo, titulo FROM materiales WHERE id = ?");
    $stmt->execute([$id]);
    $recurso = $stmt->fetch();

    if ($recurso) {
        // Corrección de ruta: El admin sube a 'uploads/materiales/'
        $ruta_archivo = "../../uploads/materiales/" . $recurso['archivo'];

        if (file_exists($ruta_archivo)) {
            // Entregar archivo
            $mime = mime_content_type($ruta_archivo);
            header('Content-Type: ' . $mime);
            header('Content-Disposition: inline; filename="' . basename($ruta_archivo) . '"');
            header('Content-Length: ' . filesize($ruta_archivo));
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            
            readfile($ruta_archivo);
            exit;
        } else {
            die("Error 404: El archivo físico no existe en el servidor.");
        }
    } else {
        die("Error: Recurso no encontrado en la base de datos.");
    }
}

// === CASO B: LIBRO COMPRADO (Lógica antigua conservada) ===
elseif ($tipo == 'libro') {
    
    // Verificar compra
    $sql = "SELECT l.archivo_pdf 
            FROM compras c 
            JOIN libros l ON c.item_id = l.id 
            WHERE c.usuario_id = ? AND c.item_id = ? AND c.tipo_item = 'libro'";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$usuario_id, $id]);
    $libro = $stmt->fetch();

    if ($libro && !empty($libro['archivo_pdf'])) {
        $ruta_archivo = "../../uploads/libros/" . $libro['archivo_pdf'];

        if (file_exists($ruta_archivo)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="libro_comprado.pdf"');
            readfile($ruta_archivo);
            exit;
        } else {
            echo "Error: El libro no está disponible físicamente.";
        }
    } else {
        echo "<h1>Acceso Denegado</h1><p>No has comprado este libro.</p>";
    }
} else {
    echo "Tipo de archivo no válido.";
}
?>