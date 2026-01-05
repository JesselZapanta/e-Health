<?php
require_once "../config/database.php";

$doctor_id = $_POST['doctor_id'] ?? null;

if (!$doctor_id) {
    echo json_encode([]);
    exit;
}

$sql = "
    SELECT DISTINCT available_date
    FROM doctor_availability
    WHERE doctor_id = ?
      AND status = 'available'
      AND available_date >= CURDATE()
    ORDER BY available_date ASC
    LIMIT 3
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$res = $stmt->get_result();

$dates = [];
while ($row = $res->fetch_assoc()) {
    $dates[] = $row['available_date'];
}

echo json_encode($dates);
