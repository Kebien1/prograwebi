<?php 
include("autenticacion.php");
include("bd.php");

$lista_usuarios = $conexion->query("SELECT * FROM usuario")->fetchAll(PDO::FETCH_ASSOC);

include("header.php");
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h3 mb-0">
                        Bienvenido<?= isset($_SESSION['nick']) ? ', ' . htmlspecialchars($_SESSION['nick']) : '' ?>
                    </h1>
                </div>
            </div>
        </div>
    </div>
</div>