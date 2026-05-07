<?php
header('Content-Type: application/json');
include "conexion.php";

try {
    // Todos los usuarios con su reserva activa si tienen
    $sql = "SELECT u.id, u.name, u.email, u.whatsapp, u.is_admin, u.created_at,
                   s.id     AS spot_id,
                   s.status AS spot_status,
                   r.plate
            FROM users u
            LEFT JOIN spots s        ON s.user_id = u.id AND s.status IN ('reservado','ocupado')
            LEFT JOIN reservations r ON r.spot_id  = s.id
            ORDER BY u.is_admin DESC, u.name ASC";

    $result = $conexion->query($sql);
    if (!$result) throw new Exception("Error al ejecutar la consulta");

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $row['id']       = (int)$row['id'];
        $row['is_admin'] = (bool)$row['is_admin'];
        $row['spot_id']  = $row['spot_id'] ? (int)$row['spot_id'] : null;
        $users[] = $row;
    }

    echo json_encode($users);

} catch (Exception $e) {
    echo json_encode(["error" => "Error al obtener usuarios", "details" => $e->getMessage()]);
}

$conexion->close();
