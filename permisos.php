<?php 
include("includes/autenticacion.php");
include("includes/bd.php");

if(isset($_GET['txtID'])){
    $txtID = $_GET['txtID'];
    $sentencia=$conexion->prepare("DELETE FROM permisos WHERE ID=:id");
    $sentencia->bindParam(":id",$txtID);
    $sentencia->execute();
    header("Location:permisos.php");
    exit;
}

$sentencia=$conexion->prepare("SELECT * FROM permisos");
$sentencia->execute();
$lista_permisos = $sentencia->fetchAll(PDO::FETCH_ASSOC);

include("includes/header.php"); 
?>
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Permisos Disponibles</h5>
        <a class="btn btn-primary btn-sm" href="permisos_crear.php">Nuevo Permiso</a>
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
                <?php foreach($lista_permisos as $permiso) { ?>
                <tr>
                    <td><?php echo $permiso['ID']; ?></td>
                    <td><?php echo $permiso['Descrip']; ?></td>
                    <td>
                        <a class="btn btn-warning btn-sm" href="permisos_edit.php?txtID=<?php echo $permiso['ID']; ?>">Editar</a>
                        <a class="btn btn-danger btn-sm" href="permisos.php?txtID=<?php echo $permiso['ID']; ?>" onclick="return confirm('¿Borrar?');">Borrar</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<?php include("includes/footer.php"); ?>