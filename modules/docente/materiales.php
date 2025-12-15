<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(2); // Solo Docente
require_once '../../includes/header.php';

$mensaje = "";

// SUBIR ARCHIVO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === 0) {
        $ext = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
        $permitidos = ['pdf', 'zip', 'rar', 'doc', 'docx', 'ppt'];
        
        if (in_array(strtolower($ext), $permitidos)) {
            $dir = "../../uploads/materiales/";
            if (!file_exists($dir)) mkdir($dir, 0777, true);
            
            $nuevo_nombre = "material_" . time() . "." . $ext;
            
            if (move_uploaded_file($_FILES['archivo']['tmp_name'], $dir . $nuevo_nombre)) {
                $sql = "INSERT INTO materiales (docente_id, titulo, archivo) VALUES (?, ?, ?)";
                $conexion->prepare($sql)->execute([$_SESSION['usuario_id'], $titulo, $nuevo_nombre]);
                $mensaje = "<div class='alert alert-success'>Archivo subido con éxito.</div>";
            }
        } else {
            $mensaje = "<div class='alert alert-danger'>Formato no permitido. Solo PDF, ZIP, Office.</div>";
        }
    }
}

// BORRAR ARCHIVO
if (isset($_GET['borrar'])) {
    $id = $_GET['borrar'];
    $stmt = $conexion->prepare("SELECT archivo FROM materiales WHERE id = ? AND docente_id = ?");
    $stmt->execute([$id, $_SESSION['usuario_id']]);
    $archivo = $stmt->fetchColumn();
    
    if ($archivo) {
        // Borrar físico
        @unlink("../../uploads/materiales/" . $archivo);
        // Borrar de BD
        $conexion->prepare("DELETE FROM materiales WHERE id = ?")->execute([$id]);
        header("Location: materiales.php");
        exit;
    }
}

// LISTAR
$stmt = $conexion->prepare("SELECT * FROM materiales WHERE docente_id = ? ORDER BY id DESC");
$stmt->execute([$_SESSION['usuario_id']]);
$materiales = $stmt->fetchAll();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark"><i class="bi bi-folder-fill text-warning"></i> Mis Materiales</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill">Volver al Panel</a>
    </div>

    <?php echo $mensaje; ?>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-cloud-upload"></i> Subir Archivo</h5>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Título del Material</label>
                            <input type="text" name="titulo" class="form-control" placeholder="Ej: Guía de Ejercicios" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Archivo</label>
                            <input type="file" name="archivo" class="form-control" required>
                            <div class="form-text small">PDF, ZIP, Word, PowerPoint.</div>
                        </div>
                        <button type="submit" class="btn btn-success w-100 fw-bold">Subir Material</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Nombre</th>
                                    <th>Fecha</th>
                                    <th class="text-end pe-4">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($materiales as $m): ?>
                                <tr>
                                    <td class="ps-4">
                                        <i class="bi bi-file-earmark-text text-primary fs-5 me-2"></i>
                                        <a href="../../uploads/materiales/<?php echo $m['archivo']; ?>" target="_blank" class="text-decoration-none fw-bold text-dark">
                                            <?php echo htmlspecialchars($m['titulo']); ?>
                                        </a>
                                    </td>
                                    <td class="text-muted small"><?php echo date('d/m/Y', strtotime($m['fecha_subida'])); ?></td>
                                    <td class="text-end pe-4">
                                        <a href="materiales.php?borrar=<?php echo $m['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Borrar este archivo?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($materiales)): ?>
                                    <tr><td colspan="3" class="text-center py-4 text-muted">No has subido archivos aún.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>