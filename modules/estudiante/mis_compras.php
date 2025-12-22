<?php
// modules/estudiante/mis_compras.php

session_start();
// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Incluir la conexión a la base de datos
require_once '../../config/bd.php'; // Asegúrate que esta ruta sea correcta
require_once '../../includes/header.php';

$id_usuario = $_SESSION['id_usuario'];

try {
    // ---------------------------------------------------------
    // CORRECCIÓN AQUÍ:
    // Usamos 'inscripciones' en lugar de 'ventas'.
    // Hacemos JOIN con 'cursos' para obtener el nombre e imagen.
    // ---------------------------------------------------------
    $sql = "SELECT 
                i.id AS id_inscripcion,
                i.fecha_inscripcion,
                c.id AS id_curso,
                c.titulo,
                c.imagen,
                c.precio,
                c.descripcion
            FROM inscripciones i 
            INNER JOIN cursos c ON i.id_curso = c.id 
            WHERE i.id_usuario = :id_usuario 
            ORDER BY i.fecha_inscripcion DESC";

    $stmt = $bd->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $mis_cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Si la tabla 'inscripciones' no existe, probamos con 'compras' para evitar el error fatal
    // Esto es solo un manejo de error, lo ideal es tener el nombre correcto arriba.
    echo "<div class='container mt-5 alert alert-danger'>";
    echo "Error de base de datos: " . $e->getMessage() . "<br>";
    echo "<b>Posible solución:</b> Verifica si tu tabla se llama 'inscripciones', 'compras' o 'pedidos' y actualiza la línea 'FROM' en este archivo.";
    echo "</div>";
    exit();
}
?>

<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-12 mb-4">
            <h2 class="text-primary"><i class="fas fa-graduation-cap"></i> Mis Cursos Adquiridos</h2>
            <hr>
        </div>
    </div>

    <?php if (count($mis_cursos) > 0): ?>
        <div class="row">
            <?php foreach ($mis_cursos as $curso): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <?php 
                            // Manejo de imagen por si no existe
                            $imagen = !empty($curso['imagen']) ? "../../uploads/cursos/" . htmlspecialchars($curso['imagen']) : "../../assets/img/no-image.png";
                        ?>
                        <img src="<?php echo $imagen; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($curso['titulo']); ?>" style="height: 200px; object-fit: cover;">
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($curso['titulo']); ?></h5>
                            <p class="card-text text-muted small">
                                Comprado el: <?php echo date('d/m/Y', strtotime($curso['fecha_inscripcion'])); ?>
                            </p>
                            
                            <div class="mt-auto">
                                <a href="ver_curso.php?id=<?php echo $curso['id_curso']; ?>" class="btn btn-primary w-100">
                                    <i class="fas fa-play-circle"></i> Ir al Curso
                                </a>
                                <a href="ver_factura.php?id=<?php echo $curso['id_inscripcion']; ?>" class="btn btn-outline-secondary w-100 mt-2 btn-sm">
                                    <i class="fas fa-file-invoice"></i> Ver Recibo
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">
            <h4><i class="fas fa-info-circle"></i> Aún no te has inscrito en ningún curso.</h4>
            <p>Visita nuestro catálogo para empezar a aprender.</p>
            <a href="catalogo.php" class="btn btn-success mt-2">Ir al Catálogo</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>