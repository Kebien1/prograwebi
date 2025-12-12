<?php 
include("includes/autenticacion.php");
include("includes/bd.php");

if(isset($_GET['txtID'])){
    $txtID = isset($_GET['txtID']) ? $_GET['txtID'] : "";
    $sentencia=$conexion->prepare("DELETE FROM usuario WHERE ID=:id");
    $sentencia->bindParam(":id",$txtID);
    $sentencia->execute();
    header("Location:usuarios.php?mensaje=Registro eliminado");
    exit;
}

$sentencia=$conexion->prepare("SELECT * FROM usuario");
$sentencia->execute();
$lista_usuarios = $sentencia->fetchAll(PDO::FETCH_ASSOC);

include("includes/header.php"); 
?>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Lista de Usuarios</h5>
        <a class="btn btn-primary btn-sm" href="crear.php">
            <i class="bi bi-person-plus-fill"></i> Nuevo Usuario
        </a>
    </div>
    <div class="card-body">
        <?php if(isset($_GET['mensaje'])) { ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_GET['mensaje']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>
        
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>Rol</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($lista_usuarios as $registro) { ?>
                    <tr>
                        <td><?php echo $registro['ID']; ?></td>
                        <td><?php echo $registro['IdRol']; ?></td>
                        <td><?php echo $registro['Nick']; ?></td>
                        <td><?php echo $registro['Email']; ?></td>
                        <td><?php echo ($registro['Estado'] == 1) ? 'Activo' : 'Inactivo'; ?></td>
                        <td>
                            <a class="btn btn-warning btn-sm" href="editar.php?txtID=<?php echo $registro['ID']; ?>">Editar</a>
                            <a class="btn btn-danger btn-sm" href="usuarios.php?txtID=<?php echo $registro['ID']; ?>" onclick="return confirm('¿Borrar?');">Eliminar</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include("includes/footer.php"); ?>