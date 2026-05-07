<?php
header('Content-Type: application/json');
include "conexion.php";

if (!isset($_POST["spot_id"])) {
    echo json_encode(["success" => false, "error" => "Datos incompletos"]);
    exit();
}

$spotId = intval($_POST["spot_id"]);

try {
    $conexion->begin_transaction();

    // Verificar que el spot existe y está reservado (o ocupado, el admin puede liberar ambos)
    $checkSql = $conexion->prepare("SELECT status FROM spots WHERE id = ? FOR UPDATE");
    $checkSql->bind_param("i", $spotId);
    $checkSql->execute();
    $result = $checkSql->get_result();

    if ($result->num_rows == 0) throw new Exception("El lugar no existe");

    $spot = $result->fetch_assoc();
    if ($spot['status'] === 'disponible') throw new Exception("El lugar ya está disponible");
    $checkSql->close();

    // Eliminar la reserva si existe
    $deleteReservation = $conexion->prepare("DELETE FROM reservations WHERE spot_id = ?");
    $deleteReservation->bind_param("i", $spotId);
    if (!$deleteReservation->execute()) throw new Exception("Error al eliminar la reserva");
    $deleteReservation->close();

    // Liberar el spot
    $updateSpot = $conexion->prepare(
        "UPDATE spots SET status = 'disponible', user_id = NULL WHERE id = ?"
    );
    $updateSpot->bind_param("i", $spotId);
    if (!$updateSpot->execute()) throw new Exception("Error al actualizar el spot");
    $updateSpot->close();

    $conexion->commit();
    echo json_encode(["success" => true, "message" => "Reserva cancelada exitosamente"]);

} catch (Exception $e) {
    $conexion->rollback();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}

$conexion->close();
