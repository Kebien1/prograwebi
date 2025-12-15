<?php
session_start();
require_once '../../config/bd.php';

// Validar sesión
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 3) {
    header("Location: ../../index.php");
    exit;
}

// Recibimos los datos (Funciona con GET y POST)
$tipo = $_REQUEST['tipo'] ?? '';
$id_item = $_REQUEST['id'] ?? 0;
$precio = $_REQUEST['precio'] ?? 0;
$usuario_id = $_SESSION['usuario_id'];

if ($tipo && $id_item) {
    try {
        // Verificar si ya lo compró antes para no duplicar
        $check = $conexion->prepare("SELECT id FROM compras WHERE usuario_id=? AND item_id=? AND tipo_item=?");
        $check->execute([$usuario_id, $id_item, $tipo]);
        
        if($check->rowCount() == 0) {
            // Registrar compra en la base de datos
            // Nota: Aquí se usa NOW() si tu BD lo soporta, o date('Y-m-d H:i:s')
            $sql = "INSERT INTO compras (usuario_id, item_id, tipo_item, monto_pagado, fecha_compra) VALUES (?, ?, ?, ?, NOW())";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$usuario_id, $id_item, $tipo, $precio]);
            
            // Redirigir a "Mis Compras" con éxito
            header("Location: mis_compras.php?exito=1");
        } else {
            // Si ya lo tiene, lo mandamos directo a mis compras sin mensaje de nuevo éxito
            header("Location: mis_compras.php");
        }
        
    } catch (Exception $e) {
        die("Error al procesar compra: " . $e->getMessage());
    }
} else {
    // Si alguien intenta entrar sin datos
    header("Location: catalogo.php?error=datos_invalidos");
}
?>