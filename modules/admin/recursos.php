<?php
// modules/admin/recursos.php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(1); // Solo Administrador

// --- LÓGICA AJAX PARA CARGAR LECCIONES (Se ejecuta en segundo plano) ---
if (isset($_GET['ajax_get_lecciones']) && isset($_GET['curso_id'])) {
    $stmt = $conexion->prepare("SELECT id, titulo FROM lecciones WHERE curso_id = ? ORDER BY orden ASC");
    $stmt->execute([$_GET['curso_id']]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit; // Detenemos la ejecución aquí para devolver solo el JSON
}

require_once '../../includes/header.php';

$mensaje = "";

// 1. Lógica para subir nuevo recurso
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo'])) {
    $nombre_mostrar = trim($_POST['nombre']);
    $tipo = "Material"; // Por defecto
    $curso_id = $_POST['curso_id'];
    $leccion_id = $_POST['leccion_id'];
    
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === 0) {
        $ext = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
        $nombre_archivo = "leccion_" . $leccion_id . "_" . time() . "." . $ext;
        
        // Crear carpeta si no existe
        if (!file_exists("../../uploads/materiales/")) {
            mkdir("../../uploads/materiales/", 0777, true);
        }
        
        $ruta_destino = "../../uploads/materiales/" . $nombre_archivo;

        if (move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_destino)) {
            $sql = "INSERT INTO materiales (nombre, archivo_path, tipo, curso_id, leccion_id, fecha_subida) VALUES (?, ?, ?, ?, ?, NOW())";
            $conexion->prepare($sql)->execute([$nombre_mostrar, $nombre_archivo, $tipo, $curso_id, $leccion_id]);
            $mensaje = "<div class='alert alert-success small'>Recurso adjuntado a la lección correctamente.</div>";
        }
    }
}

// 2. Lógica para borrar recurso
if (isset($_GET['borrar'])) {
    $id_borrar = $_GET['borrar'];
    $stmt = $conexion->prepare("SELECT archivo_path FROM materiales WHERE id = ?");
    $stmt->execute([$id_borrar]);
    $file = $stmt->fetch();

    if ($file) {
        $ruta = "../../uploads/materiales/" . $file['archivo_path'];
        if (file_exists($ruta)) unlink($ruta);
        $conexion->prepare("DELETE FROM materiales WHERE id = ?")->execute([$id_borrar]);
        $mensaje = "<div class='alert alert-danger small'>Archivo eliminado.</div>";
    }
}

// 3. Consultar Cursos para el Select
$cursos = $conexion->query("SELECT id, titulo FROM cursos ORDER BY id DESC")->fetchAll();

// 4. Consultar lista de materiales existentes con info del curso y lección
$sqlListado = "SELECT m.*, c.titulo as curso_titulo, l.titulo as leccion_titulo 
               FROM materiales m 
               LEFT JOIN cursos c ON m.curso_id = c.id 
               LEFT JOIN lecciones l ON m.leccion_id = l.id 
               ORDER BY m.id DESC";
$recursos = $conexion->query($sqlListado)->fetchAll();
?>

<div class="container mt-4">
    <h2 class="fw-bold text-dark mb-4"><i class="bi bi-paperclip text-success"></i> Recursos por Lección</h2>

    <?php echo $mensaje; ?>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 80px;">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0 fw-bold small">Adjuntar Archivo a una Clase</h6>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">1. Selecciona el Curso</label>
                            <select name="curso_id" id="selectCurso" class="form-select form-select-sm" required>
                                <option value="">-- Seleccionar --</option>
                                <?php foreach($cursos as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['titulo']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">2. Selecciona la Lección</label>
                            <select name="leccion_id" id="selectLeccion" class="form-select form-select-sm" required disabled>
                                <option value="">Primero elige un curso</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">3. Nombre del Archivo (Visible)</label>
                            <input type="text" name="nombre" class="form-control form-control-sm" placeholder="Ej: Código Fuente, Diapositivas..." required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">4. Archivo</label>
                            <input type="file" name="archivo" class="form-control form-control-sm" required>
                        </div>

                        <button type="submit" class="btn btn-success btn-sm w-100 shadow-sm">Subir Recurso</button>
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
                                <th class="small">Pertenece a</th>
                                <th class="text-end pe-4 small">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recursos as $r): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-file-earmark-arrow-down fs-4 text-primary me-2"></i>
                                        <div>
                                            <div class="fw-bold text-dark small"><?php echo htmlspecialchars($r['nombre']); ?></div>
                                            <div class="text-muted" style="font-size: 0.7rem;"><?php echo $r['fecha_subida']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="badge bg-light text-dark border mb-1"><?php echo htmlspecialchars($r['curso_titulo'] ?? 'Sin curso'); ?></div>
                                    <div class="small text-muted"><i class="bi bi-play-circle"></i> <?php echo htmlspecialchars($r['leccion_titulo'] ?? 'General'); ?></div>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="../../uploads/materiales/<?php echo $r['archivo_path']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary border-0">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="recursos.php?borrar=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('¿Eliminar?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('selectCurso').addEventListener('change', function() {
    let cursoId = this.value;
    let selectLeccion = document.getElementById('selectLeccion');
    
    selectLeccion.innerHTML = '<option value="">Cargando...</option>';
    selectLeccion.disabled = true;

    if(cursoId) {
        fetch(`recursos.php?ajax_get_lecciones=1&curso_id=${cursoId}`)
            .then(response => response.json())
            .then(data => {
                selectLeccion.innerHTML = '<option value="">-- Seleccionar Lección --</option>';
                data.forEach(leccion => {
                    let option = document.createElement('option');
                    option.value = leccion.id;
                    option.text = leccion.titulo;
                    selectLeccion.appendChild(option);
                });
                selectLeccion.disabled = false;
            })
            .catch(error => {
                console.error('Error:', error);
                selectLeccion.innerHTML = '<option value="">Error al cargar</option>';
            });
    } else {
        selectLeccion.innerHTML = '<option value="">Primero elige un curso</option>';
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>