<?php include("../bd.php"); 
if($_POST){

    $usuario=(isset($_POST["Nick"])?$_POST["Nick"]:"");
    $clave=(isset($_POST["Password"])?$_POST["Password"]:"");
    $Email=(isset($_POST["Email"])?$_POST["Email"]:"");
    $IdRol=(isset($_POST["IdRol"])?$_POST["IdRol"]:"");
 
    $estado=(isset($_POST["Estado"])?$_POST["Estado"]:"");

    if($usuario == "" || $clave == "" || $Email == "" ) {
        $error = "Todos los campos son obligatorios";
    } else {
        
        $sentencia=$conexion->prepare("INSERT INTO usuario(Nick,Password,Email,Estado, IdRol)
            VALUES (:Nick,:Password,:Email,:Estado,:IdRol)");
        $sentencia->bindParam(":Nick",$usuario);
        $sentencia->bindParam(":Password",$clave);
        $sentencia->bindParam(":Email",$Email);
        $sentencia->bindParam(":Estado",$estado);
        $sentencia->bindParam(":IdRol",$IdRol);
        $sentencia->execute();
        header("Location:usuarios.php");
        exit;
    }
}
?>
<?php include("../header.php") ?>
<br> <br>
<div class="card">
    <div class="card-header">Datos del usuario</div>
    <div class="card-body">
        <?php if(isset($error)) { ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle"></i>
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } ?>
        
        <form action="" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="Nick" class="form-label">Usuario:</label>
                <input type="text" class="form-control form-control-lg" name="Nick" id="Nick" aria-describedby="helpId"
                placeholder="Ingrese el nombre de usuario" required>
                <small id="helpId" class="form-text text-muted"></small>
            </div>
            
          

            <div class="mb-3">
                <label for="clave" class="form-label">Contraseña:</label>
                <input type="password" class="form-control form-control-lg" name="Password" id="Password" aria-describedby="helpId" 
                placeholder="Ingrese la contraseña" required>
                <small id="helpId" class="form-text text-muted"></small>
            </div>

            <div class="mb-3">
                <label for="Email" class="form-label">Email:</label>
                <input type="email" class="form-control form-control-lg" name="Email" id="Email" aria-describedby="helpId"
                placeholder="Ingrese el correo electrónico" required>
                <small id="helpId" class="form-text text-muted"></small>
            </div>

            <div class="mb-3">
                <label for="IdRol" class="form-label">Rol:</label>
                <select name="IdRol" id="IdRol" class="form-select form-select-lg" required>
                    <option value="1">ADMINISTRADOR</option>
                    <option value="2">ESTUDIANTE</option>
                    <option value="3">Docente</option>
                </select>
            </div>


            <div class="mb-3">
                <label for="Estado" class="form-label">Estado:</label>
                <select name="Estado" id="Estado" class="form-select form-select-lg" required>
                    <option value="1">ACTIVO</option>
                    <option value="0">INACTIVO</option>
                </select>
            </div>



            <button type="submit" class="btn btn-success btn-lg">Guardar</button>
            <a name="" id="" class="btn btn-primary btn-lg" href="usuarios.php" role="button">Cancelar</a>
        </form>
    </div>
</div>

