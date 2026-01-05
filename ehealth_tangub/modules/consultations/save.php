<?php
require_once "../../config/database.php";

if ($_SESSION['role'] !== 'doctor') {
    header("Location: ../../auth/login.php");
    exit();
}

$appointment_id = $_POST['appointment_id'];
$symptoms = $_POST['symptoms'];
$diagnosis = $_POST['diagnosis'];
$prescription = $_POST['prescription'];
$notes = $_POST['notes'];

mysqli_query($conn, "
    INSERT INTO consultations (appointment_id, symptoms, diagnosis, prescription, notes)
    VALUES ($appointment_id, '$symptoms', '$diagnosis', '$prescription', '$notes')
");

mysqli_query($conn, "
    UPDATE appointments SET status='Completed'
    WHERE appointment_id = $appointment_id
");

header("Location: ../../doctor/dashboard.php");
exit();
