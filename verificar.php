<?php
include 'includes/bd.php';

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
?>
<!doctype html>
<html lang="es">
<head>
    <title>Verificación</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
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
    <div class="text-center">
        <a class="btn btn-primary" href="index.php">Ir a iniciar sesión</a>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>