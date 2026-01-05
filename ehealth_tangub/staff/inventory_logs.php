<?php
require_once "../config/database.php";
session_start();

if ($_SESSION['role'] !== 'staff') exit();

$id = (int)$_GET['id'];

$logs = mysqli_query(
    $conn,
    "SELECT * FROM inventory_logs 
     WHERE inventory_id = $id 
     ORDER BY log_date DESC"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory Logs</title>
    <link rel="stylesheet" href="/ehealth_tangub/assets/css/ui.css">
</head>
<body>

<div class="app-container">
<?php require_once "../layouts/sidebar.php"; ?>
<main class="main-content">
<?php require_once "../layouts/topbar.php"; ?>

<h3 class="page-title">Inventory Logs</h3>

<div class="card">
<table class="table">
<thead>
<tr>
    <th>Action</th>
    <th>Quantity</th>
    <th>Date</th>
</tr>
</thead>
<tbody>

<?php if (mysqli_num_rows($logs) === 0): ?>
<tr><td colspan="3">No logs found.</td></tr>
<?php endif; ?>

<?php while ($l = mysqli_fetch_assoc($logs)): ?>
<tr>
    <td><?= $l['action'] ?></td>
    <td><?= $l['quantity'] ?></td>
    <td><?= $l['log_date'] ?></td>
</tr>
<?php endwhile; ?>

</tbody>
</table>
</div>

</main>
</div>

</body>
</html>
