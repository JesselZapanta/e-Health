<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

/* ================================
   FILTER HANDLING
================================ */
$filter = $_GET['filter'] ?? 'daily';
$today = date('Y-m-d');
$week_start = date('Y-m-d', strtotime('monday this week'));
$month_start = date('Y-m-01');
$year_start = date('Y-01-01');

switch ($filter) {
    case 'weekly': $date_condition = "appointment_date >= '$week_start'"; break;
    case 'monthly': $date_condition = "appointment_date >= '$month_start'"; break;
    case 'yearly': $date_condition = "appointment_date >= '$year_start'"; break;
    default: $date_condition = "appointment_date = '$today'"; break;
}

/* ================================
   FETCH REPORT DATA
================================ */

// Appointments summary (status)
$appointments_result = mysqli_query(
    $conn,
    "SELECT status, COUNT(*) total
     FROM appointments
     WHERE $date_condition
     GROUP BY status"
);
$appointments_status = [];
while ($row = mysqli_fetch_assoc($appointments_result)) {
    $appointments_status[$row['status']] = $row['total'];
}

// Appointments summary (type)
$appointments_type_result = mysqli_query(
    $conn,
    "SELECT type, COUNT(*) total
     FROM appointments
     WHERE $date_condition
     GROUP BY type"
);
$appointments_type = [];
while ($row = mysqli_fetch_assoc($appointments_type_result)) {
    $appointments_type[$row['type']] = $row['total'];
}

// Consultations count
$consultations_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM consultations") 
)['total'];

// Prenatal visits
$prenatal_visits = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM prenatal_records WHERE visit_date >= '$year_start'")
)['total'];

// Low stock items
$low_stock_result = mysqli_query(
    $conn,
    "SELECT item_name, quantity, minimum_stock FROM inventory WHERE quantity <= minimum_stock"
);

// Inventory in/out summary
$inventory_io_result = mysqli_query(
    $conn,
    "SELECT action, SUM(quantity) as total
     FROM inventory_logs
     WHERE log_date >= '$month_start'
     GROUP BY action"
);
$inventory_io = ['IN'=>0,'OUT'=>0];
while($row=mysqli_fetch_assoc($inventory_io_result)){
    $inventory_io[$row['action']] = $row['total'];
}

// Recent Inventory Logs
$inventory_logs_result = mysqli_query(
    $conn,
    "SELECT l.log_date, i.item_name, l.action, l.quantity, l.name, l.address
     FROM inventory_logs l
     JOIN inventory i ON i.inventory_id = l.inventory_id
     ORDER BY l.log_date DESC
     LIMIT 20"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reports | eHEALTH</title>
    <!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->
</head>
<body>

<div class="flex">
    <?php require_once "../layouts/sidebar.php"; ?>

    <main id="dashboard">
        <?php require_once "../layouts/topbar.php"; ?>

        <h2>Reports Dashboard</h2>

        <!-- FILTERS -->
        <div class="filters">
            <span>Filter:</span>
            <a href="?filter=daily" class="<?= $filter=='daily'?'active':'' ?>">Daily</a>
            <a href="?filter=weekly" class="<?= $filter=='weekly'?'active':'' ?>">Weekly</a>
            <a href="?filter=monthly" class="<?= $filter=='monthly'?'active':'' ?>">Monthly</a>
            <a href="?filter=yearly" class="<?= $filter=='yearly'?'active':'' ?>">Yearly</a>
        </div>

        <!-- REPORT CHARTS -->

    </main>
</div>

</body>
</html>
