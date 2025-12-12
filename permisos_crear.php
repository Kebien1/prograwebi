<?php 
include("includes/autenticacion.php");
include("includes/bd.php");

if($_POST){
    $Descrip = $_POST["Descrip"] ?? "";
    if($Descrip){
        $sentencia=$conexion->prepare("INSERT INTO permisos(Descrip) VALUES (:Descrip)");
        $sentencia->bindParam(":Descrip",$Descrip);
        $sentencia->execute();
        header("Location:permisos.php");
        exit;
    }
}
include("includes/header.php"); 
?>
<div class="card shadow-sm mx-auto" style="max-width: 500px;">
    <div class="card-header">Nuevo Permiso</div>
    <div class="card-body">
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Nombre del Permiso:</label>
                <input type="text" class="form-control" name="Descrip" required>
            </div>
            <button type="submit" class="btn btn-success">Guardar</button>
            <a href="permisos.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
<?php include("includes/footer.php"); ?>