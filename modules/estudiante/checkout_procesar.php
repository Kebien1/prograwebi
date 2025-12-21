<?php
session_start();
require_once '../../config/bd.php';

// 1. Validar sesión y que el carrito no esté vacío
if (empty($_SESSION['carrito']) || !isset($_SESSION['usuario_id'])) {
    header("Location: carrito_ver.php"); 
    exit;
}

// 2. SEGURIDAD: Validar que NO sea administrador
// Los administradores no deben generar facturas reales.
if (isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 1) {
    header("Location: ../admin/dashboard.php");
    exit;
}

$uid = $_SESSION['usuario_id'];
$carrito = $_SESSION['carrito'];

// Calcular el total nuevamente por seguridad
$total = 0;
foreach($carrito as $c) { 
    $total += $c['precio']; 
}

try {
    // Iniciar transacción (Todo o nada)
    $conexion->beginTransaction();

    // ---------------------------------------------------------
    // PASO 1: Generar la Factura Maestra
    // ---------------------------------------------------------
    $codigo = 'FAC-' . strtoupper(uniqid());
    
    // Insertamos la cabecera de la factura
    $stmtF = $conexion->prepare("INSERT INTO facturas (usuario_id, total, codigo_factura, fecha_emision) VALUES (?, ?, ?, NOW())");
    $stmtF->execute([$uid, $total, $codigo]);
    
    // Obtenemos el ID de la factura recién creada
    $factura_id = $conexion->lastInsertId();

    // ---------------------------------------------------------
    // PASO 2: Registrar los Detalles (Inscripción)
    // ---------------------------------------------------------
    // Esta es la parte que habilita el curso al estudiante.
    $stmtC = $conexion->prepare("INSERT INTO compras (usuario_id, item_id, tipo_item, monto_pagado, fecha_compra, factura_id) VALUES (?, ?, ?, ?, NOW(), ?)");
    
    foreach ($carrito as $item) {
        // Validación crucial: Si por alguna razón antigua no tiene tipo, forzamos 'curso'.
        // Gracias al paso anterior, esto ya debería venir correcto.
        $tipoItem = isset($item['tipo']) ? $item['tipo'] : 'curso';
        
        $stmtC->execute([
            $uid, 
            $item['id'], 
            $tipoItem, 
            $item['precio'], 
            $factura_id
        ]);
    }

    // Confirmar cambios en la BD
    $conexion->commit();
    
    // ---------------------------------------------------------
    // PASO 3: Finalizar
    // ---------------------------------------------------------
    $_SESSION['carrito'] = []; // Vaciar carrito
    
    // Redirigir al recibo
    header("Location: ver_factura.php?id=$factura_id"); 
    exit;

} catch (Exception $e) {
    // Si algo falla, deshacer todo para no dejar datos corruptos
    $conexion->rollBack();
    die("Error crítico procesando la compra: " . $e->getMessage());
}
?>