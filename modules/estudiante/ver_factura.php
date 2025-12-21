<?php
session_start();
require_once '../../config/bd.php';

// 1. Validar seguridad
if (!isset($_SESSION['usuario_id']) || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$id_factura = $_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

// 2. Obtener datos de la FACTURA (Cabecera)
// Verificamos "AND usuario_id = ?" para que nadie pueda ver facturas ajenas
$stmt = $conexion->prepare("SELECT * FROM facturas WHERE id = ? AND usuario_id = ?");
$stmt->execute([$id_factura, $usuario_id]);
$factura = $stmt->fetch();

if(!$factura) {
    die("Error: Factura no encontrada o no tienes permiso para verla.");
}

// 3. Obtener los DETALLES (Items comprados)
// Usamos subconsultas SQL para obtener el título correcto dependiendo si es curso o lección
$sqlDetalles = "
    SELECT c.*, 
           CASE 
               WHEN c.tipo_item = 'leccion' THEN (SELECT titulo FROM lecciones WHERE id = c.item_id)
               WHEN c.tipo_item = 'curso' THEN (SELECT titulo FROM cursos WHERE id = c.item_id)
           END as titulo_item
    FROM compras c 
    WHERE c.factura_id = ?
";
$stmtDetalles = $conexion->prepare($sqlDetalles);
$stmtDetalles->execute([$id_factura]);
$items = $stmtDetalles->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo #<?php echo htmlspecialchars($factura['codigo_factura']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; padding-top: 50px; }
        .invoice-card { max-width: 800px; margin: auto; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .invoice-header { background-color: #f8f9fa; border-bottom: 2px solid #e9ecef; }
        @media print {
            body { background: white; padding: 0; }
            .invoice-card { box-shadow: none; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="container mb-5">
    <div class="card invoice-card">
        <div class="card-header invoice-header p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="fw-bold text-primary m-0">EduPlatform</h3>
                    <p class="text-muted small m-0">Comprobante de Pago Electrónico</p>
                </div>
                <div class="text-end">
                    <h5 class="fw-bold text-dark">#<?php echo htmlspecialchars($factura['codigo_factura']); ?></h5>
                    <p class="mb-0 text-muted">Fecha: <?php echo date('d/m/Y H:i', strtotime($factura['fecha_emision'])); ?></p>
                </div>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="row mb-4">
                <div class="col-sm-6">
                    <h6 class="fw-bold text-uppercase small text-muted">Facturado a:</h6>
                    <p class="fw-bold mb-0"><?php echo $_SESSION['usuario_nombre'] . ' ' . $_SESSION['usuario_apellido']; ?></p>
                    <small class="text-muted"><?php echo $_SESSION['usuario_email']; ?></small>
                </div>
                <div class="col-sm-6 text-sm-end">
                    <h6 class="fw-bold text-uppercase small text-muted">Estado:</h6>
                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">PAGADO</span>
                </div>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Descripción</th>
                            <th class="text-center">Tipo</th>
                            <th class="text-end">Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($items as $item): ?>
                        <tr>
                            <td>
                                <span class="fw-bold"><?php echo htmlspecialchars($item['titulo_item']); ?></span>
                            </td>
                            <td class="text-center">
                                <?php if($item['tipo_item'] == 'curso'): ?>
                                    <span class="badge bg-primary">Curso</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Clase</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-nowrap">
                                $<?php echo number_format($item['monto_pagado'], 2); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-end fw-bold">TOTAL PAGADO</td>
                            <td class="text-end fw-bold fs-5 bg-light">$<?php echo number_format($factura['total'], 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="alert alert-light border small text-muted mb-4">
                <i class="bi bi-info-circle"></i> Gracias por tu compra. Este comprobante es válido para acceder a tu contenido educativo de forma inmediata.
            </div>

            <div class="d-flex justify-content-center gap-3 no-print">
                <button onclick="window.print()" class="btn btn-outline-secondary px-4">
                    Imprimir / Guardar PDF
                </button>
                <a href="dashboard.php" class="btn btn-primary px-5 fw-bold">
                    Volver a Mis Cursos
                </a>
            </div>
        </div>
    </div>
</div>

</body>
</html>