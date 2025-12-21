<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(1); 

$id = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $rol = $_POST['rol_id'];
    $estado = $_POST['estado'];

    $sql = "UPDATE usuarios SET nombre_completo=?, email=?, rol_id=?, estado=? WHERE id=?";
    $conexion->prepare($sql)->execute([$nombre, $email, $rol, $estado, $id]);
    header("Location: usuarios.php");
    exit;
}

$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$u = $stmt->fetch();
$roles = $conexion->query("SELECT * FROM roles")->fetchAll();

if (!$u) { header("Location: usuarios.php"); exit; }
require_once '../../includes/header.php';
?>

<div class="container mt-5">
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 600px;">
        <div class="card-header bg-white py-3"><h4 class="mb-0 fw-bold">Editar Usuario</h4></div>
        <div class="card-body p-4">
            <form method="post">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nombre</label>
                    <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($u['nombre_completo']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Correo</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($u['email']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Rol</label>
                    <select name="rol_id" class="form-select">
                        <?php foreach ($roles as $r): ?>
                            <option value="<?php echo $r['id']; ?>" <?php echo ($u['rol_id'] == $r['id']) ? 'selected' : ''; ?>>
                                <?php echo $r['nombre']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="1" <?php echo ($u['estado'] == 1) ? 'selected' : ''; ?>>Activo</option>
                        <option value="0" <?php echo ($u['estado'] == 0) ? 'selected' : ''; ?>>Bloqueado</option>
                    </select>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a href="usuarios.php" class="btn btn-light">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>