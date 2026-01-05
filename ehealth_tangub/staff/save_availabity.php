<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: availability.php");
    exit();
}

$doctor_id = intval($_POST['doctor_id']);
$date      = $_POST['available_date'];
$start     = $_POST['start_time'];
$end       = $_POST['end_time'];
$slots     = intval($_POST['slots']);

/* Basic validation */
if ($start >= $end) {
    die("Invalid time range.");
}

/* Insert availability */
$stmt = mysqli_prepare($conn, "
    INSERT INTO doctor_availability 
    (doctor_id, available_date, start_time, end_time, slots, status, created_at)
    VALUES (?, ?, ?, ?, ?, 'available', NOW())
");

mysqli_stmt_bind_param($stmt, "isssi", $doctor_id, $date, $start, $end, $slots);
mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

/* Redirect back */
header("Location: availability.php");
exit();
