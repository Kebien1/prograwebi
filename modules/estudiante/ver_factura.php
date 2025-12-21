<?php
session_start();
require_once '../../config/bd.php';

// Validar que recibimos un ID
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$fid = $_GET['id'];
$uid = $_SESSION['usuario_id'];

// ---------------------------------------------------------
// 1. Obtener datos de la Factura y del Cliente
// ---------------------------------------------------------
// CORRECCIÓN: Usamos INNER JOIN para traer nombre y email del usuario dueño de la factura.
$sqlFactura = "SELECT f.*, u.nombre, u.apellido, u.email, u.telefono 
               FROM facturas f 
               INNER JOIN usuarios u ON f.usuario_id = u.id 
               WHERE f.id = ? AND f.usuario_id = ?";

$stmt = $conexion->prepare($sqlFactura);
$stmt->execute([$fid, $uid]);
$f = $stmt->fetch();

if (!$f) {
    die("Error: Factura no encontrada o no tienes permiso para verla.");
}

// ---------------------------------------------------------
// 2. Obtener los productos comprados en esta factura
// ---------------------------------------------------------
// Hacemos subconsultas para obtener el nombre real del curso o lección según el tipo.
$sqlItems = "SELECT c.*, 
            CASE 
                WHEN c.tipo_item = 'curso' THEN (SELECT titulo FROM cursos WHERE id = c.item_id)
                WHEN c.tipo_item = 'leccion' THEN (SELECT titulo FROM lecciones WHERE id = c.item_id)
                ELSE 'Recurso Educativo'
            END as nombre_item 
            FROM compras c WHERE factura_id = ?";
            
$items = $conexion->prepare($sqlItems);
$items->execute([$fid]);
$detalles = $items->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo #<?php echo htmlspecialchars($f['codigo_factura']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> 
        @media print { 
            .no-print { display:none !important; } 
            body { background: white !important; }
            .card { box-shadow: none !important; border: 1px solid #ddd !important; }
        } 
    </style>
</head>
<body class="bg-light p-md-5 p-3">
    <div class="card mx-auto shadow-lg border-0" style="max-width: 800px;">
        
        <div class="card-header bg-white p-4 border-bottom">
            <div class="row align-items-center">
                <div class="col-6">
                    <h3 class="text-primary fw-bold m-0">EduPlatform</h3>
                    <p class="text-muted small m-0">Comprobante de pago electrónico</p>
                </div>
                <div class="col-6 text-end">
                    <h5 class="fw-bold mb-1">#<?php echo htmlspecialchars($f['codigo_factura']); ?></h5>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success">PAGADO</span>
                    <div class="small text-muted mt-1">
                        <?php echo date('d/m/Y H:i', strtotime($f['fecha_emision'])); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-4">
            
            <div class="row mb-5">
                <div class="col-sm-6">
                    <h6 class="fw-bold text-secondary text-uppercase small mb-3">Facturado a:</h6>
                    <h5 class="fw-bold mb-1">
                        <?php echo htmlspecialchars($f['nombre'] . ' ' . $f['apellido']); ?>
                    </h5>
                    <p class="mb-0 text-muted"><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($f['email']); ?></p>
                    <?php if(!empty($f['telefono'])): ?>
                        <p class="mb-0 text-muted"><i class="bi bi-phone"></i> <?php echo htmlspecialchars($f['telefono']); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-striped border">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" class="py-3">Descripción</th>
                            <th scope="col" class="py-3 text-center">Tipo</th>
                            <th scope="col" class="py-3 text-end">Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($detalles as $d): ?>
                        <tr>
                            <td class="align-middle">
                                <?php echo htmlspecialchars($d['nombre_item'] ?: 'Ítem eliminado o no disponible'); ?>
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge bg-secondary"><?php echo strtoupper($d['tipo_item']); ?></span>
                            </td>
                            <td class="align-middle text-end fw-bold">
                                $<?php echo number_format($d['monto_pagado'], 2); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="border-top border-2 border-dark">
                            <th colspan="2" class="text-end py-3">TOTAL PAGADO</th>
                            <th class="text-end bg-primary text-white py-3 fs-5">
                                $<?php echo number_format($f['total'], 2); ?>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="alert alert-light border text-center text-muted small">
                Gracias por confiar en nuestra plataforma educativa. Este documento sirve como comprobante de tu inscripción.
            </div>

            <div class="text-center mt-4 no-print d-grid gap-2 d-sm-block">
                <button onclick="window.print()" class="btn btn-outline-secondary px-4 me-sm-2">
                    Imprimir / Guardar PDF
                </button>
                <a href="mis_compras.php" class="btn btn-primary px-4">
                    Ir a Mis Cursos
                </a>
            </div>
        </div>
    </div>
</body>
</html>