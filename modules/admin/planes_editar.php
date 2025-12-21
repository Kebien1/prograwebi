<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(1);
require_once '../../includes/header.php';

$id = $_GET['id'] ?? 0;
$plan = ['nombre' => '', 'precio' => '', 'limite_sesiones' => 1, 'descripcion' => ''];

// Si es edición, cargamos datos
if ($id) {
    $stmt = $conexion->prepare("SELECT * FROM planes WHERE id = ?");
    $stmt->execute([$id]);
    $plan = $stmt->fetch();
    if (!$plan) echo "<script>window.location='planes.php';</script>";
}

// Procesar Formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $limite = $_POST['limite'];
    $desc = $_POST['descripcion'];

    if ($id) {
        // Actualizar
        $sql = "UPDATE planes SET nombre=?, precio=?, limite_sesiones=?, descripcion=? WHERE id=?";
        $conexion->prepare($sql)->execute([$nombre, $precio, $limite, $desc, $id]);
    } else {
        // Crear
        $sql = "INSERT INTO planes (nombre, precio, limite_sesiones, descripcion) VALUES (?, ?, ?, ?)";
        $conexion->prepare($sql)->execute([$nombre, $precio, $limite, $desc]);
    }
    echo "<script>window.location='planes.php';</script>";
    exit;
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="mb-0 fw-bold"><?php echo $id ? 'Editar Plan' : 'Crear Nuevo Plan'; ?></h5>
                </div>
                <div class="card-body p-4">
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre del Plan</label>
                            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($plan['nombre']); ?>" required placeholder="Ej: Premium">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Precio ($)</label>
                                <input type="number" step="0.01" name="precio" class="form-control" value="<?php echo htmlspecialchars($plan['precio']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Límite Sesiones</label>
                                <input type="number" name="limite" class="form-control" value="<?php echo htmlspecialchars($plan['limite_sesiones']); ?>" required min="1" max="10">
                                <small class="text-muted">Dispositivos simultáneos.</small>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="3"><?php echo htmlspecialchars($plan['descripcion']); ?></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="planes.php" class="btn btn-light border">Cancelar</a>
                            <button type="submit" class="btn btn-primary fw-bold px-4">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
