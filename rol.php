<?php 
include("includes/autenticacion.php");
include("includes/bd.php");

if(isset($_GET['txtID'])){
    $txtID = $_GET['txtID'];
    $sentencia=$conexion->prepare("DELETE FROM rol WHERE ID=:id");
    $sentencia->bindParam(":id",$txtID);
    $sentencia->execute();
    header("Location:rol.php");
    exit;
}

$sentencia=$conexion->prepare("SELECT * FROM rol");
$sentencia->execute();
$lista_roles = $sentencia->fetchAll(PDO::FETCH_ASSOC);

include("includes/header.php"); 
?>
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Roles</h5>
        <a class="btn btn-primary btn-sm" href="rol_crear.php">Nuevo Rol</a>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead class="table-primary">
                <tr>
                    <th>ID</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($lista_roles as $rol) { ?>
                <tr>
                    <td><?php echo $rol['ID']; ?></td>
                    <td><?php echo $rol['Descrip']; ?></td>
                    <td>
                        <a class="btn btn-warning btn-sm" href="rol_editar.php?txtID=<?php echo $rol['ID']; ?>">Editar</a>
                        <a class="btn btn-danger btn-sm" href="rol.php?txtID=<?php echo $rol['ID']; ?>" onclick="return confirm('¿Borrar?');">Borrar</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<?php include("includes/footer.php"); ?>