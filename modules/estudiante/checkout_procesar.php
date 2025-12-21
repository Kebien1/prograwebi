<?php
session_start();
require_once '../../config/bd.php';

// Validar que haya carrito y usuario
if (empty($_SESSION['carrito']) || !isset($_SESSION['usuario_id'])) {
    header("Location: carrito_ver.php"); exit;
}

$uid = $_SESSION['usuario_id'];
$carrito = $_SESSION['carrito'];
$total = 0;

foreach($carrito as $c) { $total += $c['precio']; }

try {
    $conexion->beginTransaction();

    // 1. Crear Factura
    $codigo = 'FAC-' . strtoupper(uniqid());
    $stmtF = $conexion->prepare("INSERT INTO facturas (usuario_id, total, codigo_factura, fecha_emision) VALUES (?, ?, ?, NOW())");
    $stmtF->execute([$uid, $total, $codigo]);
    $factura_id = $conexion->lastInsertId();

    // Preparar consultas
    $stmtC = $conexion->prepare("INSERT INTO compras (usuario_id, item_id, tipo_item, monto_pagado, fecha_compra, factura_id) VALUES (?, ?, ?, ?, NOW(), ?)");
    $stmtUpdatePlan = $conexion->prepare("UPDATE usuarios SET plan_id = ? WHERE id = ?");

    foreach ($carrito as $item) {
        // A) Insertar en historial de compras (Evitando duplicados si es necesario)
        try {
            $stmtC->execute([$uid, $item['id'], $item['tipo'], $item['precio'], $factura_id]);
        } catch (PDOException $e) {
            // Si ya existe en historial, continuamos (no detenemos la compra)
        }

        // B) IMPORTANTE: Si es un PLAN, actualizar el usuario
        if ($item['tipo'] === 'plan') {
            $stmtUpdatePlan->execute([$item['id'], $uid]);
            // Actualizar sesión para efecto inmediato
            $_SESSION['plan_id'] = $item['id'];
        }
    }

    $conexion->commit();
    
    // Limpiar carrito
    $_SESSION['carrito'] = []; 
    
    // Redirigir a la factura con éxito
    header("Location: ver_factura.php?id=$factura_id"); 
    exit;

} catch (Exception $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }
    die("Error procesando la compra: " . $e->getMessage());
}
?>