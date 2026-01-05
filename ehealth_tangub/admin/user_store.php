<?php
require_once "../config/database.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: users.php");
    exit();
}

$first = mysqli_real_escape_string($conn, $_POST['first_name']);
$middle = mysqli_real_escape_string($conn, $_POST['middle_initial']);
$last = mysqli_real_escape_string($conn, $_POST['last_name']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$role = $_POST['role'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$full_name = trim("$first $middle $last");

$check = mysqli_query($conn, "SELECT user_id FROM users WHERE email='$email'");
if (mysqli_num_rows($check) > 0) {
    die("Email already exists.");
}

mysqli_query($conn,
    "INSERT INTO users (full_name,email,password,role,status)
     VALUES ('$full_name','$email','$password','$role','active')"
);

header("Location: users.php");
exit();
