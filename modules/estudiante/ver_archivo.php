<?php
session_start();
require_once '../../config/bd.php';

// 1. Validar sesión y rol
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 3) {
    die("Acceso denegado. Debes iniciar sesión como estudiante.");
}

$id_libro = $_GET['id'] ?? 0;
$usuario_id = $_SESSION['usuario_id'];

// 2. Verificar que el estudiante realmente compró este libro
// Esto evita que compartan el link con gente que no pagó
$sql = "SELECT l.archivo_pdf 
        FROM compras c 
        JOIN libros l ON c.item_id = l.id 
        WHERE c.usuario_id = ? AND c.item_id = ? AND c.tipo_item = 'libro'";
$stmt = $conexion->prepare($sql);
$stmt->execute([$usuario_id, $id_libro]);
$libro = $stmt->fetch();

if ($libro && !empty($libro['archivo_pdf'])) {
    // Ruta real del archivo (debe estar fuera del alcance público si es posible, o protegido por .htaccess)
    $ruta_archivo = "../../uploads/libros/" . $libro['archivo_pdf'];

    if (file_exists($ruta_archivo)) {
        // 3. Cabeceras mágicas para mostrar en navegador sin descargar
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="documento_protegido.pdf"');
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
        
        // Deshabilitar caché para que no se guarde fácil en temporales
        header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
        header("Pragma: no-cache"); // HTTP 1.0.
        header("Expires: 0"); // Proxies.

        // Leer y enviar el archivo
        readfile($ruta_archivo);
        exit;
    } else {
        echo "Error: El archivo físico no se encuentra en el servidor.";
    }
} else {
    echo "<h1>Acceso Denegado</h1><p>No tienes permiso para ver este archivo o no lo has comprado.</p>";
}
?>