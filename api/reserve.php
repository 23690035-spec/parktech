<?php
header('Content-Type: application/json');
include "conexion.php";

if (!isset($_POST["spot_id"]) || !isset($_POST["user_id"]) || !isset($_POST["plate"])) {
    echo json_encode(["success" => false, "error" => "Datos incompletos"]);
    exit();
}

$spotId   = intval($_POST["spot_id"]);
$userId   = intval($_POST["user_id"]);
$plate    = strtoupper(trim($_POST["plate"]));
// ✅ WhatsApp es opcional
$whatsapp = isset($_POST["whatsapp"]) ? trim($_POST["whatsapp"]) : null;

try {
    $conexion->begin_transaction();

    // Verificar que el spot existe y está disponible
    $checkSql = $conexion->prepare("SELECT status FROM spots WHERE id = ? FOR UPDATE");
    $checkSql->bind_param("i", $spotId);
    $checkSql->execute();
    $result = $checkSql->get_result();

    if ($result->num_rows == 0) throw new Exception("El lugar no existe");

    $spot = $result->fetch_assoc();
    if ($spot['status'] !== 'disponible') throw new Exception("El lugar ya está reservado u ocupado");
    $checkSql->close();

    // Verificar que el usuario no tenga otra reserva activa
    $checkUserSql = $conexion->prepare(
        "SELECT id FROM spots WHERE user_id = ? AND status = 'reservado'"
    );
    $checkUserSql->bind_param("i", $userId);
    $checkUserSql->execute();
    if ($checkUserSql->get_result()->num_rows > 0) {
        throw new Exception("Ya tienes una reserva activa");
    }
    $checkUserSql->close();

    // Actualizar spot a reservado
    $updateSpot = $conexion->prepare(
        "UPDATE spots SET status = 'reservado', user_id = ? WHERE id = ?"
    );
    $updateSpot->bind_param("ii", $userId, $spotId);
    if (!$updateSpot->execute()) throw new Exception("Error al actualizar el spot");
    $updateSpot->close();

    // ✅ Insertar reserva con placa y whatsapp
    $insertReservation = $conexion->prepare(
        "INSERT INTO reservations (user_id, spot_id, plate, whatsapp, created_at) VALUES (?, ?, ?, ?, NOW())"
    );
    $insertReservation->bind_param("iiss", $userId, $spotId, $plate, $whatsapp);
    if (!$insertReservation->execute()) throw new Exception("Error al crear la reserva");
    $insertReservation->close();

    $conexion->commit();
    echo json_encode(["success" => true, "message" => "Reserva creada exitosamente"]);

} catch (Exception $e) {
    $conexion->rollback();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}

$conexion->close();
