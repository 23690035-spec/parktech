<?php
// api/conexion.php

define('DB_HOST', 'db');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'parking');

$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conexion->connect_error) {
    header('Content-Type: application/json');
    die(json_encode([
        "success" => false,
        "error"   => "No se pudo conectar a la base de datos. Verifica Docker."
    ]));
}

$conexion->set_charset("utf8mb4");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
