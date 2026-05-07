<?php
header('Content-Type: application/json');
include "conexion.php";

if (!isset($_POST["email"]) || !isset($_POST["password"])) {
    echo json_encode(["success" => false, "error" => "Datos incompletos"]);
    exit();
}

$email    = $_POST["email"];
$password = $_POST["password"];

try {
    // ✅ Trae whatsapp e is_admin además de los campos base
    $sql = $conexion->prepare(
        "SELECT id, name, email, password, whatsapp, is_admin FROM users WHERE email = ?"
    );
    if (!$sql) throw new Exception("Error al preparar la consulta");

    $sql->bind_param("s", $email);
    $sql->execute();
    $result = $sql->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if ($password === $user['password']) {
            unset($user['password']);

            // Castear para que JS los reciba con el tipo correcto
            $user['id']       = (int)$user['id'];
            $user['is_admin'] = (bool)$user['is_admin'];

            echo json_encode(["success" => true, "user" => $user]);
        } else {
            echo json_encode(["success" => false, "error" => "Contraseña incorrecta"]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Usuario no encontrado"]);
    }

    $sql->close();

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Error en el servidor"]);
}

$conexion->close();
