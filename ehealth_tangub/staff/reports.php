<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

/* ================================
   FETCH REPORT DATA
================================ */

// Appointments summary
$appointments = mysqli_query(
    $conn,
    "SELECT status, COUNT(*) total
     FROM appointments
     GROUP BY status"
);

// Inventory low stock
$low_stock = mysqli_query(
    $conn,
    "SELECT item_name, quantity, minimum_stock
     FROM inventory
     WHERE quantity <= minimum_stock"
);

// Consultations count
$consultations = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) total FROM consultations"
    )
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reports | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">
    <style>
        .layout { display: flex; }
        .main { flex: 1; padding: 25px; }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 20px;
        }

        h4 {
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th, td {
            padding: 8px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        th {
            background: #f8fafc;
        }

        .status {
            font-weight: bold;
            text-transform: capitalize;
        }

        .ok { color: #16a34a; }
        .warning { color: #dc2626; }
    </style>
</head>
<body>

<div class="layout">
    <?php require_once "../layouts/sidebar.php"; ?>

    <main class="main">
        <?php require_once "../layouts/topbar.php"; ?>

        <h3 style="margin-bottom:20px;">Reports</h3>

        <div class="grid">

            <!-- APPOINTMENT STATUS REPORT -->
            <div class="card">
                <h4>Appointment Summary</h4>
                <table>
                    <tr>
                        <th>Status</th>
                        <th>Total</th>
                    </tr>
                    <?php while ($a = mysqli_fetch_assoc($appointments)): ?>
                    <tr>
                        <td class="status"><?= $a['status'] ?></td>
                        <td><?= $a['total'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>

            <!-- INVENTORY ALERTS -->
            <div class="card">
                <h4>Low Stock Inventory</h4>
                <table>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Min</th>
                        <th>Status</th>
                    </tr>
                    <?php if (mysqli_num_rows($low_stock) > 0): ?>
                        <?php while ($i = mysqli_fetch_assoc($low_stock)): ?>
                        <tr>
                            <td><?= htmlspecialchars($i['item_name']) ?></td>
                            <td><?= $i['quantity'] ?></td>
                            <td><?= $i['minimum_stock'] ?></td>
                            <td class="warning">Low</td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="ok">All items are sufficiently stocked</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>

            <!-- CONSULTATION COUNT -->
            <div class="card">
                <h4>Total Consultations</h4>
                <p style="font-size:28px;font-weight:bold;margin-top:10px;">
                    <?= $consultations['total'] ?>
                </p>
                <p style="color:#666;font-size:13px;">
                    Recorded consultations in the system
                </p>
            </div>

        </div>

    </main>
</div>

</body>
</html>
