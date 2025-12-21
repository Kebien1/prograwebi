<?php
session_start();
require_once '../../config/bd.php';

// 1. Validar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

// 2. Validar que recibimos un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: mis_compras.php");
    exit;
}

$fid = $_GET['id'];
$uid = $_SESSION['usuario_id'];

try {
    // ---------------------------------------------------------
    // 3. Obtener datos de la Factura y del Cliente
    // ---------------------------------------------------------
    // CORRECCIÓN: Se cambió 'u.nombre, u.apellido' por 'u.nombre_completo'
    // y se quitó 'u.telefono' para evitar errores si la columna no existe.
    $sqlFactura = "SELECT f.*, u.nombre_completo, u.email 
                   FROM facturas f 
                   INNER JOIN usuarios u ON f.usuario_id = u.id 
                   WHERE f.id = ? AND f.usuario_id = ?";

    $stmt = $conexion->prepare($sqlFactura);
    $stmt->execute([$fid, $uid]);
    $f = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$f) {
        echo "<div style='padding:20px; text-align:center; font-family:sans-serif;'>
                <h2>Factura no encontrada</h2>
                <p>No se encontró la factura solicitada o no tienes permisos para verla.</p>
                <a href='mis_compras.php'>Volver a mis compras</a>
              </div>";
        exit;
    }

    // ---------------------------------------------------------
    // 4. Obtener los productos
    // ---------------------------------------------------------
    $sqlItems = "SELECT 
                    c.monto_pagado,
                    c.tipo_item,
                    COALESCE(cur.titulo, lec.titulo, 'Producto/Recurso Educativo') as nombre_item
                 FROM compras c
                 LEFT JOIN cursos cur ON (c.item_id = cur.id AND c.tipo_item = 'curso')
                 LEFT JOIN lecciones lec ON (c.item_id = lec.id AND c.tipo_item = 'leccion')
                 WHERE c.factura_id = ?";
                
    $itemsStmt = $conexion->prepare($sqlItems);
    $itemsStmt->execute([$fid]);
    $detalles = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error al cargar la factura: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo #<?php echo htmlspecialchars($f['codigo_factura']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style> 
        @media print { 
            .no-print { display:none !important; } 
            body { background: white !important; -webkit-print-color-adjust: exact; }
            .card { box-shadow: none !important; border: 1px solid #ddd !important; }
            .bg-primary { background-color: #0d6efd !important; color: white !important; }
        } 
        body { background-color: #f8f9fa; }
    </style>
</head>
<body class="p-md-5 p-3">
    <div class="card mx-auto shadow-sm border-0" style="max-width: 800px;">
        
        <div class="card-header bg-white p-4 border-bottom">
            <div class="row align-items-center">
                <div class="col-6">
                    <h3 class="text-primary fw-bold m-0">EduPlatform</h3>
                    <p class="text-muted small m-0">Comprobante de pago electrónico</p>
                </div>
                <div class="col-6 text-end">
                    <h5 class="fw-bold mb-1">#<?php echo htmlspecialchars($f['codigo_factura']); ?></h5>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-3">PAGADO</span>
                    <div class="small text-muted mt-2">
                        Fecha: <?php echo date('d/m/Y', strtotime($f['fecha_emision'])); ?><br>
                        Hora: <?php echo date('H:i', strtotime($f['fecha_emision'])); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-4">
            
            <div class="row mb-5">
                <div class="col-sm-6">
                    <h6 class="fw-bold text-secondary text-uppercase small mb-3">Facturado a:</h6>
                    <h5 class="fw-bold mb-1 text-dark">
                        <?php echo htmlspecialchars($f['nombre_completo']); ?>
                    </h5>
                    <p class="mb-0 text-muted small"><i class="bi bi-envelope-fill me-1"></i> <?php echo htmlspecialchars($f['email']); ?></p>
                </div>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-hover border-bottom">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="py-3 ps-3">Descripción</th>
                            <th scope="col" class="py-3 text-center">Tipo</th>
                            <th scope="col" class="py-3 text-end pe-3">Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($detalles) > 0): ?>
                            <?php foreach($detalles as $d): ?>
                            <tr>
                                <td class="align-middle ps-3">
                                    <?php echo htmlspecialchars($d['nombre_item']); ?>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="badge bg-secondary fw-normal"><?php echo strtoupper($d['tipo_item']); ?></span>
                                </td>
                                <td class="align-middle text-end fw-bold pe-3">
                                    $<?php echo number_format($d['monto_pagado'], 2); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">
                                    No se encontraron detalles para esta factura.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-end py-3 fw-bold">TOTAL PAGADO</td>
                            <td class="text-end bg-primary text-white py-3 fs-5 pe-3 rounded-end">
                                $<?php echo number_format($f['total'], 2); ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="alert alert-light border text-center text-muted small mt-4">
                <i class="bi bi-check-circle me-1"></i>
                Gracias por confiar en nuestra plataforma educativa. Este documento sirve como comprobante de tu inscripción.
            </div>

            <div class="text-center mt-5 no-print d-flex justify-content-center gap-3">
                <button onclick="window.print()" class="btn btn-outline-secondary px-4">
                    <i class="bi bi-printer me-2"></i> Imprimir
                </button>
                <a href="mis_compras.php" class="btn btn-primary px-4">
                    <i class="bi bi-journal-check me-2"></i> Ir a Mis Cursos
                </a>
            </div>
        </div>
    </div>
</body>
</html>