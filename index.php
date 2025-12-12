<?php
include 'includes/bd.php';
session_start();

if(isset($_SESSION['user_id'])){
    header('Location: dashboard.php');
    exit;
}

$error = null;

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $Email = trim($_POST['Email'] ?? '');
    $Password = $_POST['Password'] ?? '';

    if($Email === '' || $Password === ''){
        $error = 'Ingrese su correo y contraseña.';
    } else {
        $stmt = $conexion->prepare('SELECT ID, Nick, Email, Password, Estado, Verificado, IdRol FROM usuario WHERE Email = :Email LIMIT 1');
        $stmt->execute([':Email'=>$Email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$user){
            $error = 'Credenciales inválidas.';
        } else if((int)$user['Estado'] !== 1){
            $error = 'Usuario inactivo.';
        } else {
            // Verificación simple para ejemplo, idealmente usar password_verify
            $ok = false;
            if($Password === $user['Password']){
                $ok = true;
            } elseif (password_verify($Password, $user['Password'])){
                 $ok = true;
            }

            if($ok){
                $_SESSION['user_id'] = $user['ID'];
                $_SESSION['nick'] = $user['Nick'];
                $_SESSION['rol'] = $user['IdRol'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Contraseña incorrecta.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Iniciar sesión</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow-sm">
        <div class="card-header bg-primary text-white text-center">
            <h4>Iniciar sesión</h4>
        </div>
        <div class="card-body p-4">
          <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
          <?php endif; ?>
          <form method="post">
            <div class="mb-3">
              <label class="form-label">Correo electrónico</label>
              <input type="email" name="Email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Contraseña</label>
              <input type="password" name="Password" class="form-control" required>
            </div>
            <div class="d-grid">
                <button class="btn btn-primary" type="submit">Entrar</button>
            </div>
          </form>
          <div class="mt-3 text-center">
            <a href="registro.php">Registrarse</a> | 
            <a href="olvido.php">Olvidé mi contraseña</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>