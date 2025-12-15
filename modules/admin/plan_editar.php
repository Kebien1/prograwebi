<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(1);

$id = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $precio = $_POST['precio'];
    $limite = $_POST['limite_sesiones'];
    $desc = $_POST['descripcion'];

    $sql = "UPDATE planes SET precio=?, limite_sesiones=?, descripcion=? WHERE id=?";
    $conexion->prepare($sql)->execute([$precio, $limite, $desc, $id]);
    
    header("Location: planes.php");
    exit;
}

$stmt = $conexion->prepare("SELECT * FROM planes WHERE id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
    header("Location: planes.php");
    exit;
}

require_once '../../includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning bg-opacity-10 text-dark py-3 border-bottom-0">
                    <h4 class="mb-0 fw-bold"><i class="bi bi-tags"></i> Editar Plan: <?php echo htmlspecialchars($p['nombre']); ?></h4>
                </div>
                <div class="card-body p-4">
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Precio Mensual ($)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="precio" class="form-control" value="<?php echo $p['precio']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-danger">
                                <i class="bi bi-shield-lock"></i> Límite de Sesiones
                            </label>
                            <input type="number" name="limite_sesiones" class="form-control" value="<?php echo $p['limite_sesiones']; ?>" required min="1">
                            <div class="form-text small">
                                Cantidad máxima de dispositivos conectados simultáneamente. Si se supera, se cierra la sesión más antigua.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Descripción Pública</label>
                            <textarea name="descripcion" class="form-control" rows="3"><?php echo htmlspecialchars($p['descripcion']); ?></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning text-dark fw-bold">Guardar Cambios</button>
                            <a href="planes.php" class="btn btn-light text-muted">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>