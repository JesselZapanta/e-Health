<?php
require_once "../config/database.php";

// Only staff can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

// Handle Add New Item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_name'], $_POST['quantity'], $_POST['minimum_stock'])) {
    $name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $desc = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
    $qty = (int)$_POST['quantity'];
    $min = (int)$_POST['minimum_stock'];

    mysqli_query(
        $conn,
        "INSERT INTO inventory (item_name, description, quantity, minimum_stock)
         VALUES ('$name', '$desc', $qty, $min)"
    );

    header("Location: inventory.php");
    exit();
}

// Get inventory items
$items = mysqli_query($conn, "SELECT * FROM inventory ORDER BY item_name ASC");

// Get selected item and logs
$selected = isset($_GET['view']) ? (int)$_GET['view'] : null;
$selectedItem = null;
$logs = [];

if ($selected) {
    $selectedItem = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT * FROM inventory WHERE inventory_id = $selected")
    );

    $logResult = mysqli_query(
        $conn,
        "SELECT * FROM inventory_logs WHERE inventory_id = $selected ORDER BY log_date DESC"
    );

    if ($logResult) {
        while ($row = mysqli_fetch_assoc($logResult)) {
            $logs[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory Management | eHEALTH</title>
    <link rel="stylesheet" href="/ehealth_tangub/assets/css/ui.css">
    <style>
        .split-layout { display: flex; gap: 20px; }
        .has-detail .card { flex: 1; }
        .detail-panel { flex: 1; max-width: 400px; }
        .modal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); justify-content:center; align-items:center; }
        .modal-content { background:#fff; padding:20px; border-radius:8px; width:400px; }
        .logs { max-height: 200px; overflow-y: auto; }
        .log-item { border-bottom: 1px solid #eee; padding:5px 0; }
    </style>
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
        <?= $low ? '<span class="badge badge-danger">Low</span>' : '<span class="badge badge-success">OK</span>' ?>
    </td>
    <td>
        <a href="inventory_stock.php?id=<?= $row['inventory_id'] ?>" class="link-btn">🏷️Stock</a>
        <a href="?view=<?= $row['inventory_id'] ?>" class="link-btn">👁️View</a>
    </td>

</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>

<!-- RIGHT: DETAILS PANEL -->
<div class="card detail-panel">
<?php if ($selectedItem): ?>
    <h4><?= htmlspecialchars($selectedItem['item_name']) ?></h4>
    <p><?= htmlspecialchars($selectedItem['description'] ?? 'No description') ?></p>

    <hr>
    <h5>Logs</h5>
    <div class="logs">
    <?php if (!empty($logs)): ?>
        <?php foreach ($logs as $l): ?>
            <div class="log-item">
                <strong><?= $l['action'] ?></strong> — <?= $l['quantity'] ?> pcs
                <span><?= $l['log_date'] ?></span>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="color:#888;">No logs available for this item.</p>
    <?php endif; ?>
    </div>

<?php else: ?>
    <p style="text-align:center; color:#888; margin-top:50px;">
        Select an item from the list to view details and logs.
    </p>
<?php endif; ?>
</div>

</div>
</main>
</div>

<!-- ADD ITEM MODAL -->
<div class="modal" id="addModal">
<div class="modal-content">
<h4>Add Inventory Item</h4>
<form method="POST">
    <div class="form_wrapper">
        <div class="form-group">
        <label>Item Name</label>
        <input type="text" name="item_name" required>
    </div>

    <div class="form-group">
        <label>Description</label>
        <input type="text" name="description">
    </div>

    <div class="form-group">
        <label>Initial Quantity</label>
        <input type="number" name="quantity" required>
    </div>

    <div class="form-group">
        <label>Minimum Stock</label>
        <input type="number" name="minimum_stock" required>
    </div>
    </div>

    <div class="modal-actions" style="margin-top:10px;">
        <button type="button" class="btn-danger" onclick="closeModal()">Cancel</button>
        <button type="submit" class="btn-primary">Save</button>
    </div>
</form>
</div>
</div>

<script>
function openModal() { document.getElementById('addModal').style.display = 'flex'; }
function closeModal() { document.getElementById('addModal').style.display = 'none'; }
</script>

</body>
</html>
