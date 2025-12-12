<?php
include 'bd.php';
session_start();

if(isset($_SESSION['user_id'])){
    header('Location: dashboard.php');
    exit;
}

$error = null;

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $Email = trim($_POST['Email'] ?? '');
    $Password = $_POST['Password'] ?? '';
    
    if(empty($Email) || empty($Password)){
        $error = 'Ingrese su correo y contraseña.';
    } else {
        $stmt = $conexion->prepare('SELECT ID, Nick, Email, Password, Estado, Verificado, IdRol FROM usuario WHERE Email = :Email LIMIT 1');
        $stmt->execute([':Email'=>$Email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$user){
            $error = 'Credenciales inválidas.';
        } elseif($user['Estado'] != 1){
            $error = 'Tu usuario está inactivo. Contacta al administrador.';
        } elseif(isset($user['Verificado']) && $user['Verificado'] != 1){
            $error = 'Tu correo no está verificado. Revisa tu bandeja de entrada.';
        } else {
            $stored = $user['Password'];
            $ok = password_verify($Password, $stored) || 
                  (strlen($stored) === 64 && ctype_xdigit($stored) && hash('sha256', $Password) === strtolower($stored)) || 
                  ($Password === $stored);
            
            if($ok){
                $_SESSION['user_id'] = $user['ID'];
                $_SESSION['nick'] = $user['Nick'];
                $_SESSION['email'] = $user['Email'];
                $_SESSION['rol'] = $user['IdRol'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Credenciales inválidas.';
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
  <style>body{background:#f6f8fa;}</style>
</head>
<body>
<div class="container mt-5">
  <?php if($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= $error ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow-sm">
        <div class="card-header">Iniciar sesión (prueba GITHUB) otra vez</div>
        <div class="card-body">
          <form method="post">
            <div class="mb-3">
              <label class="form-label">Correo electrónico</label>
              <input type="email" name="Email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Contraseña</label>
              <input type="password" name="Password" class="form-control" required>
            </div>
            <button class="btn btn-primary" type="submit">Entrar</button>
            <a class="btn btn-link" href="registro.php">Registrarse</a>
            <a class="btn btn-link" href="olvido.php">¿Olvidaste tu contraseña?</a>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>