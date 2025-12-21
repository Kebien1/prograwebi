<?php
// modules/admin/cursos_crear.php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(1); // Solo admin
require_once '../../includes/header.php';

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $desc = trim($_POST['descripcion']);
    $duracion = trim($_POST['duracion']);
    $nivel = $_POST['nivel'];
    $precio = $_POST['precio']; // <--- NUEVO CAMPO
    $admin_id = $_SESSION['usuario_id'];

    // Manejo de Imagen
    $imagen_nombre = "";
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $directorio = "../../uploads/cursos/";
        if (!file_exists($directorio)) mkdir($directorio, 0777, true);
        
        $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $imagen_nombre = uniqid() . "." . $extension;
        move_uploaded_file($_FILES['imagen']['tmp_name'], $directorio . $imagen_nombre);
    }

    if ($titulo) {
        // Insertamos el PRECIO también
        $sql = "INSERT INTO cursos (titulo, descripcion, duracion, nivel, precio, docente_id, imagen_portada, fecha_creacion) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conexion->prepare($sql);
        
        if ($stmt->execute([$titulo, $desc, $duracion, $nivel, $precio, $admin_id, $imagen_nombre])) {
            echo "<script>alert('Curso creado con éxito'); window.location='cursos_lista.php';</script>";
        } else {
            $mensaje = "<div class='alert alert-danger'>Error al crear el curso.</div>";
        }
    }
}
?>

<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Crear Nuevo Curso</h5>
        </div>
        <div class="card-body">
            <?php echo $mensaje; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-bold">Título del Curso</label>
                    <input type="text" name="titulo" class="form-control" required>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Precio ($)</label>
                        <input type="number" step="0.01" name="precio" class="form-control" placeholder="0.00" required>
                        <div class="form-text">Pon 0 para que sea gratis.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Duración</label>
                        <input type="text" name="duracion" class="form-control" placeholder="Ej: 10 Horas">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Nivel</label>
                        <select name="nivel" class="form-select">
                            <option value="Básico">Básico</option>
                            <option value="Intermedio">Intermedio</option>
                            <option value="Avanzado">Avanzado</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="4"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Imagen de Portada</label>
                    <input type="file" name="imagen" class="form-control" accept="image/*">
                </div>

                <div class="d-flex justify-content-between">
                    <a href="cursos_lista.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-5">Guardar Curso</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer_admin.php'; ?>