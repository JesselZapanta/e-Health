<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

/* ================================
   FILTER HANDLING
================================ */
$filter      = $_GET['filter'] ?? 'daily';
$today       = date('Y-m-d');
$week_start  = date('Y-m-d', strtotime('monday this week'));
$month_start = date('Y-m-01');
$year_start  = date('Y-01-01');

switch ($filter) {
    case 'weekly':
        $date_condition = "appointment_date >= '$week_start'";
        break;
    case 'monthly':
        $date_condition = "appointment_date >= '$month_start'";
        break;
    case 'yearly':
        $date_condition = "appointment_date >= '$year_start'";
        break;
    default:
        $date_condition = "appointment_date = '$today'";
        break;
}

/* ================================
   KPI METRICS
================================ */
$totalAppointments = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM appointments WHERE $date_condition AND status='Approved'")
)['total'] ?? 0;

$checkinAppointments = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM appointments WHERE $date_condition AND status='Check-in'")
)['total'] ?? 0;

$cancelledAppointments = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM appointments WHERE $date_condition AND status='Cancelled'")
)['total'] ?? 0;

$completedAppointments = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM appointments WHERE $date_condition AND status='Completed'")
)['total'] ?? 0;

$totalPatients = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM patients")
)['total'] ?? 0;

$malePatients = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM patients WHERE gender='male'")
)['total'] ?? 0;

$femalePatients = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM patients WHERE gender='female'")
)['total'] ?? 0;

$consultations_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM consultations")
)['total'] ?? 0;

$prenatal_visits = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM prenatal_records WHERE visit_date >= '$year_start'")
)['total'] ?? 0;

$lowStockCount = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM inventory WHERE quantity <= minimum_stock")
)['total'] ?? 0;

/* ================================
   CHART DATA
================================ */
$appointments_status = [];
$res = mysqli_query(
    $conn,
    "SELECT status, COUNT(*) total FROM appointments WHERE $date_condition GROUP BY status"
);
while ($r = mysqli_fetch_assoc($res)) {
    $appointments_status[$r['status']] = $r['total'];
}

$appointments_type = [];
$res = mysqli_query(
    $conn,
    "SELECT type, COUNT(*) total FROM appointments WHERE $date_condition GROUP BY type"
);
while ($r = mysqli_fetch_assoc($res)) {
    $appointments_type[$r['type']] = $r['total'];
}

$gender = ['male' => 0, 'female' => 0];
$res = mysqli_query($conn, "SELECT gender, COUNT(*) total FROM patients GROUP BY gender");
while ($r = mysqli_fetch_assoc($res)) {
    if ($r['gender']) {
        $gender[$r['gender']] = $r['total'];
    }
}

$age = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "
        SELECT
            SUM(TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) < 18) below_18,
            SUM(TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 18 AND 30) age_18_30,
            SUM(TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 31 AND 50) age_31_50,
            SUM(TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) > 50) above_50
        FROM patients
        "
    )
) ?: [
    'below_18'   => 0,
    'age_18_30' => 0,
    'age_31_50' => 0,
    'above_50'  => 0
];

/* ================================
   INVENTORY TABLES
================================ */
$most_stock_out = mysqli_query(
    $conn,
    "
    SELECT i.item_name, SUM(l.quantity) total_out
    FROM inventory_logs l
    JOIN inventory i ON i.inventory_id = l.inventory_id
    WHERE l.action = 'OUT'
    GROUP BY i.item_name
    ORDER BY total_out DESC
    LIMIT 5
    "
);

$least_stock_out = mysqli_query(
    $conn,
    "
    SELECT i.item_name, IFNULL(SUM(l.quantity), 0) total_out
    FROM inventory i
    LEFT JOIN inventory_logs l
        ON i.inventory_id = l.inventory_id
        AND l.action = 'OUT'
    GROUP BY i.item_name
    ORDER BY total_out ASC
    LIMIT 5
    "
);

// Fetch staff name
$user_id = $_SESSION['user_id'];
$current_user = "ehealth user"; // default fallback

$result = mysqli_query($conn, "SELECT full_name FROM users WHERE user_id = '$user_id' LIMIT 1");
if($row = mysqli_fetch_assoc($result)) {
    $current_user = $row['full_name'];
}

// Determine greeting based on current hour
$hour = date('H');
if ($hour < 12) {
    $greeting = "Good morning";
} elseif ($hour < 18) {
    $greeting = "Good afternoon";
} else {
    $greeting = "Good evening";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reports | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }
        .card {
            background: #fff;
            padding: 18px;
            border-radius: 14px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, .08);
        }
        .card h4 {
            font-size: 13px;
            color: #555;
        }
        .stat {
            font-size: 28px;
            font-weight: 700;
        }
        .blue   { border-left: 6px solid #3498db; }
        .green  { border-left: 6px solid #2ecc71; }
        .orange { border-left: 6px solid #f39c12; }
        .red    { border-left: 6px solid #e74c3c; }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 6px 0;
        }

        form{
            padding: 1rem 0;
        }

        .filter-select {
            padding: 8px 12px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
            background-color: #f9f9f9;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .filter-select:hover {
            border-color: #888;
            background-color: #f0f0f0;
        }

        .filter-select:focus {
            outline: none;
            border-color: #4CAF50;
            background-color: #fff;
        }
    </style>
</head>

<body>
<div class="layout">
    <?php require "../layouts/sidebar.php"; ?>

    <main class="main">
        <?php require "../layouts/topbar.php"; ?>

        <h3><i class="fa-solid fa-chart-line"></i> Reports Dashboard</h3>

        <form method="GET">
            <select name="filter" onchange="this.form.submit()" class="filter-select">
                <option value="daily"  <?= $filter == 'daily'  ? 'selected' : '' ?>>Daily</option>
                <option value="weekly" <?= $filter == 'weekly' ? 'selected' : '' ?>>Weekly</option>
                <option value="monthly"<?= $filter == 'monthly'? 'selected' : '' ?>>Monthly</option>
                <option value="yearly" <?= $filter == 'yearly' ? 'selected' : '' ?>>Yearly</option>
            </select>
        </form>

        <div class="card green" style="margin-bottom: 1rem;">
            <h4><?= date('n/j/Y') ?></h4>
            <div class="stat"><?= "$greeting, $current_user" ?></div>
        </div>

        <!-- KPI CARDS -->
        <div class="grid">
            <div class="card blue">
                <h4>Total Approved Appointments</h4>
                <div class="stat"><?= $totalAppointments ?></div>
            </div>

            <div class="card green">
                <h4>Total Check-in Appointments</h4>
                <div class="stat"><?= $checkinAppointments ?></div>
            </div>

            <div class="card orange">
                <h4>Total Cancelled Appointments</h4>
                <div class="stat"><?= $cancelledAppointments ?></div>
            </div>

            <div class="card blue">
                <h4>Total Completed Appointments</h4>
                <div class="stat"><?= $completedAppointments ?></div>
            </div>

            <div class="card green">
                <h4>Total Registered Patients</h4>
                <div class="stat"><?= $totalPatients ?></div>
            </div>

            <div class="card blue">
                <h4>Male Registered Patients</h4>
                <div class="stat"><?= $malePatients ?></div>
            </div>

            <div class="card orange">
                <h4>Female Registered Patients</h4>
                <div class="stat"><?= $femalePatients ?></div>
            </div>

            <div class="card red">
                <h4>Low Stock Alerts</h4>
                <div class="stat"><?= $lowStockCount ?></div>
            </div>
        </div>

        <!-- CHARTS -->
        <div class="grid-2">
            <div class="card">
                <h4>Appointments Status</h4>
                <?php if(empty($appointments_status)): ?>
                    <p>No data</p>
                <?php else: ?>
                    <canvas id="statusChart"></canvas>
                <?php endif; ?>
            </div>

            <div class="card">
                <h4>Appointments Type</h4>
                <?php if(empty($appointments_type)): ?>
                    <p>No data</p>
                <?php else: ?>
                    <canvas id="typeChart"></canvas>
                <?php endif; ?>
            </div>

            <div class="card">
                <h4>Patients by Gender</h4>
                <?php if($gender['male'] == 0 && $gender['female'] == 0): ?>
                    <p>No data</p>
                <?php else: ?>
                    <canvas id="genderChart"></canvas>
                <?php endif; ?>
            </div>

            <div class="card">
                <h4>Patients by Age Group</h4>
                <?php if(array_sum($age) == 0): ?>
                    <p>No data</p>
                <?php else: ?>
                    <canvas id="ageChart"></canvas>
                <?php endif; ?>
            </div>
        </div>

        <!-- INVENTORY TABLES -->
        <div class="grid-2">
            <div class="card">
                <h4>Most Stock-Out Items (Pinakadaghan nahatag)</h4>
                <table>
                    <?php if (mysqli_num_rows($most_stock_out) == 0): ?>
                        <tr><td>0</td></tr>
                    <?php endif; ?>
                    <?php while ($m = mysqli_fetch_assoc($most_stock_out)): ?>
                        <tr>
                            <td><?= $m['item_name'] ?></td>
                            <td><b><?= $m['total_out'] ?></b></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>

            <div class="card">
                <h4>Least Stock-Out Items (Dili kaayu mahatag)</h4>
                <table>
                    <?php if (mysqli_num_rows($least_stock_out) == 0): ?>
                        <tr><td>0</td></tr>
                    <?php endif; ?>
                    <?php while ($l = mysqli_fetch_assoc($least_stock_out)): ?>
                        <tr>
                            <td><?= $l['item_name'] ?></td>
                            <td><b><?= $l['total_out'] ?></b></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
<?php if(!empty($appointments_status)): ?>
new Chart(statusChart, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_keys($appointments_status)) ?>,
        datasets: [{ data: <?= json_encode(array_values($appointments_status)) ?> }]
    },
    options: {
        plugins: { legend: { display: false } }
    }
});
<?php endif; ?>

<?php if(!empty($appointments_type)): ?>
new Chart(typeChart, {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_keys($appointments_type)) ?>,
        datasets: [{ data: <?= json_encode(array_values($appointments_type)) ?> }]
    }
});
<?php endif; ?>

<?php if($gender['male'] != 0 || $gender['female'] != 0): ?>
new Chart(genderChart, {
    type: 'pie',
    data: {
        labels: ['Male', 'Female'],
        datasets: [{ data: [<?= $gender['male'] ?>, <?= $gender['female'] ?>] }]
    }
});
<?php endif; ?>

<?php if(array_sum($age) != 0): ?>
new Chart(ageChart, {
    type: 'bar',
    data: {
        labels: ['Below 18', '18–30', '31–50', '50+'],
        datasets: [{
            data: [
                <?= $age['below_18'] ?>,
                <?= $age['age_18_30'] ?>,
                <?= $age['age_31_50'] ?>,
                <?= $age['above_50'] ?>
            ]
        }]
    }
});
<?php endif; ?>
</script>

</body>
</html>
