<?php
// modules/admin/cursos_crear.php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(1); // Solo Administrador

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $desc = trim($_POST['descripcion']);
    $duracion = trim($_POST['duracion']);
    $nivel = trim($_POST['nivel']);
    
    // Ahora usamos el ID del Administrador logueado como docente_id
    $admin_id = $_SESSION['usuario_id'];
    $imagen_nombre = null;

    // Lógica para subir la imagen de portada
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $imagen_nombre = "curso_" . time() . "." . $ext;
        
        // Asegúrate de que la carpeta exista
        $ruta_destino = "../../uploads/cursos/" . $imagen_nombre;
        move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino);
    }

    if($titulo) {
        // Insertamos el curso vinculándolo al Administrador actual
        $sql = "INSERT INTO cursos (titulo, descripcion, duracion, nivel, docente_id, imagen_portada, fecha_creacion) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $conexion->prepare($sql)->execute([$titulo, $desc, $duracion, $nivel, $admin_id, $imagen_nombre]);
        
        header("Location: cursos_lista.php");
        exit;
    }
}

require_once '../../includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0 fw-bold"><i class="bi bi-plus-circle"></i> Publicar Nuevo Curso</h4>
                </div>
                <div class="card-body p-5">
                    <form method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Título del Curso</label>
                            <input type="text" name="titulo" class="form-control" placeholder="Ej: Introducción a PHP" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Duración estimada</label>
                                <input type="text" name="duracion" class="form-control" placeholder="Ej: 20 horas">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nivel de dificultad</label>
                                <select name="nivel" class="form-select">
                                    <option value="Principiante">Principiante</option>
                                    <option value="Intermedio">Intermedio</option>
                                    <option value="Avanzado">Avanzado</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="5" placeholder="Escribe aquí los objetivos del curso..."></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Imagen de Portada</label>
                            <input type="file" name="imagen" class="form-control" accept="image/*">
                            <small class="text-muted">Formatos sugeridos: JPG, PNG. Tamaño recomendado: 800x450px.</small>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="cursos_lista.php" class="btn btn-light px-4 border">Cancelar</a>
                            <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm">Publicar Curso</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
