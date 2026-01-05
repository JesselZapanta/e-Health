<?php
require_once "../config/database.php";
session_start();

if ($_SESSION['role'] !== 'staff') exit();

$id = (int)$_GET['id'];

$item = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT * FROM inventory WHERE inventory_id = $id")
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action']; // IN or OUT
    $qty = (int)$_POST['quantity'];

    if ($action === 'IN') {
        mysqli_query($conn, "UPDATE inventory SET quantity = quantity + $qty WHERE inventory_id = $id");
    } else {
        mysqli_query($conn, "UPDATE inventory SET quantity = quantity - $qty WHERE inventory_id = $id");
    }

    mysqli_query(
        $conn,
        "INSERT INTO inventory_logs (inventory_id, action, quantity)
         VALUES ($id, '$action', $qty)"
    );

    header("Location: inventory.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Stock In / Out</title>
    <link rel="stylesheet" href="/ehealth_tangub/assets/css/ui.css">
</head>
<body>

<div class="app-container">
<?php require_once "../layouts/sidebar.php"; ?>
<main class="main-content">
<?php require_once "../layouts/topbar.php"; ?>

<h3 class="page-title">Stock In / Out — <?= htmlspecialchars($item['item_name']) ?></h3>

<div class="card" style="max-width:400px;">
<form method="POST">
    <label>Action</label>
    <select name="action">
        <option value="IN">Stock In</option>
        <option value="OUT">Stock Out</option>
    </select>

    <label>Quantity</label>
    <input type="number" name="quantity" required>

    <button class="btn-primary" style="margin-top:10px;">Submit</button>
</form>
</div>

</main>
</div>

</body>
</html>
