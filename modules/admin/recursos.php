<?php
// modules/admin/recursos.php

// Depuración activada
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(1); // Solo Administrador

// --- AJAX LECCIONES ---
if (isset($_GET['ajax_get_lecciones']) && isset($_GET['curso_id'])) {
    try {
        $stmt = $conexion->prepare("SELECT id, titulo FROM lecciones WHERE curso_id = ? ORDER BY orden ASC");
        $stmt->execute([$_GET['curso_id']]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        echo json_encode([]);
    }
    exit;
}

require_once '../../includes/header.php';

$mensaje = "";

// 1. SUBIR RECURSO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo'])) {
    try {
        if (empty($_POST['curso_id'])) throw new Exception("Selecciona un curso.");
        if (empty($_POST['leccion_id'])) throw new Exception("Selecciona una lección.");
        if (empty($_POST['titulo'])) throw new Exception("Escribe un nombre para el archivo.");
        
        $titulo = trim($_POST['titulo']);
        $tipo = "Material"; 
        $curso_id = $_POST['curso_id'];
        $leccion_id = $_POST['leccion_id'];
        
        if ($_FILES['archivo']['error'] !== 0) throw new Exception("Error al subir archivo. Code: " . $_FILES['archivo']['error']);

        $ext = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
        $nombre_archivo_fisico = "leccion_" . $leccion_id . "_" . time() . "." . $ext;
        
        $upload_dir = "../../uploads/materiales/";
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $ruta_destino = $upload_dir . $nombre_archivo_fisico;

        if (move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_destino)) {
            // CORRECCIÓN: Usamos 'titulo' y 'archivo' (en vez de 'nombre' y 'archivo_path')
            $sql = "INSERT INTO materiales (titulo, archivo, tipo, curso_id, leccion_id, fecha_subida) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$titulo, $nombre_archivo_fisico, $tipo, $curso_id, $leccion_id]);
            
            $mensaje = "<div class='alert alert-success small'>Recurso guardado correctamente.</div>";
        } else {
            throw new Exception("No se pudo mover el archivo subido.");
        }

    } catch (Exception $e) {
        $mensaje = "<div class='alert alert-danger small'>Error: " . $e->getMessage() . "</div>";
    }
}

// 2. BORRAR RECURSO
if (isset($_GET['borrar'])) {
    try {
        $id_borrar = $_GET['borrar'];
        // CORRECCIÓN: Seleccionamos 'archivo' en lugar de 'archivo_path'
        $stmt = $conexion->prepare("SELECT archivo FROM materiales WHERE id = ?");
        $stmt->execute([$id_borrar]);
        $file = $stmt->fetch();

        if ($file) {
            $ruta = "../../uploads/materiales/" . $file['archivo'];
            if (file_exists($ruta)) unlink($ruta);
            $conexion->prepare("DELETE FROM materiales WHERE id = ?")->execute([$id_borrar]);
            $mensaje = "<div class='alert alert-danger small'>Archivo eliminado.</div>";
        }
    } catch (Exception $e) {
        $mensaje = "<div class='alert alert-danger small'>Error al borrar: " . $e->getMessage() . "</div>";
    }
}

// 3. LISTADO
try {
    $cursos = $conexion->query("SELECT id, titulo FROM cursos ORDER BY id DESC")->fetchAll();

    $sqlListado = "SELECT m.*, c.titulo as curso_titulo, l.titulo as leccion_titulo 
                   FROM materiales m 
                   LEFT JOIN cursos c ON m.curso_id = c.id 
                   LEFT JOIN lecciones l ON m.leccion_id = l.id 
                   ORDER BY m.id DESC";
    $recursos = $conexion->query($sqlListado)->fetchAll();
} catch (Exception $e) {
    $mensaje = "<div class='alert alert-danger'>Error BD: " . $e->getMessage() . "</div>";
    $cursos = []; 
    $recursos = [];
}
?>

<div class="container mt-4">
    <h2 class="fw-bold text-dark mb-4"><i class="bi bi-paperclip text-success"></i> Recursos por Lección</h2>
    <?php echo $mensaje; ?>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 80px;">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0 fw-bold small">Adjuntar Archivo</h6>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">1. Curso</label>
                            <select name="curso_id" id="selectCurso" class="form-select form-select-sm" required>
                                <option value="">-- Seleccionar --</option>
                                <?php foreach($cursos as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['titulo']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">2. Lección</label>
                            <select name="leccion_id" id="selectLeccion" class="form-select form-select-sm" required disabled>
                                <option value="">Elige un curso primero</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">3. Título del Recurso</label>
                            <input type="text" name="titulo" class="form-control form-control-sm" placeholder="Ej: Ejercicios PDF" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">4. Archivo</label>
                            <input type="file" name="archivo" class="form-control form-control-sm" required>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm w-100 shadow-sm">Subir</button>
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
                                    <th class="ps-4 small">Recurso</th>
                                    <th class="small">Ubicación</th>
                                    <th class="text-end pe-4 small">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($recursos)): ?>
                                    <tr><td colspan="3" class="text-center py-4 text-muted">Sin recursos.</td></tr>
                                <?php else: ?>
                                    <?php foreach($recursos as $r): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-file-earmark-text fs-4 text-primary me-2"></i>
                                                <div>
                                                    <div class="fw-bold text-dark small"><?php echo htmlspecialchars($r['titulo'] ?? 'Sin título'); ?></div>
                                                    <div class="text-muted" style="font-size: 0.7rem;"><?php echo $r['fecha_subida']; ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="badge bg-light text-dark border mb-1"><?php echo htmlspecialchars($r['curso_titulo'] ?? '-'); ?></div>
                                            <div class="small text-muted"><?php echo htmlspecialchars($r['leccion_titulo'] ?? '-'); ?></div>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="../../uploads/materiales/<?php echo $r['archivo']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-eye"></i></a>
                                            <a href="recursos.php?borrar=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('¿Borrar?');"><i class="bi bi-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectCurso = document.getElementById('selectCurso');
    const selectLeccion = document.getElementById('selectLeccion');

    selectLeccion.disabled = true;

    selectCurso.addEventListener('change', function() {
        let cursoId = this.value;
        selectLeccion.innerHTML = '<option value="">Cargando...</option>';
        selectLeccion.disabled = true;

        if(cursoId) {
            fetch(`recursos.php?ajax_get_lecciones=1&curso_id=${cursoId}`)
                .then(r => r.json())
                .then(data => {
                    selectLeccion.innerHTML = '<option value="">-- Seleccionar --</option>';
                    if(data.length > 0) {
                        data.forEach(l => {
                            let opt = document.createElement('option');
                            opt.value = l.id;
                            opt.text = l.titulo;
                            selectLeccion.appendChild(opt);
                        });
                        selectLeccion.disabled = false;
                    } else {
                        selectLeccion.innerHTML = '<option value="">Sin lecciones</option>';
                    }
                })
                .catch(() => selectLeccion.innerHTML = '<option value="">Error</option>');
        } else {
            selectLeccion.innerHTML = '<option value="">Elige un curso primero</option>';
        }
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>