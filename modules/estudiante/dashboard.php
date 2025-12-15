<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(3);
require_once '../../includes/header.php';
?>

<div class="container mt-4">
    <div class="alert alert-primary border-0 shadow-sm mb-4">
        <h4 class="fw-bold mb-1">¡Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?>!</h4>
        <p class="mb-0">Bienvenido a tu espacio de aprendizaje gratuito.</p>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-laptop display-4 text-primary"></i>
                    <h3 class="fw-bold mt-3">Mis Cursos</h3>
                    <a href="mis_compras.php" class="btn btn-primary rounded-pill px-4 mt-2">Ir al Aula</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-book display-4 text-success"></i>
                    <h3 class="fw-bold mt-3">Biblioteca</h3>
                    <a href="mis_compras.php" class="btn btn-success rounded-pill px-4 mt-2">Mis Libros</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>