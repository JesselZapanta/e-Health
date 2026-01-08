<?php
require_once "../config/database.php";

// Only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
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
        .split-layout {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        .inventory-panel {
            flex: 0 0 65%;
        }

        .detail-panel {
            flex: 0 0 35%;
            max-width: 35%;
        }

        .logs {
            max-height: 300px;
            overflow-y: auto;
        }

        .log-item {
            background:#fff;
            padding:10px;
            border-radius:6px;
            margin-bottom:10px;
            box-shadow:0 1px 3px rgba(0,0,0,0.05);
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
    <?php if (!empty($logs)): ?>
        <?php foreach ($logs as $l): ?>
            <div class="log-item">

                <span style="
                    display:inline-block;
                    padding:3px 8px;
                    border-radius:12px;
                    font-size:12px;
                    font-weight:bold;
                    color:#fff;
                    background: <?= strtoupper($l['action']) === 'OUT' ? '#e74c3c' : '#2ecc71' ?>;
                    margin-bottom:5px;
                ">
                    <?= htmlspecialchars($l['action']) ?>
                </span>

                <span style="display:block; font-size:12px; color:#777; margin-bottom:5px;">
                    <?= $l['log_date'] ?>
                </span>

                <strong>Quantity: <?= $l['quantity'] ?> pcs</strong>

                <?php if (strtoupper($l['action']) === 'OUT'): ?>
                    <span>Name: <?= htmlspecialchars($l['name']) ?></span><br>
                    <span>Address: <?= htmlspecialchars($l['address']) ?></span>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="color:#888; text-align:center; margin-top:30px;">
            No logs available for this item.
        </p>
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

<!-- SEARCH SCRIPT (FIXED & WORKING) -->
<script>
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
