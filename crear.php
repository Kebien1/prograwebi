<?php 
include("includes/bd.php"); 
include("includes/autenticacion.php");

if($_POST){
    $usuario = $_POST["Nick"] ?? "";
    $clave = $_POST["Password"] ?? "";
    $Email = $_POST["Email"] ?? "";
    $IdRol = $_POST["IdRol"] ?? "";
    $estado = $_POST["Estado"] ?? "";

    if($usuario && $clave && $Email){
        $sentencia=$conexion->prepare("INSERT INTO usuario(Nick,Password,Email,Estado, IdRol) VALUES (:Nick,:Password,:Email,:Estado,:IdRol)");
        $sentencia->bindParam(":Nick",$usuario);
        // En producción usa password_hash($clave, PASSWORD_BCRYPT)
        $sentencia->bindParam(":Password",$clave); 
        $sentencia->bindParam(":Email",$Email);
        $sentencia->bindParam(":Estado",$estado);
        $sentencia->bindParam(":IdRol",$IdRol);
        $sentencia->execute();
        header("Location:usuarios.php");
        exit;
    }
}
include("includes/header.php");
?>
<div class="card shadow-sm mx-auto" style="max-width: 600px;">
    <div class="card-header">Nuevo Usuario</div>
    <div class="card-body">
        <form action="" method="post">
            <div class="mb-3">
                <label class="form-label">Usuario:</label>
                <input type="text" class="form-control" name="Nick" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Contraseña:</label>
                <input type="password" class="form-control" name="Password" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email:</label>
                <input type="email" class="form-control" name="Email" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Rol:</label>
                <select name="IdRol" class="form-select" required>
                    <option value="1">Administrador</option>
                    <option value="2">Estudiante</option>
                    <option value="3">Docente</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Estado:</label>
                <select name="Estado" class="form-select" required>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Guardar</button>
            <a class="btn btn-secondary" href="usuarios.php">Cancelar</a>
        </form>
    </div>
</div>
<?php include("includes/footer.php"); ?>