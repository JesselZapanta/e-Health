<?php
require_once "../config/database.php";

$id     = (int)$_POST['user_id'];
$name   = mysqli_real_escape_string($conn, $_POST['full_name']);
$role   = $_POST['role'];
$status = $_POST['status'];

mysqli_query(
    $conn,
    "UPDATE users
     SET full_name='$name', role='$role', status='$status'
     WHERE user_id=$id"
);

header("Location: users.php");
exit();
