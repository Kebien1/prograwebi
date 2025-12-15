<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(1); // Solo Admin
require_once '../../includes/header.php';

// Lógica de eliminación
if (isset($_GET['borrar'])) {
    $id = $_GET['borrar'];
    if ($id != $_SESSION['usuario_id']) { // Evitar auto-borrado
        $conexion->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$id]);
        echo "<script>window.location='usuarios.php';</script>";
    }
}

// Consultar usuarios
$sql = "SELECT u.*, r.nombre as rol, p.nombre as plan 
        FROM usuarios u 
        JOIN roles r ON u.rol_id = r.id 
        JOIN planes p ON u.plan_id = p.id 
        ORDER BY u.id DESC";
$usuarios = $conexion->query($sql)->fetchAll();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark"><i class="bi bi-people-fill text-primary"></i> Gestión de Usuarios</h2>
        <a href="../auth/registro.php" class="btn btn-primary rounded-pill">
            <i class="bi bi-person-plus-fill"></i> Nuevo Usuario
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="ps-4 py-3">Usuario</th>
                            <th>Rol</th>
                            <th>Plan</th>
                            <th>Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($u['nombre_completo']); ?></div>
                                <div class="small text-muted"><?php echo htmlspecialchars($u['email']); ?></div>
                            </td>
                            <td>
                                <?php if($u['rol_id'] == 1): ?>
                                    <span class="badge bg-dark">Admin</span>
                                <?php elseif($u['rol_id'] == 2): ?>
                                    <span class="badge bg-info text-dark">Docente</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Estudiante</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    $bgPlan = match($u['plan']) {
                                        'Premium' => 'bg-warning text-dark',
                                        'Pro' => 'bg-primary',
                                        default => 'bg-light text-dark border'
                                    };
                                ?>
                                <span class="badge <?php echo $bgPlan; ?>"><?php echo htmlspecialchars($u['plan']); ?></span>
                            </td>
                            <td>
                                <?php if ($u['estado']): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 rounded-pill">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-3 rounded-pill">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <a href="usuario_editar.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-primary me-1" title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                
                                <?php if ($u['rol_id'] != 1): ?>
                                <a href="usuarios.php?borrar=<?php echo $u['id']; ?>" 
                                   class="btn btn-sm btn-outline-danger" 
                                   onclick="return confirm('¿Estás seguro de eliminar a este usuario? Esta acción no se puede deshacer.');"
                                   title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>