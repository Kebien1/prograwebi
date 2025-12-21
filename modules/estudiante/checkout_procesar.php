<?php
session_start();
require_once '../../config/bd.php';

// 1. Validaciones de Seguridad
// Si no está logueado o el carrito está vacío, lo expulsamos
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['carrito'])) {
    header("Location: carrito_ver.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$carrito = $_SESSION['carrito'];
$total = 0;

// Calcular total nuevamente por seguridad
foreach($carrito as $item) { 
    $total += $item['precio']; 
}

try {
    // 2. Iniciar Transacción (Todo o Nada)
    $conexion->beginTransaction();

    // ---------------------------------------------------------
    // A) CREAR LA FACTURA (CABECERA)
    // ---------------------------------------------------------
    // Generamos un código único, ej: FAC-65A2B3C
    $codigo_factura = 'FAC-' . strtoupper(substr(uniqid(), -7));
    
    $sqlFactura = "INSERT INTO facturas (usuario_id, total, codigo_factura, fecha_emision) VALUES (?, ?, ?, NOW())";
    $stmtF = $conexion->prepare($sqlFactura);
    $stmtF->execute([$usuario_id, $total, $codigo_factura]);
    
    // Obtenemos el ID de la factura recién creada
    $factura_id = $conexion->lastInsertId();

    // ---------------------------------------------------------
    // B) REGISTRAR CADA ÍTEM COMPRADO
    // ---------------------------------------------------------
    $sqlCompra = "INSERT INTO compras (usuario_id, item_id, tipo_item, monto_pagado, fecha_compra, factura_id) 
                  VALUES (?, ?, ?, ?, NOW(), ?)";
    $stmtC = $conexion->prepare($sqlCompra);

    foreach ($carrito as $item) {
        // Insertamos cada curso o lección vinculándolo a la factura
        $stmtC->execute([
            $usuario_id, 
            $item['id'], 
            $item['tipo'], 
            $item['precio'], 
            $factura_id
        ]);
    }

    // 3. Confirmar Transacción
    $conexion->commit();

    // 4. Limpieza y Redirección
    $_SESSION['carrito'] = []; // Vaciamos el carrito porque ya pagó
    
    // Redirigimos a ver el recibo (Paso 6)
    header("Location: ver_factura.php?id=$factura_id");
    exit;

} catch (Exception $e) {
    // Si algo falla, deshacemos todo
    $conexion->rollBack();
    die("Error procesando la compra: " . $e->getMessage());
}
?>