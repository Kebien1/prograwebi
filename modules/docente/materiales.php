<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(2); // Solo Docente
require_once '../../includes/header.php';

$mensaje = "";
$tab_activa = "materiales"; // Para controlar qué pestaña se muestra al recargar

// --- LÓGICA DE SUBIDA DE MATERIALES (CURSOS) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'subir_material') {
    $titulo = trim($_POST['titulo']);
    $curso_id = $_POST['curso_id']; // Nuevo: Curso seleccionado
    
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === 0) {
        $ext = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
        // Permitimos más formatos
        $permitidos = ['pdf', 'zip', 'rar', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'];
        
        if (in_array(strtolower($ext), $permitidos)) {
            $dir = "../../uploads/materiales/";
            if (!file_exists($dir)) mkdir($dir, 0777, true);
            
            $nuevo_nombre = "material_" . time() . "." . $ext;
            
            if (move_uploaded_file($_FILES['archivo']['tmp_name'], $dir . $nuevo_nombre)) {
                $sql = "INSERT INTO materiales (docente_id, curso_id, titulo, archivo, fecha_subida) VALUES (?, ?, ?, ?, NOW())";
                $conexion->prepare($sql)->execute([$_SESSION['usuario_id'], $curso_id, $titulo, $nuevo_nombre]);
                $mensaje = "<div class='alert alert-success'>Material subido al curso exitosamente.</div>";
            }
        } else {
            $mensaje = "<div class='alert alert-danger'>Formato no permitido.</div>";
        }
    }
}

// --- LÓGICA DE SUBIDA DE LIBROS (BIBLIOTECA) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'subir_libro') {
    $tab_activa = "libros"; // Mantenerse en esta pestaña
    $titulo = trim($_POST['titulo']);
    $autor = trim($_POST['autor']);
    
    if (isset($_FILES['archivo_libro']) && $_FILES['archivo_libro']['error'] === 0) {
        $ext = pathinfo($_FILES['archivo_libro']['name'], PATHINFO_EXTENSION);
        if (strtolower($ext) === 'pdf') { // Libros solo PDF
            $dir = "../../uploads/libros/";
            if (!file_exists($dir)) mkdir($dir, 0777, true);
            
            $nombre_pdf = "libro_" . time() . ".pdf";
            
            if (move_uploaded_file($_FILES['archivo_libro']['tmp_name'], $dir . $nombre_pdf)) {
                $sql = "INSERT INTO libros (titulo, autor, archivo_pdf, fecha_creacion) VALUES (?, ?, ?, NOW())";
                $conexion->prepare($sql)->execute([$titulo, $autor, $nombre_pdf]);
                $mensaje = "<div class='alert alert-success'>Libro publicado en la Biblioteca General.</div>";
            }
        } else {
            $mensaje = "<div class='alert alert-danger'>Para la biblioteca, solo se permiten archivos PDF.</div>";
        }
    }
}

// --- BORRAR ---
if (isset($_GET['borrar_material'])) {
    $id = $_GET['borrar_material'];
    $stmt = $conexion->prepare("SELECT archivo FROM materiales WHERE id = ? AND docente_id = ?");
    $stmt->execute([$id, $_SESSION['usuario_id']]);
    $archivo = $stmt->fetchColumn();
    if ($archivo) {
        @unlink("../../uploads/materiales/" . $archivo);
        $conexion->prepare("DELETE FROM materiales WHERE id = ?")->execute([$id]);
        header("Location: materiales.php"); exit;
    }
}
if (isset($_GET['borrar_libro'])) {
    $id = $_GET['borrar_libro'];
    // Validar si queremos que solo el creador borre, o cualquier docente. Por ahora simple:
    $stmt = $conexion->prepare("SELECT archivo_pdf FROM libros WHERE id = ?");
    $stmt->execute([$id]);
    $archivo = $stmt->fetchColumn();
    if ($archivo) {
        @unlink("../../uploads/libros/" . $archivo);
        $conexion->prepare("DELETE FROM libros WHERE id = ?")->execute([$id]);
        header("Location: materiales.php?tab=libros"); exit;
    }
}

if(isset($_GET['tab'])) $tab_activa = $_GET['tab'];

// --- CONSULTAS ---
// 1. Cursos del docente para el select
$mis_cursos = $conexion->prepare("SELECT id, titulo FROM cursos WHERE docente_id = ? ORDER BY id DESC");
$mis_cursos->execute([$_SESSION['usuario_id']]);
$lista_cursos = $mis_cursos->fetchAll();

// 2. Materiales subidos
$sqlMat = "SELECT m.*, c.titulo as nombre_curso FROM materiales m LEFT JOIN cursos c ON m.curso_id = c.id WHERE m.docente_id = ? ORDER BY m.id DESC";
$lista_materiales = $conexion->prepare($sqlMat);
$lista_materiales->execute([$_SESSION['usuario_id']]);
$mis_materiales = $lista_materiales->fetchAll();

// 3. Libros en la biblioteca
$lista_libros = $conexion->query("SELECT * FROM libros ORDER BY id DESC")->fetchAll();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">Centro de Recursos</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill">Volver al Panel</a>
    </div>

    <?php echo $mensaje; ?>

    <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
        <li class="nav-item">
            <button class="nav-link <?php echo $tab_activa == 'materiales' ? 'active fw-bold' : ''; ?>" id="mat-tab" data-bs-toggle="tab" data-bs-target="#materiales-content" type="button">
                <i class="bi bi-folder-fill"></i> Materiales de Curso
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?php echo $tab_activa == 'libros' ? 'active fw-bold' : ''; ?>" id="lib-tab" data-bs-toggle="tab" data-bs-target="#libros-content" type="button">
                <i class="bi bi-book-half"></i> Biblioteca Pública
            </button>
        </li>
    </ul>

    <div class="tab-content" id="myTabContent">
        
        <div class="tab-pane fade <?php echo $tab_activa == 'materiales' ? 'show active' : ''; ?>" id="materiales-content">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">Subir al Aula Virtual</h6>
                        </div>
                        <div class="card-body">
                            <form method="post" enctype="multipart/form-data">
                                <input type="hidden" name="accion" value="subir_material">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Curso de Destino</label>
                                    <select name="curso_id" class="form-select form-select-sm" required>
                                        <option value="">-- Seleccionar --</option>
                                        <?php foreach($lista_cursos as $c): ?>
                                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['titulo']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Título</label>
                                    <input type="text" name="titulo" class="form-control form-control-sm" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Archivo</label>
                                    <input type="file" name="archivo" class="form-control form-control-sm" required>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm w-100">Subir Material</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-0 table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="bg-light"><tr><th>Material</th><th>Curso</th><th class="text-end">Acción</th></tr></thead>
                                <tbody>
                                    <?php foreach($mis_materiales as $m): ?>
                                    <tr>
                                        <td><a href="../../uploads/materiales/<?php echo $m['archivo']; ?>" target="_blank"><?php echo htmlspecialchars($m['titulo']); ?></a></td>
                                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($m['nombre_curso'] ?? 'Sin asignar'); ?></span></td>
                                        <td class="text-end"><a href="materiales.php?borrar_material=<?php echo $m['id']; ?>" class="text-danger"><i class="bi bi-trash"></i></a></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if(empty($mis_materiales)): ?><tr><td colspan="3" class="text-center text-muted py-3">Sin materiales.</td></tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade <?php echo $tab_activa == 'libros' ? 'show active' : ''; ?>" id="libros-content">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">Publicar Libro (PDF)</h6>
                        </div>
                        <div class="card-body">
                            <form method="post" enctype="multipart/form-data">
                                <input type="hidden" name="accion" value="subir_libro">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Título del Libro</label>
                                    <input type="text" name="titulo" class="form-control form-control-sm" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Autor</label>
                                    <input type="text" name="autor" class="form-control form-control-sm" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Archivo PDF</label>
                                    <input type="file" name="archivo_libro" class="form-control form-control-sm" accept="application/pdf" required>
                                </div>
                                <button type="submit" class="btn btn-warning btn-sm w-100">Publicar en Biblioteca</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-0 table-responsive">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="bg-light"><tr><th>Libro</th><th>Autor</th><th class="text-end">Acción</th></tr></thead>
                                <tbody>
                                    <?php foreach($lista_libros as $l): ?>
                                    <tr>
                                        <td>
                                            <i class="bi bi-file-pdf text-danger me-1"></i>
                                            <a href="../../uploads/libros/<?php echo $l['archivo_pdf']; ?>" target="_blank" class="text-dark fw-bold"><?php echo htmlspecialchars($l['titulo']); ?></a>
                                        </td>
                                        <td><?php echo htmlspecialchars($l['autor']); ?></td>
                                        <td class="text-end">
                                            <a href="materiales.php?borrar_libro=<?php echo $l['id']; ?>" class="text-danger" onclick="return confirm('¿Eliminar libro de la biblioteca?');"><i class="bi bi-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if(empty($lista_libros)): ?><tr><td colspan="3" class="text-center text-muted py-3">La biblioteca está vacía.</td></tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>