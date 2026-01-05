<?php
require_once "../config/database.php";

if (!isset($_SESSION)) {
    session_start();
}

if ($_SESSION['role'] !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

$id = (int)$_GET['id'];

mysqli_query(
    $conn,
    "UPDATE appointments 
     SET status = 'rejected'
     WHERE appointment_id = $id"
);

header("Location: appointments.php");
exit();
