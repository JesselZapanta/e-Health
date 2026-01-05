<?php
require_once "../../config/database.php";

$inventory_id = $_POST['inventory_id'];
$consultation_id = $_POST['consultation_id'];
$qty = $_POST['quantity'];

mysqli_query($conn,
    "INSERT INTO inventory_logs (inventory_id, consultation_id, action, quantity)
     VALUES ($inventory_id, $consultation_id, 'OUT', $qty)"
);

mysqli_query($conn,
    "UPDATE inventory SET quantity = quantity - $qty
     WHERE inventory_id = $inventory_id"
);

header("Location: ../../doctor/dashboard.php");
exit();
