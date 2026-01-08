<?php
require_once "../config/database.php";

// Only staff can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

// Handle Add New Item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_name'], $_POST['quantity'], $_POST['maximum_stock'])) {
    $name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $desc = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
    $qty = (int)$_POST['quantity'];
    $max = (int)$_POST['maximum_stock'];

    $query = "INSERT INTO inventory (item_name, description, quantity, maximum_stock)
              VALUES ('$name', '$desc', $qty, $max)";

    if (!mysqli_query($conn, $query)) {
        die("Database Error: " . mysqli_error($conn));
    }

    header("Location: inventory.php");
    exit();
}

// Get inventory items
$items = mysqli_query($conn, "SELECT * FROM inventory ORDER BY inventory_id DESC");

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
        "SELECT * FROM inventory_logs WHERE inventory_id = $selected ORDER BY log_date DESC")
    ;

    while ($row = mysqli_fetch_assoc($logResult)) {
        $logs[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory Management | eHEALTH</title>
    <link rel="stylesheet" href="/ehealth_tangub/assets/css/ui.css">

    <style>
        .split-layout {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        /* LEFT: Inventory table (fixed width) */
        .inventory-panel {
            flex: 0 0 65%;
        }

        /* RIGHT: Details panel (fixed width) */
        .detail-panel {
            flex: 0 0 35%;
            max-width: 35%;
        }

        .modal {
            display:none;
            position:fixed;
            inset:0;
            background:rgba(0,0,0,.5);
            justify-content:center;
            align-items:center;
        }

        .modal-content {
            background:#fff;
            padding:20px;
            border-radius:8px;
            width:400px;
        }

        .logs {
            max-height:300px;
            overflow-y:auto;
        }

        .log-item {
            background:#fff;
            padding:10px;
            border-radius:6px;
            margin-bottom:10px;
            box-shadow:0 1px 3px rgba(0,0,0,.05);
        }

        .log-item strong {
            display:block;
            font-size:14px;
            margin-bottom:3px;
        }

        .log-item span {
            font-size:12px;
            color:#555;
        }
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

<div class="split-layout">

<!-- LEFT: INVENTORY TABLE -->
<div class="card inventory-panel">
    <div class="form-group">
        <label>Search Item</label>
        <input type="text" id="searchInput" placeholder="Type item name..." />
    </div>

<table class="table">
<thead>
<tr>
    <th>Item</th>
    <th>Qty</th>
    <th>Max</th>
    <th>Status</th>
    <th>Action</th>
</tr>
</thead>
<tbody>
<?php while ($row = mysqli_fetch_assoc($items)):
    $low = $row['quantity'] < 0.2 * $row['maximum_stock'];
?>
<tr>
    <td><?= htmlspecialchars($row['item_name']) ?></td>
    <td><?= $row['quantity'] ?></td>
    <td><?= $row['maximum_stock'] ?></td>
    <td>
        <?= $low
            ? '<span class="badge badge-danger">Low</span>'
            : '<span class="badge badge-success">OK</span>' ?>
    </td>
    <td>
        <a href="inventory_stock.php?id=<?= $row['inventory_id'] ?>" class="link-btn">🏷️ Stock</a>
        <a href="?view=<?= $row['inventory_id'] ?>" class="link-btn">👁️ View</a>
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
    <?php if ($logs): foreach ($logs as $l): ?>
        <div class="log-item">
            <span style="
                display:inline-block;
                padding:3px 8px;
                border-radius:12px;
                font-size:12px;
                font-weight:bold;
                color:#fff;
                background:<?= strtoupper($l['action']) === 'OUT' ? '#e74c3c' : '#2ecc71' ?>;">
                <?= htmlspecialchars($l['action']) ?>
            </span>

            <span style="display:block;color:#777;font-size:12px;margin:5px 0;">
                <?= $l['log_date'] ?>
            </span>

            <strong>Quantity: <?= $l['quantity'] ?> pcs</strong>

            <?php if (strtoupper($l['action']) === 'OUT'): ?>
                <span>Name: <?= htmlspecialchars($l['name']) ?></span><br>
                <span>Address: <?= htmlspecialchars($l['address']) ?></span>
            <?php endif; ?>
        </div>
    <?php endforeach; else: ?>
        <p style="text-align:center;color:#888;margin-top:30px;">
            No logs available for this item.
        </p>
    <?php endif; ?>
    </div>

<?php else: ?>
    <p style="text-align:center;color:#888;margin-top:50px;">
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
        <label>Maximum Stock</label>
        <input type="number" name="maximum_stock" required>
    </div>

    <div class="modal-actions" style="margin-top:10px;">
        <button type="button" class="btn-danger" onclick="closeModal()">Cancel</button>
        <button type="submit" class="btn-primary">Save</button>
    </div>
</form>
</div>
</div>

<script>
function openModal(){ document.getElementById('addModal').style.display='flex'; }
function closeModal(){ document.getElementById('addModal').style.display='none'; }

const searchInput = document.getElementById('searchInput');
const table = document.querySelector('.inventory-panel table');

function filterTable() {
    const searchText = searchInput.value.toLowerCase().trim();
    let anyVisible = false;

    for (let row of table.tBodies[0].rows) {
        const itemName = row.cells[0].textContent.toLowerCase().trim();

        if (itemName.includes(searchText)) {
            row.style.display = '';
            anyVisible = true;
        } else {
            row.style.display = 'none';
        }
    }

    let noDataRow = table.tBodies[0].querySelector('.no-data-row');
    if (!noDataRow) {
        noDataRow = document.createElement('tr');
        noDataRow.classList.add('no-data-row');
        noDataRow.innerHTML =
            '<td colspan="5" style="text-align:center; padding:10px;">No data found.</td>';
        table.tBodies[0].appendChild(noDataRow);
    }

    noDataRow.style.display = anyVisible ? 'none' : '';
}

searchInput.addEventListener('input', filterTable);

</script>

</body>
</html>
