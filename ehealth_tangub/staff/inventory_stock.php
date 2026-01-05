<?php
require_once "../config/database.php";


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

// Get inventory ID from GET
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$item = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT * FROM inventory WHERE inventory_id = $id")
);

if (!$item) {
    die("Item not found.");
}

// Handle Stock In / Out POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'], $_POST['quantity'])) {
        $action = $_POST['action'];
        $qty = (int)$_POST['quantity'];

        if ($action === 'IN') {
            mysqli_query($conn, "UPDATE inventory SET quantity = quantity + $qty WHERE inventory_id = $id");
        } else {
            mysqli_query($conn, "UPDATE inventory SET quantity = quantity - $qty WHERE inventory_id = $id");
        }

        mysqli_query(
            $conn,
            "INSERT INTO inventory_logs (inventory_id, action, quantity, log_date)
             VALUES ($id, '$action', $qty, NOW())"
        );

        header("Location: inventory.php?view=$id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Stock In / Out | <?= htmlspecialchars($item['item_name']) ?></title>
    <link rel="stylesheet" href="/ehealth_tangub/assets/css/ui.css">
    <style>
        .card { max-width: 400px; margin: 50px auto; padding:20px; }
    </style>
</head>
<body>
<div class="app-container">
<?php require "../layouts/sidebar.php"; ?>
<main class="main-content">
<?php require "../layouts/topbar.php"; ?>



<div class="card">
<form method="POST">
    <h3>Stock In / Out — <?= htmlspecialchars($item['item_name']) ?></h3>
    <div class="form-group">
        <label>Action</label>
        <select name="action">
            <option value="IN">Stock In</option>
            <option value="OUT">Stock Out</option>
        </select>
    </div>

    <div class="form-group">
        <label>Quantity</label>
        <input type="number" name="quantity" required min="1">
    </div>

    <a href="inventory.php" class="btn-danger" style="margin-top:10px; display:inline-block;">
        Cancel
    </a>

    <button class="btn-primary" style="margin-top:10px;">Submit</button>
</form>
</div>

</main>
</div>
</body>
</html>
