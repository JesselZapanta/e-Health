<?php
require_once "../config/database.php";
session_start();

/*
|--------------------------------------------------------------------------
| SECURITY CHECK
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| INPUTS (can be from form or manual trigger)
|--------------------------------------------------------------------------
*/
$doctor_id = $_POST['doctor_id'] ?? 1;
$date      = $_POST['date'] ?? date('Y-m-d');
$start     = $_POST['start_time'] ?? '08:00';
$end       = $_POST['end_time'] ?? '16:00';
$interval  = 30; // minutes

/*
|--------------------------------------------------------------------------
| PREVENT DUPLICATE GENERATION
|--------------------------------------------------------------------------
*/
$check = mysqli_query($conn, "
    SELECT COUNT(*) as total
    FROM doctor_availability
    WHERE doctor_id = $doctor_id
    AND available_date = '$date'
");

$exists = mysqli_fetch_assoc($check);

if ($exists['total'] > 0) {
    die("Slots already exist for this doctor and date.");
}

/*
|--------------------------------------------------------------------------
| SLOT GENERATION
|--------------------------------------------------------------------------
*/
$startTime = strtotime("$date $start");
$endTime   = strtotime("$date $end");

while ($startTime < $endTime) {

    $slotStart = date("H:i:s", $startTime);
    $slotEnd   = date("H:i:s", strtotime("+$interval minutes", $startTime));

    mysqli_query($conn, "
        INSERT INTO doctor_availability
        (doctor_id, available_date, start_time, end_time, status)
        VALUES
        ($doctor_id, '$date', '$slotStart', '$slotEnd', 'available')
    ");

    $startTime = strtotime("+$interval minutes", $startTime);
}

header("Location: ../admin/doctor_availability.php?success=1");
exit();
