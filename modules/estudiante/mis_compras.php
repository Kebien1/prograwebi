<?php
// modules/estudiante/mis_compras.php

// 1. ACTIVAR REPORTE DE ERRORES (Vital para detectar fallos en lugar de ver pantalla blanca)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. INCLUIR ARCHIVOS DE CONFIGURACIÓN Y SEGURIDAD
require_once '../../config/bd.php';
require_once '../../includes/security.php';

// 3. VERIFICAR QUE EL USUARIO SEA ESTUDIANTE (Rol 3)
verificarRol(3);

// 4. INCLUIR EL ENCABEZADO (HEADER)
require_once '../../includes/header.php';
?>

<div class="container mt-5" style="min-height: 60vh;">
    <h2 class="mb-4 text-center text-md-start">
        <i class="bi bi-bag-check-fill text-success"></i> Mis Cursos Comprados
    </h2>

    <div class="row">
        <?php
        try {
            // Verificar si hay sesión activa (doble seguridad)
            if (!isset($_SESSION['usuario_id'])) {
                throw new Exception("No se ha identificado el usuario.");
            }

            $estudiante_id = $_SESSION['usuario_id'];

            // 5. CONSULTA A LA BASE DE DATOS
            // Busca las ventas con estado 'completado' y trae los datos del curso
            $sql = "SELECT c.id, c.titulo, c.descripcion, c.imagen, v.fecha_venta 
                    FROM ventas v 
                    INNER JOIN cursos c ON v.curso_id = c.id 
                    WHERE v.usuario_id = :usuario_id 
                    AND v.estado = 'completado' 
                    ORDER BY v.fecha_venta DESC";

            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':usuario_id', $estudiante_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $mis_cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 6. MOSTRAR LOS CURSOS
            if (count($mis_cursos) > 0) {
                foreach ($mis_cursos as $curso) {
                    // Validar imagen
                    $ruta_imagen = "../../assets/img/no-image.jpg"; // Imagen por defecto
                    if (!empty($curso['imagen']) && file_exists("../../uploads/cursos/" . $curso['imagen'])) {
                        $ruta_imagen = "../../uploads/cursos/" . $curso['imagen'];
                    } elseif (!empty($curso['imagen'])) {
                        // Si la imagen está en BD pero no en carpeta, intentar ruta directa
                        $ruta_imagen = "../../uploads/cursos/" . $curso['imagen'];
                    }
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
                                    // Limitar descripción a 100 caracteres
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
                // MENSAJE SI NO HAY CURSOS
                echo '
                <div class="col-12 text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-cart-x display-1 text-muted opacity-50"></i>
                    </div>
                    <h4 class="text-muted">Aún no tienes cursos inscritos.</h4>
                    <p class="text-secondary">¡Es un buen momento para aprender algo nuevo!</p>
                    <a href="catalogo.php" class="btn btn-outline-primary mt-3">
                        <i class="bi bi-search"></i> Ver Catálogo de Cursos
                    </a>
                </div>';
            }

        } catch (PDOException $e) {
            // Error de Base de Datos
            echo '<div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i> Error de base de datos: ' . $e->getMessage() . '
                  </div>';
        } catch (Exception $e) {
            // Error General
            echo '<div class="alert alert-warning" role="alert">
                    <i class="bi bi-exclamation-circle"></i> ' . $e->getMessage() . '
                  </div>';
        }
        ?>
    </div>
</div>

<?php 
require_once '../../includes/footer_admin.php'; 
?>