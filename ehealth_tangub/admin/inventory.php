<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$items = mysqli_query(
    $conn,
    "SELECT inventory_id, item_name, description, quantity, minimum_stock
     FROM inventory
     ORDER BY item_name ASC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inventory | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 14px;
            box-shadow: var(--shadow);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 14px;
            border-bottom: 1px solid #eee;
            text-align: left;
            font-size: 14px;
            vertical-align: middle;
        }

        th {
            background: #f8fafc;
            font-weight: 600;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-ok {
            background: #dcfce7;
            color: #166534;
        }

        .badge-low {
            background: #fee2e2;
            color: #991b1b;
        }

        .actions {
            width: 120px;
        }
    </style>
</head>
<body>

<div class="layout">
<?php require_once "../layouts/sidebar.php"; ?>

<main class="main">
<?php require_once "../layouts/topbar.php"; ?>

<div class="page-header">
    <h2>Inventory</h2>
</div>

<div class="card">
<table>
    <thead>
        <tr>
            <th>Item Name</th>
            <th>Description</th>
            <th>Quantity</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if (mysqli_num_rows($items) > 0): ?>
            <?php while ($item = mysqli_fetch_assoc($items)): ?>
                <tr>
                    <td><?= htmlspecialchars($item['item_name']) ?></td>
                    <td><?= htmlspecialchars($item['description'] ?? '—') ?></td>
                    <td><?= (int)$item['quantity'] ?></td>
                    <td>
                        <?php if ($item['quantity'] <= $item['minimum_stock']): ?>
                            <span class="badge badge-low">Low Stock</span>
                        <?php else: ?>
                            <span class="badge badge-ok">Sufficient</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">No inventory items found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>

</main>
</div>

</body>
</html>
