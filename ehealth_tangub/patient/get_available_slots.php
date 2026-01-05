<?php
require_once "../config/database.php";

$doctor_id = $_POST['doctor_id'] ?? null;
$date      = $_POST['date'] ?? null;

if (!$doctor_id || !$date) {
    echo json_encode([]);
    exit;
}

/*
  1. Get doctor's availability for the selected date
*/
$sql = "
    SELECT start_time, end_time
    FROM doctor_availability
    WHERE doctor_id = ?
      AND available_date = ?
      AND status = 'available'
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $doctor_id, $date);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([]);
    exit;
}

$availability = $result->fetch_assoc();
$start = strtotime($availability['start_time']);
$end   = strtotime($availability['end_time']);

$slotInterval = 30 * 60; // 30 minutes
$slots = [];

/*
  2. Generate raw slots
*/
while ($start < $end) {
    $slots[] = date("H:i:s", $start);
    $start += $slotInterval;
}

/*
  3. Remove slots already booked
*/
$booked = [];
$bookedSql = "
    SELECT appointment_time
    FROM appointments
    WHERE doctor_id = ?
      AND appointment_date = ?
      AND status IN ('pending','approved')
";

$stmt2 = $conn->prepare($bookedSql);
$stmt2->bind_param("is", $doctor_id, $date);
$stmt2->execute();
$res2 = $stmt2->get_result();

while ($row = $res2->fetch_assoc()) {
    $booked[] = $row['appointment_time'];
}

if (count($availableSlots) === 0) {

    // Find alternate dates
    $alt = $conn->prepare("
        SELECT DISTINCT available_date
        FROM doctor_availability
        WHERE doctor_id = ?
          AND status = 'available'
          AND available_date > ?
        ORDER BY available_date ASC
        LIMIT 3
    ");
    $alt->bind_param("is", $doctor_id, $date);
    $alt->execute();
    $altRes = $alt->get_result();

    $suggestions = [];
    while ($r = $altRes->fetch_assoc()) {
        $suggestions[] = $r['available_date'];
    }

    echo json_encode([
        "slots" => [],
        "suggested_dates" => $suggestions
    ]);
    exit;
}

echo json_encode([
    "slots" => $availableSlots,
    "suggested_dates" => []
]);
