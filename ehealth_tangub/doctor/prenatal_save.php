<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: prenatal.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];
$patient_id = (int) $_POST['patient_id'];
$visit_date = $_POST['visit_date'];
$weight = $_POST['weight'];
$blood_pressure = $_POST['blood_pressure'];
$notes = $_POST['notes'];

/* ================================
   SECURITY CHECK
================================ */
$check = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT 1
         FROM appointments
         WHERE doctor_id = $doctor_id
         AND patient_id = $patient_id
         AND status = 'Approved'
         LIMIT 1"
    )
);

if (!$check) {
    die("Unauthorized action.");
}

/* ================================
   INSERT PRENATAL RECORD
================================ */
mysqli_query(
    $conn,
    "INSERT INTO prenatal_records
     (patient_id, visit_date, weight, blood_pressure, notes)
     VALUES (
        $patient_id,
        '$visit_date',
        '$weight',
        '$blood_pressure',
        '$notes'
     )"
);

/* ================================
   REDIRECT
================================ */
header("Location: prenatal.php");
exit();
