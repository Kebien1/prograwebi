<?php include("../autenticacion.php");
include("../bd.php");
if($_POST){

    $Descrip=(isset($_POST["Descrip"])?$_POST["Descrip"]:"");
 
    

    if($Descrip == "" ) {
        $error = "Todos los campos son obligatorios";
    } else {
        
        $sentencia=$conexion->prepare("INSERT INTO permisos(Descrip)
            VALUES (:Descrip)");
        $sentencia->bindParam(":Descrip",$Descrip);
        $sentencia->execute();
        header("Location:permisos.php");
        exit;
    }
}
?>
<?php include("../header.php") ?>
<br> <br>
<div class="card">
    <div class="card-header">Datos del Permiso</div>
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
                <label for="Descrip" class="form-label">Permiso:</label>
                <input type="text" class="form-control form-control-lg" name="Descrip" id="Descrip" aria-describedby="helpId"
                placeholder="Ingrese el nombre del permiso" required>
                <small id="helpId" class="form-text text-muted"></small>
            </div>
            
          



            <button type="submit" class="btn btn-success btn-lg">Guardar</button>
            <a name="" id="" class="btn btn-primary btn-lg" href="permisos.php" role="button">Cancelar</a>
        </form>
    </div>
</div>

