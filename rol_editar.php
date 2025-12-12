<?php 
include("includes/autenticacion.php");
include("includes/bd.php");

if(isset($_GET["txtID"])){
    $txtID = (isset($_GET["txtID"])) ? $_GET["txtID"] : "";
    $sentencia = $conexion->prepare("SELECT * FROM rol WHERE ID = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    $registro = $sentencia->fetch(PDO::FETCH_LAZY);

    if($registro){
        $ID = $registro["ID"];
        $Descrip = $registro["Descrip"];
    }
}

if($_POST){
    $txtID = (isset($_POST["ID"])) ? $_POST["ID"] : "";
    $Descrip = (isset($_POST["Descrip"])) ? $_POST["Descrip"] : "";
    
    $sentencia = $conexion->prepare("UPDATE rol SET Descrip=:Descrip WHERE ID=:id");
    $sentencia->bindParam(":Descrip",$Descrip);
    $sentencia->bindParam(":id",$txtID);
    $sentencia->execute();
    $mensaje = "Registro actualizado";
    header("Location:rol.php");
    exit;
}

include("includes/header.php"); 
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="mb-4">
                <i class="bi bi-pencil-square"></i> Editar Rol
            </h2>
            
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Datos del Rol
                    </h5>
                </div>
                
                <div class="card-body p-4">
                    <form action="" method="post">
                        
                        <input type="hidden" name="ID" value="<?php echo htmlspecialchars($txtID ?? ''); ?>"/>
                
                        <div class="mb-4">
                            <label for="ID" class="form-label fw-bold">
                                <i class="bi bi-hash"></i> ID:
                            </label>
                            <input type="text" value="<?php echo $txtID; ?>" class="form-control form-control-lg bg-light" disabled />
                        </div>

                        <div class="mb-4">
                            <label for="Descrip" class="form-label fw-bold">
                                <i class="bi bi-person"></i> Descripción:
                            </label>
                            <input type="text" value="<?php echo $Descrip ?? ''; ?>" class="form-control form-control-lg border-2" 
                                   name="Descrip" id="Descrip" required/>
                        </div>
                        
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="rol.php" class="btn btn-secondary btn-lg">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Actualizar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("includes/footer.php"); ?>