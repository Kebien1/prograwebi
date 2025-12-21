<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(1);
require_once '../../includes/header.php';

if (isset($_GET['borrar'])) {
    $id = $_GET['borrar'];
    if ($id != $_SESSION['usuario_id']) {
        $conexion->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$id]);
        echo "<script>window.location='usuarios.php';</script>";
    }
}

$sql = "SELECT u.*, r.nombre as rol FROM usuarios u JOIN roles r ON u.rol_id = r.id ORDER BY u.id DESC";
$usuarios = $conexion->query($sql)->fetchAll();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">Gestión de Usuarios</h2>
        <a href="../auth/registro.php" class="btn btn-primary rounded-pill">Nuevo Usuario</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr><th class="ps-4">Usuario</th><th>Rol</th><th>Estado</th><th class="text-end pe-4">Acciones</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold"><?php echo htmlspecialchars($u['nombre_completo']); ?></div>
                            <div class="small text-muted"><?php echo htmlspecialchars($u['email']); ?></div>
                        </td>
                        <td><?php echo $u['rol']; ?></td>
                        <td>
                            <?php if ($u['estado']): ?><span class="badge bg-success">Activo</span>
                            <?php else: ?><span class="badge bg-danger">Inactivo</span><?php endif; ?>
                        </td>
                        <td class="text-end pe-4">
                            <a href="usuario_editar.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <?php if ($u['rol_id'] != 1): ?>
                            <a href="usuarios.php?borrar=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar?');"><i class="bi bi-trash"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>