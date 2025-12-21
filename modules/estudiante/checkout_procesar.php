<?php
session_start();
require_once '../../config/bd.php';

if (empty($_SESSION['carrito']) || !isset($_SESSION['usuario_id'])) {
    header("Location: carrito_ver.php"); exit;
}

$uid = $_SESSION['usuario_id'];
$carrito = $_SESSION['carrito'];
$total = 0;
foreach($carrito as $c) { $total += $c['precio']; }

try {
    $conexion->beginTransaction();

    // 1. Factura
    $codigo = 'FAC-' . strtoupper(uniqid());
    $stmtF = $conexion->prepare("INSERT INTO facturas (usuario_id, total, codigo_factura, fecha_emision) VALUES (?, ?, ?, NOW())");
    $stmtF->execute([$uid, $total, $codigo]);
    $factura_id = $conexion->lastInsertId();

    // 2. Detalles de compra
    $stmtC = $conexion->prepare("INSERT INTO compras (usuario_id, item_id, tipo_item, monto_pagado, fecha_compra, factura_id) VALUES (?, ?, ?, ?, NOW(), ?)");
    
    foreach ($carrito as $item) {
        $stmtC->execute([$uid, $item['id'], $item['tipo'], $item['precio'], $factura_id]);
    }

    $conexion->commit();
    $_SESSION['carrito'] = []; // Limpiar
    
    header("Location: ver_factura.php?id=$factura_id"); 
    exit;

} catch (Exception $e) {
    $conexion->rollBack();
    die("Error en compra: " . $e->getMessage());
}
?>