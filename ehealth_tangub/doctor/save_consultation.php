<?php
require_once "../config/database.php";

if ($_SESSION['role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];

mysqli_query(
    $conn,
    "INSERT INTO consultations
     (appointment_id, doctor_id, patient_id, symptoms, diagnosis, prescription, notes)
     VALUES (
        {$_POST['appointment_id']},
        $doctor_id,
        {$_POST['patient_id']},
        '{$_POST['symptoms']}',
        '{$_POST['diagnosis']}',
        '{$_POST['prescription']}',
        '{$_POST['notes']}'
     )"
);

mysqli_query(
    $conn,
    "UPDATE appointments
     SET status='Completed'
     WHERE appointment_id={$_POST['appointment_id']}"
);

header("Location: consultations.php");
exit();
