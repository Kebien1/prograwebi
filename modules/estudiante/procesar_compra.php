<?php
session_start();
require_once '../../config/bd.php';

// 1. Validar sesión
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 3) {
    header("Location: ../../index.php");
    exit;
}

// 2. Recibir datos (Funciona tanto para GET como para POST gracias a $_REQUEST)
$tipo = $_REQUEST['tipo'] ?? '';
$id_item = $_REQUEST['id'] ?? 0;
$usuario_id = $_SESSION['usuario_id'];

// 3. Procesar la inscripción
// CORRECCIÓN: Quitamos la restricción estricta de "POST" para permitir enlaces directos
if ($tipo && $id_item) {
    
    try {
        // CASO 1: SUSCRIPCIÓN (PLANES)
        if ($tipo === 'plan') {
            // Verificar que el plan existe
            $stmt = $conexion->prepare("SELECT id, nombre FROM planes WHERE id = ?");
            $stmt->execute([$id_item]);
            $plan = $stmt->fetch();

            if ($plan) {
                // Actualizar el usuario con el nuevo plan
                $sql = "UPDATE usuarios SET plan_id = ? WHERE id = ?";
                $conexion->prepare($sql)->execute([$id_item, $usuario_id]);
                
                // Actualizar sesión actual
                $_SESSION['plan_id'] = $id_item;
                
                // Redirigir con éxito
                header("Location: suscripcion.php?pago_exitoso=1&plan=" . urlencode($plan['nombre']));
                exit;
            }
        } 
        // CASO 2: CURSOS Y LIBROS
        else {
            // Verificar si ya lo tiene comprado para no duplicar registros
            $check = $conexion->prepare("SELECT id FROM compras WHERE usuario_id=? AND item_id=? AND tipo_item=?");
            $check->execute([$usuario_id, $id_item, $tipo]);
            
            if($check->rowCount() == 0) {
                // Registrar la compra (inscripción gratuita)
                $sql = "INSERT INTO compras (usuario_id, item_id, tipo_item, monto_pagado, fecha_compra) VALUES (?, ?, ?, 0, NOW())";
                $stmt = $conexion->prepare($sql);
                $stmt->execute([$usuario_id, $id_item, $tipo]);
            }
            
            // Redirigir al contenido correspondiente
            if($tipo == 'curso') {
                header("Location: aula.php?id=$id_item&compra_exitosa=1");
            } else {
                header("Location: mis_compras.php?exito=1");
            }
            exit;
        }

    } catch (Exception $e) {
        die("Error procesando la transacción: " . $e->getMessage());
    }
} else {
    // Si intentan entrar directo sin datos válidos
    header("Location: catalogo.php");
    exit;
}
?>