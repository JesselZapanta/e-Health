<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

/* ================================
   DASHBOARD COUNTS
================================ */

// Low stock items
$lowStock = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) total
         FROM inventory
         WHERE quantity <= minimum_stock"
    )
)['total'];

// Today's appointments
$todayAppointments = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM appointments
         WHERE appointment_date = CURDATE()
           AND status = 'Approved'"
    )
)['total'];

/* ================================
   TODAY'S APPOINTMENTS LIST
================================ */
$todayList = mysqli_query(
    $conn,
    "SELECT a.appointment_time, a.status,
            u.full_name AS patient_name
     FROM appointments a
     JOIN patients p ON a.patient_id = p.patient_id
     JOIN users u ON p.user_id = u.user_id
     WHERE a.appointment_date = CURDATE()
        AND a.status = 'Approved'
     ORDER BY a.appointment_time ASC"
);

/* ================================
   LOW STOCK ITEMS LIST
================================ */
$lowStockItems = mysqli_query(
    $conn,
    "SELECT * FROM inventory WHERE quantity <= minimum_stock ORDER BY inventory_id ASC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Health Staff Dashboard | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">
    <style>
        .layout {
            display: flex;
        }

        .main {
            flex: 1;
            padding: 25px;
        }

        /* TOPBAR */
        .topbar {
            background: #fff;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        /* DASHBOARD CARDS */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .card {
            background: #fff;
            border-radius: 14px;
            padding: 20px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .card-icon {
            font-size: 30px;
            color: var(--primary);
        }

        .card h4 {
            font-size: 13px;
            color: #555;
        }

        .card p {
            font-size: 26px;
            font-weight: bold;
            margin-top: 5px;
        }

        /* TABLE */
        table {
            width: 100%;
            background: #fff;
            border-radius: 12px;
            box-shadow: var(--shadow);
            border-collapse: collapse;
            overflow: hidden;
            margin-bottom: 25px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
            text-align: left;
        }

        th {
            background: #f8fafc;
        }

        .status.Pending { color: #f59e0b; font-weight: bold; }
        .status.Approved { color: #0284c7; font-weight: bold; }
        .status.Completed { color: #16a34a; font-weight: bold; }

        /* Low stock highlight */
        .low-stock { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>

<div class="layout">

    <!-- ✅ REUSABLE SIDEBAR -->
    <?php require_once "../layouts/sidebar.php"; ?>

    <!-- MAIN CONTENT -->
    <main class="main">

        <!-- TOPBAR -->
        <?php require_once "../layouts/topbar.php"; ?>

        <!-- SUMMARY CARDS -->
        <div class="dashboard-grid">

            <div class="card">
                <div class="card-icon">📅</div>
                <div>
                    <h4>Today's Approved Appointments</h4>
                    <p><?= $todayAppointments ?></p>
                </div>
            </div>

            <div class="card">
                <div class="card-icon">⚠️</div>
                <div>
                    <h4>Low Stock Alerts</h4>
                    <p><?= $lowStock ?></p>
                </div>
            </div>

        </div>

        <!-- TODAY'S APPOINTMENTS TABLE -->
        <h3 style="margin-bottom:15px;">Today's Approved Appointment List</h3>
        <table>
            <tr>
                <th>Time</th>
                <th>Patient</th>
                <th>Status</th>
            </tr>

            <?php if (mysqli_num_rows($todayList) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($todayList)): ?>
                    <tr>
                        <td><?= $row['appointment_time'] ?></td>
                        <td><?= htmlspecialchars($row['patient_name']) ?></td>
                        <td class="status <?= $row['status'] ?>">
                            <?= $row['status'] ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No approved appointments today.</td>
                </tr>
            <?php endif; ?>
        </table>

        <!-- LOW STOCK ITEMS TABLE -->
        <h3 style="margin-bottom:15px; margin-top:15px;">Low Stock Alerts</h3>
        <table>
            <tr>
                <th>Item Name</th>
                <th>Description</th>
                <th>Quantity</th>
                <th>Minimum Stock</th>
            </tr>

            <?php if (mysqli_num_rows($lowStockItems) > 0): ?>
                <?php while ($item = mysqli_fetch_assoc($lowStockItems)): ?>
                    <tr class="<?= $item['quantity'] == 0 ? 'low-stock' : '' ?>">
                        <td><?= htmlspecialchars($item['item_name']) ?></td>
                        <td><?= htmlspecialchars($item['description']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= $item['minimum_stock'] ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align:center;">No low-stock items found.</td>
                </tr>
            <?php endif; ?>
        </table>

    </main>
</div>

</body>
</html>
