<?php 
include("includes/autenticacion.php");
include("includes/bd.php");

$mensaje = "";
$idRolSeleccionado = isset($_GET['rol_id']) ? $_GET['rol_id'] : "";

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $idRolSeleccionado = $_POST['rol_id'];
    $permisosMarcados = isset($_POST['permisos']) ? $_POST['permisos'] : []; 

    try {
        $sentencia = $conexion->prepare("DELETE FROM detalles WHERE IdRol = :IdRol");
        $sentencia->bindParam(":IdRol", $idRolSeleccionado);
        $sentencia->execute();

        foreach($permisosMarcados as $idPermiso){
            $sentencia = $conexion->prepare("INSERT INTO detalles (IdRol, IdPermiso) VALUES (:IdRol, :IdPermiso)");
            $sentencia->bindParam(":IdRol", $idRolSeleccionado);
            $sentencia->bindParam(":IdPermiso", $idPermiso);
            $sentencia->execute();
        }
        $mensaje = "Permisos actualizados correctamente.";
    } catch(Exception $ex){
        $mensaje = "Error: " . $ex->getMessage();
    }
}

$sentenciaRoles = $conexion->prepare("SELECT * FROM rol");
$sentenciaRoles->execute();
$listaRoles = $sentenciaRoles->fetchAll(PDO::FETCH_ASSOC);

if(empty($idRolSeleccionado) && count($listaRoles) > 0){
    $idRolSeleccionado = $listaRoles[0]['ID'];
}

$sentenciaPermisos = $conexion->prepare("SELECT * FROM permisos");
$sentenciaPermisos->execute();
$listaPermisos = $sentenciaPermisos->fetchAll(PDO::FETCH_ASSOC);

$permisosAsignados = [];
if(!empty($idRolSeleccionado)){
    $sentenciaDetalles = $conexion->prepare("SELECT IdPermiso FROM detalles WHERE IdRol = :IdRol");
    $sentenciaDetalles->bindParam(":IdRol", $idRolSeleccionado);
    $sentenciaDetalles->execute();
    $permisosAsignados = $sentenciaDetalles->fetchAll(PDO::FETCH_COLUMN, 0);
}

include("includes/header.php"); 
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Administrar Permisos por Rol</h5>
            </div>
            <div class="card-body">
                <?php if($mensaje): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $mensaje; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form action="" method="get" class="mb-4">
                    <label class="form-label fw-bold">1. Selecciona el Rol:</label>
                    <div class="input-group">
                        <select name="rol_id" class="form-select" onchange="this.form.submit()">
                            <?php foreach($listaRoles as $rol): ?>
                                <option value="<?php echo $rol['ID']; ?>" <?php echo ($rol['ID'] == $idRolSeleccionado) ? 'selected' : ''; ?>>
                                    <?php echo $rol['Descrip']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-secondary" type="submit">Cargar</button>
                    </div>
                </form>

                <hr>

                <?php if(!empty($idRolSeleccionado)): ?>
                <form action="" method="post">
                    <input type="hidden" name="rol_id" value="<?php echo $idRolSeleccionado; ?>">
                    <h6 class="mb-3 fw-bold">2. Marca los permisos permitidos:</h6>
                    
                    <div class="list-group mb-3">
                        <?php foreach($listaPermisos as $permiso): ?>
                            <?php $checked = in_array($permiso['ID'], $permisosAsignados) ? "checked" : ""; ?>
                            <label class="list-group-item list-group-item-action">
                                <input class="form-check-input me-2" type="checkbox" name="permisos[]" value="<?php echo $permiso['ID']; ?>" <?php echo $checked; ?>>
                                <?php echo $permiso['Descrip']; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg">Guardar Permisos</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include("includes/footer.php"); ?>