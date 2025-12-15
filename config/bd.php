<?php
$servidor = "sql111.infinityfree.com";
$basededatos = "if0_40651214_db_prograwebi";
$usuario = "if0_40651214";
$clave = "q5iIgPxzSQGR";

try {
    $conexion = new PDO("mysql:host=$servidor;dbname=$basededatos;charset=utf8mb4", $usuario, $clave);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(Exception $ex) {
    echo "Error de conexión: " . $ex->getMessage();
    exit;
}
?>