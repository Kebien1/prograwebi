<?php
session_start();
require_once '../../config/bd.php';

if (!isset($_GET['id'])) header("Location: dashboard.php");

$fid = $_GET['id'];
$uid = $_SESSION['usuario_id'];

// --- CORRECCIÓN: JOIN para obtener datos del usuario (Cliente) ---
$sql = "SELECT f.*, u.nombre AS nombre_usuario, u.email 
        FROM facturas f
        JOIN usuarios u ON f.usuario_id = u.id
        WHERE f.id = ? AND f.usuario_id = ?";

$factura = $conexion->prepare($sql);
$factura->execute([$fid, $uid]);
$f = $factura->fetch();

if (!$f) die("Factura no encontrada o no tienes permiso para verla.");

// Obtener Ítems
$sqlItems = "SELECT c.*, 
        CASE 
            WHEN c.tipo_item = 'curso' THEN (SELECT titulo FROM cursos WHERE id = c.item_id)
            WHEN c.tipo_item = 'leccion' THEN (SELECT titulo FROM lecciones WHERE id = c.item_id)
            WHEN c.tipo_item = 'plan' THEN (SELECT nombre FROM planes WHERE id = c.item_id)
        END as nombre 
        FROM compras c WHERE factura_id = ?";
$items = $conexion->prepare($sqlItems);
$items->execute([$fid]);
$detalles = $items->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Recibo de Compra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> @media print { .no-print { display:none; } } </style>
</head>
<body class="bg-light p-5">
    <div class="card mx-auto shadow" style="max-width: 800px;">
        <div class="card-header bg-white p-4 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="text-primary fw-bold">EduPlatform</h3>
                    <p class="mb-0 text-muted">Comprobante de pago</p>
                </div>
                <div class="text-end">
                    <h5 class="fw-bold">#<?php echo htmlspecialchars($f['codigo_factura']); ?></h5>
                    <small class="text-muted">Fecha: <?php echo date('d/m/Y H:i', strtotime($f['fecha_emision'])); ?></small>
                </div>
            </div>
        </div>
        
        <div class="card-body p-4">
            <div class="row mb-4">
                <div class="col-sm-6">
                    <h6 class="fw-bold text-uppercase text-secondary">Facturado a:</h6>
                    <p class="mb-1 fw-bold"><?php echo htmlspecialchars($f['nombre_usuario']); ?></p>
                    <p class="mb-0"><?php echo htmlspecialchars($f['email']); ?></p>
                </div>
            </div>

            <table class="table table-bordered">
                <thead class="table-light">
                    <tr><th>Descripción</th><th>Tipo</th><th class="text-end">Importe</th></tr>
                </thead>
                <tbody>
                    <?php foreach($detalles as $d): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($d['nombre'] ?? 'Item eliminado'); ?></td>
                        <td><?php echo strtoupper($d['tipo_item']); ?></td>
                        <td class="text-end">$<?php echo number_format($d['monto_pagado'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2" class="text-end">TOTAL</th>
                        <th class="text-end bg-light fw-bold">$<?php echo number_format($f['total'], 2); ?></th>
                    </tr>
                </tfoot>
            </table>
            
            <div class="text-center mt-5 no-print">
                <button onclick="window.print()" class="btn btn-secondary me-2">Imprimir</button>
                <a href="dashboard.php" class="btn btn-primary">Volver al Panel</a>
            </div>
        </div>
    </div>
</body>
</html>