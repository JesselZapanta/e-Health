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
    "DELETE FROM doctor_availability 
     WHERE availability_id = $id"
);

header("Location: availability.php");
exit();
