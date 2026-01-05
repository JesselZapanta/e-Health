<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$id = (int)$_GET['id'];

mysqli_query(
    $conn,
    "UPDATE users
     SET status = IF(status='active','inactive','active')
     WHERE user_id=$id"
);

header("Location: users.php");
exit();
