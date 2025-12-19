<?php
// modules/admin/recursos.php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(1); // Solo Administrador
require_once '../../includes/header.php';

$mensaje = "";

// 1. Lógica para subir nuevo recurso
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo'])) {
    $nombre_mostrar = trim($_POST['nombre']);
    $tipo = $_POST['tipo']; // 'Material' o 'Libro'
    
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === 0) {
        $ext = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
        $nombre_archivo = "rec_" . time() . "." . $ext;
        $ruta_destino = "../../uploads/materiales/" . $nombre_archivo;

        if (move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_destino)) {
            $sql = "INSERT INTO materiales (nombre, archivo_path, tipo, fecha_subida) VALUES (?, ?, ?, NOW())";
            $conexion->prepare($sql)->execute([$nombre_mostrar, $nombre_archivo, $tipo]);
            $mensaje = "<div class='alert alert-success small'>Archivo subido con éxito.</div>";
        }
    }
}

// 2. Lógica para borrar recurso
if (isset($_GET['borrar'])) {
    $id_borrar = $_GET['borrar'];
    // Primero buscamos el nombre del archivo para borrarlo del servidor
    $stmt = $conexion->prepare("SELECT archivo_path FROM materiales WHERE id = ?");
    $stmt->execute([$id_borrar]);
    $file = $stmt->fetch();

    if ($file) {
        $ruta = "../../uploads/materiales/" . $file['archivo_path'];
        if (file_exists($ruta)) unlink($ruta); // Borra el archivo físico
        
        $conexion->prepare("DELETE FROM materiales WHERE id = ?")->execute([$id_borrar]);
        $mensaje = "<div class='alert alert-danger small'>Archivo eliminado.</div>";
    }
}

// 3. Consultar todos los materiales
$recursos = $conexion->query("SELECT * FROM materiales ORDER BY id DESC")->fetchAll();
?>

<div class="container mt-4">
    <h2 class="fw-bold text-dark mb-4"><i class="bi bi-folder-plus text-success"></i> Biblioteca de Recursos</h2>

    <?php echo $mensaje; ?>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0 fw-bold small">Subir Nuevo Archivo</h6>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nombre del Material</label>
                            <input type="text" name="nombre" class="form-control form-control-sm" placeholder="Ej: Guía de Estudio PDF" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Tipo de Recurso</label>
                            <select name="tipo" class="form-select form-select-sm">
                                <option value="Material">Material de Apoyo</option>
                                <option value="Libro">Libro / PDF Completo</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Seleccionar Archivo</label>
                            <input type="file" name="archivo" class="form-control form-control-sm" accept=".pdf,.doc,.docx,.zip" required>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm w-100 shadow-sm">Subir a la Biblioteca</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 small">Archivo</th>
                                <th class="small">Tipo</th>
                                <th class="text-end pe-4 small">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recursos as $r): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-file-earmark-pdf fs-4 text-danger me-2"></i>
                                        <div>
                                            <div class="fw-bold text-dark small"><?php echo htmlspecialchars($r['nombre']); ?></div>
                                            <div class="text-muted" style="font-size: 0.7rem;"><?php echo $r['fecha_subida']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?php echo $r['tipo'] == 'Libro' ? 'bg-info text-dark' : 'bg-light text-dark border'; ?> btn-sm">
                                        <?php echo $r['tipo']; ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="../../uploads/materiales/<?php echo $r['archivo_path']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary border-0">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="recursos.php?borrar=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('¿Eliminar permanentemente este archivo?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($recursos)): ?>
                                <tr><td colspan="3" class="text-center py-4 text-muted small">No hay archivos en la biblioteca.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>