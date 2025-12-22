<?php
// modules/admin/biblioteca.php

// Mostrar errores para depurar si algo falla
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Incluimos tu configuración de PDO
require_once '../../config/bd.php'; 
require_once '../../includes/security.php';
verificarRol(1); // Solo admin
require_once '../../includes/header.php';

$mensaje = "";

// 2. Lógica para subir el archivo
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['archivo_pdf'])) {
    try {
        $titulo = $_POST['titulo'];
        $descripcion = $_POST['descripcion'];
        
        // Procesar el archivo
        if ($_FILES['archivo_pdf']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Error al subir el archivo. Código: " . $_FILES['archivo_pdf']['error']);
        }

        $nombre_archivo = $_FILES['archivo_pdf']['name'];
        $ruta_temporal = $_FILES['archivo_pdf']['tmp_name'];
        
        // Crear carpeta si no existe
        $carpeta_destino = "../../uploads/biblioteca/";
        if (!file_exists($carpeta_destino)) {
            mkdir($carpeta_destino, 0777, true);
        }
        
        // Nombre único
        $nuevo_nombre = uniqid() . "_" . $nombre_archivo;
        $destino_final = $carpeta_destino . $nuevo_nombre;

        if (move_uploaded_file($ruta_temporal, $destino_final)) {
            // INSERTAR USANDO PDO (Aquí estaba el error antes)
            $sql = "INSERT INTO biblioteca (titulo, descripcion, archivo) VALUES (:titulo, :descripcion, :archivo)";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                ':titulo' => $titulo, 
                ':descripcion' => $descripcion, 
                ':archivo' => $nuevo_nombre
            ]);

            $mensaje = "<div class='alert alert-success'>Libro subido correctamente.</div>";
        } else {
            throw new Exception("No se pudo mover el archivo a la carpeta.");
        }
    } catch (Exception $e) {
        $mensaje = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}

// 3. Lógica para eliminar (Opcional pero útil)
if (isset($_GET['borrar'])) {
    $id = $_GET['borrar'];
    // Obtener nombre del archivo para borrarlo de la carpeta también
    $stmt = $conexion->prepare("SELECT archivo FROM biblioteca WHERE id = ?");
    $stmt->execute([$id]);
    $libro = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($libro) {
        $ruta = "../../uploads/biblioteca/" . $libro['archivo'];
        if (file_exists($ruta)) unlink($ruta); // Borrar archivo físico
        
        $conexion->prepare("DELETE FROM biblioteca WHERE id = ?")->execute([$id]);
        $mensaje = "<div class='alert alert-warning'>Libro eliminado.</div>";
    }
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white fw-bold">Subir Recurso</div>
                <div class="card-body">
                    <?php echo $mensaje; ?>
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Título</label>
                            <input type="text" name="titulo" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Archivo (PDF/Doc)</label>
                            <input type="file" name="archivo_pdf" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Guardar</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header fw-bold">Biblioteca Actual</div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Descarga</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // CONSULTA USANDO PDO
                            $stmt = $conexion->query("SELECT * FROM biblioteca ORDER BY id DESC");
                            $libros = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            if (count($libros) > 0) {
                                foreach ($libros as $libro) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($libro['titulo']) . "</td>";
                                    echo "<td><a href='../../uploads/biblioteca/" . $libro['archivo'] . "' target='_blank' class='btn btn-sm btn-info'>Ver</a></td>";
                                    echo "<td><a href='biblioteca.php?borrar=" . $libro['id'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"¿Seguro?\")'>X</a></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3' class='text-center text-muted'>La biblioteca está vacía.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../../includes/footer_admin.php'); ?>