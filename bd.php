<?php
$servidor = "sql307.infinityfree.com"; //127.0.0.1
$basededatos = "if0_40651251_prograweb";
$usuario = "if0_40651251";
$clave = "bgeup79mb3q0ye";
try {
    $conexion = new PDO("mysql:host=$servidor;dbname=$basededatos",
    $usuario,$clave);
} catch(Exception $ex) {
    echo $ex->getMessage();
}
?>