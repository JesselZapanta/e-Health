<?php
require_once "../config/database.php";


if ($_SESSION['role'] !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

$items = mysqli_query($conn, "SELECT * FROM inventory ORDER BY item_name ASC");
$selected = isset($_GET['view']) ? (int)$_GET['view'] : null;

$selectedItem = null;
$logs = null;

if ($selected) {
    $selectedItem = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT * FROM inventory WHERE inventory_id = $selected")
    );

    $logs = mysqli_query(
        $conn,
        "SELECT * FROM inventory_logs WHERE inventory_id = $selected ORDER BY log_date DESC"
    );
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory Management | eHEALTH</title>
    <link rel="stylesheet" href="/ehealth_tangub/assets/css/ui.css">
</head>
<body>

<div class="app-container">
<?php require "../layouts/sidebar.php"; ?>

<main class="main-content">
<?php require "../layouts/topbar.php"; ?>

<div class="page-header">
    <h3>Inventory Management</h3>
    <button class="btn-primary" onclick="openModal()">+ Add Item</button>
</div>

<div class="split-layout <?= $selected ? 'has-detail' : '' ?>">

<!-- LEFT: INVENTORY TABLE -->
<div class="card">
<table class="table">
<thead>
<tr>
    <th>Item</th>
    <th>Qty</th>
    <th>Min</th>
    <th>Status</th>
    <th>Action</th>
</tr>
</thead>
<tbody>

<?php while ($row = mysqli_fetch_assoc($items)):
    $low = $row['quantity'] <= $row['minimum_stock'];
?>
<tr>
    <td><?= htmlspecialchars($row['item_name']) ?></td>
    <td><?= $row['quantity'] ?></td>
    <td><?= $row['minimum_stock'] ?></td>
    <td>
        <?= $low
            ? '<span class="badge badge-danger">Low</span>'
            : '<span class="badge badge-success">OK</span>' ?>
    </td>
    <td>
        <a href="?view=<?= $row['inventory_id'] ?>" class="link-btn">View</a>
    </td>
</tr>
<?php endwhile; ?>

</tbody>
</table>
</div>

<!-- RIGHT: DETAILS PANEL -->
<?php if ($selectedItem): ?>
<div class="card detail-panel">

<h4><?= htmlspecialchars($selectedItem['item_name']) ?></h4>
<p><?= htmlspecialchars($selectedItem['description'] ?? 'No description') ?></p>

<hr>

<h5>Stock In / Out</h5>
<form method="POST" action="inventory_stock.php">
    <input type="hidden" name="inventory_id" value="<?= $selectedItem['inventory_id'] ?>">

    <select name="action">
        <option value="IN">Stock In</option>
        <option value="OUT">Stock Out</option>
    </select>

    <input type="number" name="quantity" placeholder="Quantity" required>

    <button class="btn-primary">Submit</button>
</form>

<hr>

<h5>Logs</h5>
<div class="logs">
<?php while ($l = mysqli_fetch_assoc($logs)): ?>
    <div class="log-item">
        <strong><?= $l['action'] ?></strong> —
        <?= $l['quantity'] ?> pcs
        <span><?= $l['log_date'] ?></span>
    </div>
<?php endwhile; ?>
</div>

</div>
<?php endif; ?>

</div>
</main>
</div>

<!-- ADD ITEM MODAL -->
<div class="modal" id="addModal">
<div class="modal-content">
<h4>Add Inventory Item</h4>

<form method="POST" action="inventory_add.php">
    <label>Item Name</label>
    <input type="text" name="item_name" required>

    <label>Description</label>
    <input type="text" name="description">

    <label>Initial Quantity</label>
    <input type="number" name="quantity" required>

    <label>Minimum Stock</label>
    <input type="number" name="minimum_stock" required>

    <div class="modal-actions">
        <button type="button" onclick="closeModal()">Cancel</button>
        <button class="btn-primary">Save</button>
    </div>
</form>
</div>
</div>

<script>
function openModal() {
    document.getElementById('addModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('addModal').style.display = 'none';
}
</script>

</body>
</html>
