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

// Only allow approve or cancel
if (!in_array($action, ['approve', 'cancel'])) {
    header("Location: appointments.php");
    exit();
}

// Set the new status
$newStatus = ($action === 'approve') ? 'Approved' : 'Cancelled';

// Update the database
$stmt = mysqli_prepare($conn, "UPDATE appointments SET status = ? WHERE appointment_id = ?");
mysqli_stmt_bind_param($stmt, "si", $newStatus, $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// Redirect back
header("Location: appointments.php");
exit();
