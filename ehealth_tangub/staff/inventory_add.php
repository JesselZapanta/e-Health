<?php
require_once "../config/database.php";
session_start();

if ($_SESSION['role'] !== 'staff') exit();

$item = mysqli_real_escape_string($conn, $_POST['item_name']);
$desc = mysqli_real_escape_string($conn, $_POST['description']);
$qty  = (int)$_POST['quantity'];
$min  = (int)$_POST['minimum_stock'];

mysqli_query(
    $conn,
    "INSERT INTO inventory (item_name, description, quantity, minimum_stock)
     VALUES ('$item', '$desc', $qty, $min)"
);

header("Location: inventory.php");
exit();
