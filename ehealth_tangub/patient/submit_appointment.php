<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: request_appointment.php");
    exit();
}

$patient_user_id = $_SESSION['user_id'];

/* Get patient_id */
$p = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT patient_id FROM patients WHERE user_id = $patient_user_id")
);

if (!$p) {
    die("Patient record not found.");
}

$patient_id = $p['patient_id'];

/* Sanitize inputs */
$doctor_id = (int) $_POST['doctor_id'];
$date = $_POST['appointment_date'];
$time = $_POST['appointment_time'];

/* Basic validation */
if (!$doctor_id || !$date || !$time) {
    die("All fields are required.");
}

/* Insert appointment as PENDING */
$stmt = mysqli_prepare($conn, "
    INSERT INTO appointments 
    (patient_id, doctor_id, appointment_date, appointment_time, status, created_at)
    VALUES (?, ?, ?, ?, 'pending', NOW())
");

mysqli_stmt_bind_param($stmt, "iiss", 
    $patient_id, 
    $doctor_id, 
    $date, 
    $time
);

if (mysqli_stmt_execute($stmt)) {
    header("Location: appointments.php?success=1");
    exit();
} else {
    die("Failed to submit appointment request.");
}
