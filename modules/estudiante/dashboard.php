<?php
require_once '../../config/bd.php';
require_once '../../includes/security.php';
verificarRol(3); // Solo Estudiantes
require_once '../../includes/header.php';

// Validar datos de sesión
$nombre = $_SESSION['nombre'] ?? 'Estudiante';
$plan = $_SESSION['plan_nombre'] ?? 'Básico';
?>

<div class="container mt-4">
    <div class="alert alert-primary border-0 shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-center">
            <div class="fs-1 me-3">👋</div>
            <div>
                <h4 class="alert-heading fw-bold mb-1">¡Hola, <?php echo htmlspecialchars($nombre); ?>!</h4>
                <p class="mb-0">Bienvenido a tu espacio de aprendizaje. Estás disfrutando del <strong>Plan <?php echo htmlspecialchars($plan); ?></strong>.</p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-3 text-primary bg-primary bg-opacity-10 rounded-circle d-inline-flex p-4">
                        <i class="bi bi-laptop display-4"></i>
                    </div>
                    <h3 class="card-title fw-bold mt-3">Mis Cursos</h3>
                    <p class="card-text text-muted">Accede a tus clases y continúa aprendiendo.</p>
                    <a href="mis_compras.php" class="btn btn-primary btn-lg px-4 rounded-pill stretched-link">
                        <i class="bi bi-play-circle"></i> Ir al Aula Virtual
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="mb-3 text-success bg-success bg-opacity-10 rounded-circle d-inline-flex p-4">
                        <i class="bi bi-book display-4"></i>
                    </div>
                    <h3 class="card-title fw-bold mt-3">Biblioteca</h3>
                    <p class="card-text text-muted">Descarga libros y material de apoyo en PDF.</p>
                    <a href="mis_compras.php" class="btn btn-success btn-lg px-4 rounded-pill stretched-link">
                        <i class="bi bi-download"></i> Ver mis Libros
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-12">
            <div class="card bg-dark text-white border-0 shadow-sm">
                <div class="card-body p-4 d-md-flex justify-content-between align-items-center">
                    <div class="mb-3 mb-md-0">
                        <h4 class="fw-bold mb-1"><i class="bi bi-stars text-warning"></i> ¿Buscas aprender algo nuevo?</h4>
                        <p class="mb-0 text-white-50">Explora nuestro catálogo completo de cursos y recursos.</p>
                    </div>
                    <a href="catalogo.php" class="btn btn-light fw-bold px-4 py-2 rounded-pill">
                        Explorar Catálogo <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>