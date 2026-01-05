<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: appointments.php");
    exit();
}

$id = intval($_POST['id']);
$action = $_POST['action'];

if (!in_array($action, ['approve', 'deny'])) {
    header("Location: appointments.php");
    exit();
}

$newStatus = ($action === 'approve') ? 'approved' : 'denied';

$stmt = mysqli_prepare($conn, "
    UPDATE appointments 
    SET status = ?
    WHERE appointment_id = ?
");

mysqli_stmt_bind_param($stmt, "si", $newStatus, $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header("Location: appointments.php");
exit();
