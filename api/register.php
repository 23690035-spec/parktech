<?php
header('Content-Type: application/json');
include "conexion.php";

if (!isset($_POST["name"]) || !isset($_POST["email"]) || !isset($_POST["password"])) {
    echo json_encode(["success" => false, "error" => "Datos incompletos"]);
    exit();
}

$name      = trim($_POST["name"]);
$email     = trim($_POST["email"]);
$password  = $_POST["password"];
// ✅ WhatsApp es opcional
$whatsapp  = isset($_POST["whatsapp"]) ? trim($_POST["whatsapp"]) : null;

if (empty($name) || empty($email) || empty($password)) {
    echo json_encode(["success" => false, "error" => "Todos los campos son obligatorios"]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "error" => "Email inválido"]);
    exit();
}

try {
    // Verificar si el email ya existe
    $checkSql = $conexion->prepare("SELECT id FROM users WHERE email = ?");
    $checkSql->bind_param("s", $email);
    $checkSql->execute();
    $checkResult = $checkSql->get_result();

    if ($checkResult->num_rows > 0) {
        echo json_encode(["success" => false, "error" => "El correo ya está registrado"]);
        exit();
    }
    $checkSql->close();

    // ✅ Insertamos también whatsapp (is_admin = 0 por defecto en la BD)
    $sql = $conexion->prepare(
        "INSERT INTO users (name, email, password, whatsapp) VALUES (?, ?, ?, ?)"
    );
    if (!$sql) throw new Exception("Error al preparar la consulta");

    $sql->bind_param("ssss", $name, $email, $password, $whatsapp);

    if ($sql->execute()) {
        echo json_encode(["success" => true, "message" => "Usuario registrado exitosamente"]);
    } else {
        throw new Exception("Error al ejecutar la consulta");
    }

    $sql->close();

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Error en el servidor: " . $e->getMessage()]);
}

$conexion->close();
