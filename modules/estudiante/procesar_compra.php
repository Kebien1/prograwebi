<?php
session_start();
require_once '../../config/bd.php';

// 1. SEGURIDAD: Validar sesión y Rol
// Si no hay usuario o es Administrador (Rol 1), redirigir.
if (!isset($_SESSION['usuario_id']) || (isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 1)) {
    // Si es admin intentando comprar, lo mandamos a su dashboard
    if (isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 1) {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../../index.php");
    }
    exit;
}

// 2. Recibir datos (GET o POST)
$tipo = $_REQUEST['tipo'] ?? '';
$id_item = $_REQUEST['id'] ?? 0;
$usuario_id = $_SESSION['usuario_id'];

if ($tipo && $id_item) {
    
    try {
        $conexion->beginTransaction();

        // ---------------------------------------------------------
        // CASO 1: SUSCRIPCIÓN A PLANES (Membresías)
        // ---------------------------------------------------------
        if ($tipo === 'plan') {
            // Verificar que el plan existe
            $stmt = $conexion->prepare("SELECT id, nombre, precio FROM planes WHERE id = ?");
            $stmt->execute([$id_item]);
            $plan = $stmt->fetch();

            if ($plan) {
                // Actualizar el usuario con el nuevo plan
                $sql = "UPDATE usuarios SET plan_id = ? WHERE id = ?";
                $conexion->prepare($sql)->execute([$id_item, $usuario_id]);
                
                // Actualizar sesión actual para que vea los cambios al instante
                $_SESSION['plan_id'] = $id_item;
                
                // Opcional: Generar factura de suscripción aquí si lo deseas
                // Por ahora, confirmamos y redirigimos
                $conexion->commit();

                header("Location: suscripcion.php?pago_exitoso=1&plan=" . urlencode($plan['nombre']));
                exit;
            } else {
                throw new Exception("El plan seleccionado no existe.");
            }
        } 
        
        // ---------------------------------------------------------
        // CASO 2: CURSOS Y LIBROS (Inscripción Directa / Gratuita)
        // ---------------------------------------------------------
        else {
            // A. Verificar si el ítem existe y obtener su precio real
            $precio = 0;
            $titulo = "Producto";
            
            if ($tipo == 'curso') {
                $stmt = $conexion->prepare("SELECT titulo, precio FROM cursos WHERE id = ?");
                $stmt->execute([$id_item]);
                $prod = $stmt->fetch();
            } elseif ($tipo == 'libro' || $tipo == 'leccion') {
                // Asumiendo tabla lecciones o similar para otros tipos
                $stmt = $conexion->prepare("SELECT titulo FROM lecciones WHERE id = ?"); // Ajustar si tienes tabla libros
                $stmt->execute([$id_item]);
                $prod = $stmt->fetch();
                $prod['precio'] = 0; // Asumimos precio 0 o búscalo en su tabla
            }

            if (!$prod) {
                throw new Exception("El producto no existe.");
            }
            
            $precio = $prod['precio'] ?? 0; // Si es null, es 0

            // B. Verificar si ya lo tiene comprado para no duplicar
            $check = $conexion->prepare("SELECT id FROM compras WHERE usuario_id=? AND item_id=? AND tipo_item=?");
            $check->execute([$usuario_id, $id_item, $tipo]);
            
            if($check->rowCount() == 0) {
                
                // C. Generar FACTURA (Necesaria para mantener integridad de BD)
                $codigo = 'DIR-' . strtoupper(uniqid()); // DIR = Directo
                $stmtF = $conexion->prepare("INSERT INTO facturas (usuario_id, total, codigo_factura, fecha_emision) VALUES (?, ?, ?, NOW())");
                $stmtF->execute([$usuario_id, $precio, $codigo]);
                $factura_id = $conexion->lastInsertId();

                // D. Registrar la COMPRA vinculada a la factura
                $sql = "INSERT INTO compras (usuario_id, item_id, tipo_item, monto_pagado, fecha_compra, factura_id) VALUES (?, ?, ?, ?, NOW(), ?)";
                $stmt = $conexion->prepare($sql);
                $stmt->execute([
                    $usuario_id, 
                    $id_item, 
                    $tipo, 
                    $precio, 
                    $factura_id
                ]);
                
                $conexion->commit();

                // Redirigir al contenido
                if($tipo == 'curso') {
                    header("Location: aula.php?id=$id_item&compra_exitosa=1");
                } else {
                    header("Location: mis_compras.php?exito=1");
                }
                exit;

            } else {
                // Si ya lo tiene, simplemente lo enviamos al aula
                $conexion->rollBack(); // No guardamos nada nuevo
                if($tipo == 'curso') {
                    header("Location: aula.php?id=$id_item");
                } else {
                    header("Location: mis_compras.php");
                }
                exit;
            }
        }

    } catch (Exception $e) {
        $conexion->rollBack();
        die("Error procesando la transacción: " . $e->getMessage());
    }
} else {
    // Si intentan entrar directo sin datos válidos
    header("Location: catalogo.php");
    exit;
}
?>