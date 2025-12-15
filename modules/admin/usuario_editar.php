<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(1); 

$id = $_GET['id'] ?? 0;

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $rol = $_POST['rol_id'];
    $plan = $_POST['plan_id'];
    $estado = $_POST['estado'];

    $sql = "UPDATE usuarios SET nombre_completo=?, email=?, rol_id=?, plan_id=?, estado=? WHERE id=?";
    $conexion->prepare($sql)->execute([$nombre, $email, $rol, $plan, $estado, $id]);
    
    header("Location: usuarios.php");
    exit;
}

// Obtener datos actuales
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$u = $stmt->fetch();

if (!$u) {
    header("Location: usuarios.php");
    exit;
}

// Listas para selects
$roles = $conexion->query("SELECT * FROM roles")->fetchAll();
$planes = $conexion->query("SELECT * FROM planes")->fetchAll();

require_once '../../includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h4 class="mb-0 fw-bold text-primary"><i class="bi bi-person-gear"></i> Editar Usuario</h4>
                </div>
                <div class="card-body p-4">
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($u['nombre_completo']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($u['email']); ?>" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Rol</label>
                                <select name="rol_id" class="form-select">
                                    <?php foreach ($roles as $r): ?>
                                        <option value="<?php echo $r['id']; ?>" <?php echo ($u['rol_id'] == $r['id']) ? 'selected' : ''; ?>>
                                            <?php echo $r['nombre']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Plan</label>
                                <select name="plan_id" class="form-select">
                                    <?php foreach ($planes as $p): ?>
                                        <option value="<?php echo $p['id']; ?>" <?php echo ($u['plan_id'] == $p['id']) ? 'selected' : ''; ?>>
                                            <?php echo $p['nombre']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Estado de la Cuenta</label>
                            <select name="estado" class="form-select">
                                <option value="1" <?php echo ($u['estado'] == 1) ? 'selected' : ''; ?>>Activo (Permitir acceso)</option>
                                <option value="0" <?php echo ($u['estado'] == 0) ? 'selected' : ''; ?>>Bloqueado (Denegar acceso)</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="usuarios.php" class="btn btn-light px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-4">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>