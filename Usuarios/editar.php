<?php include("../bd.php"); 
if(isset($_GET["txtID"])){
    $txtID = (isset($_GET["txtID"])) ? $_GET["txtID"] : "";
    $sentencia = $conexion->prepare("SELECT * FROM usuario WHERE ID = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    $registro = $sentencia->fetch(PDO::FETCH_LAZY);
    
    if($registro){
        $Nick = $registro["Nick"];
        $Password = $registro["Password"];
        $Email = $registro["Email"];
        $Estado = $registro["Estado"];
        $IdRol = $registro["IdRol"];
    }
}

if($_POST){
    $txtID = (isset($_POST["ID"])) ? $_POST["ID"] : "";
    $Nick = (isset($_POST["Nick"])) ? $_POST["Nick"] : "";
    $Password = (isset($_POST["Password"])) ? $_POST["Password"] : "";
    $Email = (isset($_POST["Email"])) ? $_POST["Email"] : "";
    $Estado = (isset($_POST["Estado"])) ? $_POST["Estado"] : "";
    $IdRol = (isset($_POST["IdRol"])) ? $_POST["IdRol"] : "";

    $sentencia = $conexion->prepare("UPDATE usuario SET
        Nick=:Nick,
        Password=:Password,
        Email=:Email,
        IdRol=:IdRol,
        Estado=:Estado
        WHERE ID=:id");
    $sentencia->bindParam(":Nick",$Nick);
    $sentencia->bindParam(":Password",$Password);
    $sentencia->bindParam(":Email",$Email);
    $sentencia->bindParam(":Estado",$Estado);
    $sentencia->bindParam(":IdRol",$IdRol);
    $sentencia->bindParam(":id",$txtID);
    $sentencia->execute();
    

    $mensaje = "Registro actualizado";
    header("Location:dashboard.php?mensaje=".$mensaje);
    exit;
}
?>
<?php include("../header.php") ?>
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="mb-4">
                <i class="bi bi-pencil-square"></i> Editar Usuario
            </h2>
            
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Datos del usuario
                    </h5>
                </div>
                
                <div class="card-body p-4">
                    <form action="" method="post" enctype="multipart/form-data">
                        

                
                        <div class="mb-4">
                            <label for="ID" class="form-label fw-bold">
                                <i class="bi bi-hash"></i> ID:
                            </label>
                            <input type="hidden" value="<?php echo $txtID; ?>" name="ID" />
                            <input type="text" value="<?php echo $txtID; ?>" class="form-control form-control-lg bg-light" disabled />
                        </div>

                        <div class="mb-4">
                            <label for="IdRol" class="form-label fw-bold">
                                <i class="bi bi-hash"></i> ID ROL:
                            </label>
                            <input type="hidden" value="<?php echo $IdRol; ?>" name="IdRol" />
                            <input type="text" value="<?php echo $IdRol; ?>" class="form-control form-control-lg bg-light" disabled />
                        </div>

                        <div class="mb-4">
                            <label for="Nick" class="form-label fw-bold">
                                <i class="bi bi-person"></i> Usuario:
                            </label>
                            <input type="text" value="<?php echo $Nick ?? ''; ?>" class="form-control form-control-lg border-2" 
                                   name="Nick" id="Nick" placeholder="Nombre de usuario" required/>
                            <small class="form-text text-muted d-block mt-2">Ingrese el nombre de usuario</small>
                        </div>
                        <div class="mb-3">
                <label for="IdRol" class="form-label">Rol:</label>
                <select name="IdRol" id="IdRol" class="form-select form-select-lg" required>
                    <option value="1">ADMINISTRADOR</option>
                    <option value="2">ESTUDIANTE</option>
                    <option value="3">Docente</option>
                </select>
            </div>
                      


                        <div class="mb-4">
                            <label for="Password" class="form-label fw-bold">
                                <i class="bi bi-lock"></i> Contraseña:
                            </label>
                            <input type="password" value="<?php echo $Password ?? ''; ?>" class="form-control form-control-lg border-2" 
                                   name="Password" id="Password" placeholder="Password" required/>
                            <small class="form-text text-muted d-block mt-2">Ingrese la contraseña</small>
                        </div>
                        
               
                        <div class="mb-4">
                            <label for="Email" class="form-label fw-bold">
                                <i class="bi bi-envelope"></i> Email:
                            </label>
                            <input type="email" value="<?php echo $Email ?? ''; ?>" class="form-control form-control-lg border-2" 
                                   name="Email" id="Email" placeholder="Correo electrónico" required/>
                            <small class="form-text text-muted d-block mt-2">Ingrese el correo electrónico</small>
                        </div>
                        

                        <div class="mb-3">
                <label for="role" class="form-label">Estado:</label>
                <select name="Estado" id="Estado" class="form-select form-select-lg" required>
                    <option value="1">ACTIVO</option>
                    <option value="0">INACTIVO</option>
                </select>
                <small id="helpId" class="form-text text-muted"></small>
            </div>
                        
                      
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="usuarios.php" class="btn btn-secondary btn-lg">
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

