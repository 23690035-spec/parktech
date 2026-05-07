<?php
header('Content-Type: application/json');
include "conexion.php";

try {
    // ✅ Ahora también devuelve whatsapp de la reserva
    $sql = "SELECT s.id, s.status, s.user_id,
                   u.name  AS user_name,
                   r.plate,
                   r.whatsapp,
                   r.created_at AS reserved_at
            FROM spots s
            LEFT JOIN users u        ON s.user_id = u.id
            LEFT JOIN reservations r ON s.id = r.spot_id
            ORDER BY s.id ASC";

    $result = $conexion->query($sql);

    if (!$result) throw new Exception("Error al ejecutar la consulta");

    $spots = [];
    while ($row = $result->fetch_assoc()) {
        $row['id']      = (int)$row['id'];
        $row['user_id'] = $row['user_id'] ? (int)$row['user_id'] : null;
        $spots[] = $row;
    }

    echo json_encode($spots);

} catch (Exception $e) {
    echo json_encode(["error" => "Error al obtener spots", "details" => $e->getMessage()]);
}

$conexion->close();
