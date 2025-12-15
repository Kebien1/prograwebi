<?php

define('BASE_URL', 'https://prograweb1.infinityfreeapp.com/');
// ... resto del código de conexión ...
$servidor = "sql111.infinityfree.com";
$basededatos = "if0_40651214_db_prograwebi"; 
$usuario = "if0_40651214";
$clave = "q5iIgPxzSQGR";

try {
    $conexion = new PDO("mysql:host=$servidor;dbname=$basededatos;charset=utf8mb4", $usuario, $clave);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    date_default_timezone_set('America/La_Paz');
} catch(PDOException $ex) {
    die("Error de conexión: " . $ex->getMessage());
}
?>