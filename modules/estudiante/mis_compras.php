<?php
// modules/estudiante/mis_compras.php

// 1. ACTIVAR REPORTE DE ERRORES
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. INCLUIR ARCHIVOS DE CONFIGURACIÓN Y SEGURIDAD
require_once '../../config/bd.php';
require_once '../../includes/security.php';

// 3. VERIFICAR QUE EL USUARIO SEA ESTUDIANTE (Rol 3)
verificarRol(3);

// 4. INCLUIR EL ENCABEZADO
require_once '../../includes/header.php';
?>

<div class="container mt-5" style="min-height: 60vh;">
    <h2 class="mb-4 text-center text-md-start">
        <i class="bi bi-bag-check-fill text-success"></i> Mis Cursos Comprados
    </h2>

    <div class="row">
        <?php
        try {
            if (!isset($_SESSION['usuario_id'])) {
                throw new Exception("No se ha identificado el usuario.");
            }

            $estudiante_id = $_SESSION['usuario_id'];

            // 5. CONSULTA CORREGIDA (Usa 'c.foto' en lugar de 'c.imagen')
            // IMPORTANTE: Si en tu base de datos la columna se llama 'img' o 'portada',
            // cambia 'c.foto' por ese nombre justo aquí abajo.
            $sql = "SELECT c.id, c.titulo, c.descripcion, c.foto, co.fecha_compra as fecha_venta 
                    FROM compras co 
                    INNER JOIN cursos c ON co.item_id = c.id 
                    WHERE co.usuario_id = :usuario_id 
                    AND co.tipo_item = 'curso'
                    ORDER BY co.fecha_compra DESC";

            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':usuario_id', $estudiante_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $mis_cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 6. MOSTRAR LOS CURSOS
            if (count($mis_cursos) > 0) {
                foreach ($mis_cursos as $curso) {
                    
                    // --- LÓGICA DE FOTO CORREGIDA ---
                    // Definir imagen por defecto
                    $ruta_imagen = "../../assets/img/no-image.jpg"; 
                    
                    // Verificamos si existe 'foto' y si el archivo real existe
                    $nombre_foto = $curso['foto'] ?? ''; // Usa operador de fusión null para evitar errores

                    if (!empty($nombre_foto) && file_exists("../../uploads/cursos/" . $nombre_foto)) {
                        $ruta_imagen = "../../uploads/cursos/" . $nombre_foto;
                    }
                    // --------------------------------
        ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm hover-scale">
                            <img src="<?php echo htmlspecialchars($ruta_imagen); ?>" class="card-img-top" alt="Portada del curso" style="height: 200px; object-fit: cover;">
                            
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title fw-bold text-dark">
                                    <?php echo htmlspecialchars($curso['titulo']); ?>
                                </h5>
                                <p class="card-text text-muted small flex-grow-1">
                                    <?php 
                                    echo htmlspecialchars(substr($curso['descripcion'] ?? '', 0, 100)) . '...'; 
                                    ?>
                                </p>
                                
                                <div class="mt-3 border-top pt-2">
                                    <small class="text-muted d-block mb-2">
                                        <i class="bi bi-calendar-check"></i> Adquirido: <?php echo date('d/m/Y', strtotime($curso['fecha_venta'])); ?>
                                    </small>
                                    
                                    <a href="ver_curso.php?id=<?php echo $curso['id']; ?>" class="btn btn-primary w-100">
                                        <i class="bi bi-play-circle-fill"></i> Ir al Curso
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
        <?php
                }
            } else {
                echo '
                <div class="col-12 text-center py-5">
                    <h4 class="text-muted">Aún no tienes cursos inscritos.</h4>
                    <a href="catalogo.php" class="btn btn-outline-primary mt-3">Ver Catálogo</a>
                </div>';
            }

        } catch (PDOException $e) {
            echo '<div class="alert alert-danger">Error de base de datos: ' . $e->getMessage() . '</div>';
        } catch (Exception $e) {
            echo '<div class="alert alert-warning">' . $e->getMessage() . '</div>';
        }
        ?>
    </div>
</div>

<?php 
require_once '../../includes/footer_admin.php'; 
?>