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
$time      = $_POST['time'];
$slots     = intval($_POST['slots']);

/* Set status */
$status = 'available';

/* Insert availability */
$stmt = mysqli_prepare($conn, "
    INSERT INTO doctor_availability 
    (doctor_id, available_date, time, slots, status, created_at)
    VALUES (?, ?, ?, ?, ?, NOW())
");

mysqli_stmt_bind_param($stmt, "issis", $doctor_id, $date, $time, $slots, $status);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

/* Redirect back */
header("Location: availability.php");
exit();
