<?php
header('Content-Type: application/json');
include "conexion.php";

if (!isset($_POST["spot_id"]) || !isset($_POST["status"])) {
    echo json_encode(["success" => false, "error" => "Datos incompletos"]);
    exit();
}

$spotId   = intval($_POST["spot_id"]);
$status   = trim($_POST["status"]);
$plate    = isset($_POST["plate"])    ? strtoupper(trim($_POST["plate"]))    : null;
$whatsapp = isset($_POST["whatsapp"]) ? trim($_POST["whatsapp"])             : null;
$userId   = isset($_POST["user_id"])  ? intval($_POST["user_id"])            : 0;

$validStatuses = ['disponible', 'reservado', 'ocupado'];
if (!in_array($status, $validStatuses)) {
    echo json_encode(["success" => false, "error" => "Estado inválido"]);
    exit();
}

if ($status === 'reservado' && (!$userId || !$plate)) {
    echo json_encode(["success" => false, "error" => "Se requiere usuario y placa para reservar"]);
    exit();
}

try {
    $conexion->begin_transaction();

    // Limpiar reserva anterior si existe
    $deleteRes = $conexion->prepare("DELETE FROM reservations WHERE spot_id = ?");
    $deleteRes->bind_param("i", $spotId);
    $deleteRes->execute();
    $deleteRes->close();

    if ($status === 'disponible') {
        // Liberar el lugar
        $upd = $conexion->prepare(
            "UPDATE spots SET status = 'disponible', user_id = NULL WHERE id = ?"
        );
        $upd->bind_param("i", $spotId);
        $upd->execute();
        $upd->close();

    } elseif ($status === 'reservado') {
        // Marcar como reservado y crear registro
        $upd = $conexion->prepare(
            "UPDATE spots SET status = 'reservado', user_id = ? WHERE id = ?"
        );
        $upd->bind_param("ii", $userId, $spotId);
        $upd->execute();
        $upd->close();

        $ins = $conexion->prepare(
            "INSERT INTO reservations (user_id, spot_id, plate, whatsapp, created_at) VALUES (?, ?, ?, ?, NOW())"
        );
        $ins->bind_param("iiss", $userId, $spotId, $plate, $whatsapp);
        if (!$ins->execute()) throw new Exception("Error al crear la reserva");
        $ins->close();

    } elseif ($status === 'ocupado') {
        // Marcar como ocupado (entrada directa sin reserva)
        $upd = $conexion->prepare(
            "UPDATE spots SET status = 'ocupado', user_id = NULL WHERE id = ?"
        );
        $upd->bind_param("i", $spotId);
        $upd->execute();
        $upd->close();

        // Guardar placa si se proporcionó (usando admin id=1 como referencia)
        if ($plate) {
            $adminId = 1;
            $ins = $conexion->prepare(
                "INSERT INTO reservations (user_id, spot_id, plate, whatsapp, created_at) VALUES (?, ?, ?, ?, NOW())"
            );
            $ins->bind_param("iiss", $adminId, $spotId, $plate, $whatsapp);
            $ins->execute();
            $ins->close();
        }
    }

    $conexion->commit();
    echo json_encode(["success" => true, "message" => "Lugar actualizado correctamente"]);

} catch (Exception $e) {
    $conexion->rollback();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}

$conexion->close();
