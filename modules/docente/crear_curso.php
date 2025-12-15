<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(2);

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $desc = trim($_POST['descripcion']);
    $docente_id = $_SESSION['usuario_id'];
    $imagen_nombre = null;

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $imagen_nombre = "curso_" . time() . "." . $ext;
        move_uploaded_file($_FILES['imagen']['tmp_name'], "../../uploads/cursos/" . $imagen_nombre);
    }

    if($titulo) {
        $sql = "INSERT INTO cursos (titulo, descripcion, docente_id, imagen_portada, fecha_creacion) VALUES (?, ?, ?, ?, NOW())";
        $conexion->prepare($sql)->execute([$titulo, $desc, $docente_id, $imagen_nombre]);
        header("Location: mis_cursos.php");
        exit;
    }
}
require_once '../../includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-header bg-success text-white py-3">
                    <h4 class="mb-0 fw-bold">Publicar Nuevo Curso</h4>
                </div>
                <div class="card-body p-5">
                    <form method="post" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Título</label>
                            <input type="text" name="titulo" class="form-control" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="5"></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Imagen</label>
                            <input type="file" name="imagen" class="form-control" accept="image/*">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">Publicar Curso</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>