<?php
session_start();
require_once '../../config/bd.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 3) {
    header("Location: ../../index.php");
    exit;
}

$tipo = $_REQUEST['tipo'] ?? '';
$id_item = $_REQUEST['id'] ?? 0;
$usuario_id = $_SESSION['usuario_id'];

if ($tipo && $id_item) {
    try {
        // Verificar si ya lo tiene
        $check = $conexion->prepare("SELECT id FROM compras WHERE usuario_id=? AND item_id=? AND tipo_item=?");
        $check->execute([$usuario_id, $id_item, $tipo]);
        
        if($check->rowCount() == 0) {
            // INSCRIBIR GRATIS
            $sql = "INSERT INTO compras (usuario_id, item_id, tipo_item, monto_pagado, fecha_compra) VALUES (?, ?, ?, 0, NOW())";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$usuario_id, $id_item, $tipo]);
        }
        
        // Redirigir al contenido
        if($tipo == 'curso') {
            header("Location: aula.php?id=$id_item");
        } else {
            header("Location: mis_compras.php?exito=1");
        }
        exit;
        
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    header("Location: catalogo.php");
}
?>