<?php
// modules/estudiante/checkout_procesar.php
session_start();
require_once '../../config/bd.php';

// 1. Validaciones de seguridad
if (empty($_SESSION['carrito']) || !isset($_SESSION['usuario_id'])) {
    $_SESSION['mensaje_carrito'] = [
        'tipo' => 'danger',
        'texto' => 'Tu carrito está vacío o no has iniciado sesión'
    ];
    header("Location: carrito_ver.php"); 
    exit;
}

// 2. SEGURIDAD: Validar que NO sea administrador
if (isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 1) {
    header("Location: ../admin/dashboard.php");
    exit;
}

$uid = $_SESSION['usuario_id'];
$carrito = $_SESSION['carrito'];

// 3. Validar que el carrito tenga productos válidos
if(count($carrito) == 0) {
    $_SESSION['mensaje_carrito'] = [
        'tipo' => 'warning',
        'texto' => 'No hay productos en el carrito'
    ];
    header("Location: carrito_ver.php");
    exit;
}

// 4. Calcular el total con validación
$total = 0;
foreach($carrito as $c) {
    $cantidad = isset($c['cantidad']) ? max(1, intval($c['cantidad'])) : 1;
    $precio = isset($c['precio']) ? floatval($c['precio']) : 0;
    $total += ($precio * $cantidad);
}

// Validar que el total sea mayor a 0
if($total <= 0) {
    $_SESSION['mensaje_carrito'] = [
        'tipo' => 'danger',
        'texto' => 'Error: El total de la compra no es válido'
    ];
    header("Location: carrito_ver.php");
    exit;
}

try {
    // 5. Iniciar transacción (Todo o nada)
    $conexion->beginTransaction();

    // ---------------------------------------------------------
    // PASO 1: Generar la Factura Maestra
    // ---------------------------------------------------------
    $codigo = 'FAC-' . strtoupper(uniqid());
    $fecha_actual = date('Y-m-d H:i:s');
    
    $stmtF = $conexion->prepare("INSERT INTO facturas (usuario_id, total, codigo_factura, fecha_emision) VALUES (?, ?, ?, ?)");
    $stmtF->execute([$uid, $total, $codigo, $fecha_actual]);
    
    $factura_id = $conexion->lastInsertId();

    if(!$factura_id || $factura_id <= 0) {
        throw new Exception("Error al generar la factura");
    }

    // ---------------------------------------------------------
    // PASO 2: Registrar los Detalles (Inscripción)
    // ---------------------------------------------------------
    $stmtC = $conexion->prepare("INSERT INTO compras (usuario_id, item_id, tipo_item, monto_pagado, fecha_compra, factura_id) VALUES (?, ?, ?, ?, ?, ?)");
    
    $items_procesados = 0;
    foreach ($carrito as $item) {
        // Validar datos del item
        if(!isset($item['id']) || !isset($item['precio'])) {
            continue; // Saltar items inválidos
        }

        $item_id = intval($item['id']);
        $tipoItem = isset($item['tipo']) ? trim($item['tipo']) : 'curso';
        $cantidad = isset($item['cantidad']) ? max(1, intval($item['cantidad'])) : 1;
        $precio_unitario = floatval($item['precio']);
        $monto_total = $precio_unitario * $cantidad;

        // Verificar que no esté duplicado (por si acaso)
        $checkDup = $conexion->prepare("SELECT id FROM compras WHERE usuario_id = ? AND item_id = ? AND tipo_item = ?");
        $checkDup->execute([$uid, $item_id, $tipoItem]);
        
        if($checkDup->rowCount() == 0) {
            // Insertar la compra
            $stmtC->execute([
                $uid, 
                $item_id, 
                $tipoItem, 
                $monto_total, 
                $fecha_actual,
                $factura_id
            ]);
            $items_procesados++;
        }
    }

    // Validar que se procesó al menos un item
    if($items_procesados == 0) {
        throw new Exception("No se pudo procesar ningún producto. Es posible que ya los tengas.");
    }

    // 6. Confirmar cambios en la BD
    $conexion->commit();
    
    // ---------------------------------------------------------
    // PASO 3: Finalizar - Limpiar carrito y redirigir
    // ---------------------------------------------------------
    $_SESSION['carrito'] = [];
    $_SESSION['mensaje_compra'] = [
        'tipo' => 'success',
        'texto' => '¡Compra realizada con éxito! Ya puedes acceder a tus cursos.'
    ];
    
    // Redirigir al recibo
    header("Location: ver_factura.php?id=$factura_id"); 
    exit;

} catch (Exception $e) {
    // Si algo falla, deshacer todo
    $conexion->rollBack();
    
    // Registrar el error
    error_log("Error en checkout_procesar.php: " . $e->getMessage());
    
    $_SESSION['mensaje_carrito'] = [
        'tipo' => 'danger',
        'texto' => 'Error al procesar la compra: ' . $e->getMessage()
    ];
    
    header("Location: carrito_ver.php");
    exit;
}