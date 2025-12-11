<?php
include 'bd.php';

$mensaje = null;
$error = null;
$token = $_GET['token'] ?? '';

if($token === ''){
    $error = 'Token no proporcionado.';
} else {
    try {
        // Buscar token
        $sql = 'SELECT ev.id, ev.user_id, u.Verificado FROM verificacion_email ev INNER JOIN usuario u ON u.ID = ev.user_id WHERE ev.token = :tok LIMIT 1';
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':tok'=>$token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!$row){
            $error = 'Token inválido o ya utilizado.';
        } else {
            // Activar usuario
            if((int)$row['Verificado'] !== 1){
                $upd = $conexion->prepare('UPDATE usuario SET Verificado = 1 WHERE ID = :id');
                $upd->execute([':id'=>$row['user_id']]);
            }
            // Borrar token
            $del = $conexion->prepare('DELETE FROM verificacion_email WHERE id = :id');
            $del->execute([':id'=>$row['id']]);
            $mensaje = 'Cuenta verificada correctamente. Ya puedes iniciar sesión.';
        }
    } catch(Exception $e){
        $error = 'Error al verificar: ' . $e->getMessage();
    }
}

include 'header.php';
?>
<div class="container mt-5">
    <?php if($mensaje): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <a class="btn btn-primary" href="index.php">Ir a iniciar sesión</a>
</div>
