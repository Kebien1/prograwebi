<?php
session_start();
require_once '../../config/bd.php';

// Capturamos el plan seleccionado desde la URL (si viene desde la Home)
$plan_preseleccionado = $_GET['plan'] ?? 1;

// Obtenemos los planes de la BD para mostrarlos en el select
$planes = $conexion->query("SELECT * FROM planes ORDER BY precio ASC")->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear Cuenta - EduPlatform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow border-0 p-4" style="width: 100%; max-width: 450px;">
        <div class="text-center mb-4">
            <h3 class="text-primary fw-bold mb-1">Crear Cuenta</h3>
            <p class="text-muted small">Ingresa tus datos para continuar al pago</p>
        </div>

        <form action="pasarela_plan.php" method="post">
            
            <div class="mb-3">
                <label class="form-label fw-bold">Nombre Completo</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                    <input type="text" name="nombre" class="form-control" placeholder="Ej: María Perez" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Correo Electrónico</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="correo@ejemplo.com" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="******" required minlength="6">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Selecciona tu Plan</label>
                <select name="plan_id" class="form-select bg-light border-primary">
                    <?php foreach ($planes as $p): ?>
                        <option value="<?php echo $p['id']; ?>" <?php if($plan_preseleccionado == $p['id']) echo 'selected'; ?>>
                            <?php echo $p['nombre']; ?> - $<?php echo number_format($p['precio'], 0); ?>/mes
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text small">Podrás revisar el total en el siguiente paso.</div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                Continuar al Pago <i class="bi bi-arrow-right"></i>
            </button>
        </form>

        <div class="text-center mt-4 pt-3 border-top">
            <p class="small text-muted mb-0">¿Ya tienes cuenta?</p>
            <a href="login.php" class="text-decoration-none fw-bold">Iniciar Sesión</a>
        </div>
    </div>
</body>
</html>