<?php
session_start();
require_once '../../config/bd.php';

if (!isset($_GET['id'])) header("Location: dashboard.php");

$fid = $_GET['id'];
$uid = $_SESSION['usuario_id'];

// Obtener Factura
$factura = $conexion->prepare("SELECT * FROM facturas WHERE id = ? AND usuario_id = ?");
$factura->execute([$fid, $uid]);
$f = $factura->fetch();

if (!$f) die("Factura no encontrada.");

// Obtener Ítems
$sql = "SELECT c.*, 
        CASE 
            WHEN c.tipo_item = 'curso' THEN (SELECT titulo FROM cursos WHERE id = c.item_id)
            WHEN c.tipo_item = 'leccion' THEN (SELECT titulo FROM lecciones WHERE id = c.item_id)
        END as nombre 
        FROM compras c WHERE factura_id = ?";
$items = $conexion->prepare($sql);
$items->execute([$fid]);
$detalles = $items->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Recibo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> @media print { .no-print { display:none; } } </style>
</head>
<body class="bg-light p-5">
    <div class="card mx-auto shadow" style="max-width: 800px;">
        <div class="card-header bg-white p-4 border-bottom">
            <div class="d-flex justify-content-between">
                <h3 class="text-primary fw-bold">EduPlatform</h3>
                <div class="text-end">
                    <h5>#<?php echo $f['codigo_factura']; ?></h5>
                    <small><?php echo $f['fecha_emision']; ?></small>
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr><th>Descripción</th><th>Tipo</th><th class="text-end">Importe</th></tr>
                </thead>
                <tbody>
                    <?php foreach($detalles as $d): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($d['nombre']); ?></td>
                        <td><?php echo strtoupper($d['tipo_item']); ?></td>
                        <td class="text-end">$<?php echo number_format($d['monto_pagado'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2" class="text-end">TOTAL</th>
                        <th class="text-end bg-light">$<?php echo number_format($f['total'], 2); ?></th>
                    </tr>
                </tfoot>
            </table>
            <div class="text-center mt-4 no-print">
                <button onclick="window.print()" class="btn btn-secondary">Imprimir</button>
                <a href="dashboard.php" class="btn btn-primary">Volver al Inicio</a>
            </div>
        </div>
    </div>
</body>
</html>