<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['patient_id'])) {
    header("Location: prenatal.php");
    exit();
}

$doctor_id  = $_SESSION['user_id'];
$patient_id = (int) $_GET['patient_id'];

/* ================================
   SECURITY CHECK
================================ */
$verify = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT 1
         FROM appointments
         WHERE doctor_id = $doctor_id
         AND patient_id = $patient_id
         LIMIT 1"
    )
);

if (!$verify) {
    die("Access denied.");
}

/* ================================
   FETCH PATIENT INFO
================================ */
$patient = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT u.full_name
         FROM patients p
         JOIN users u ON p.user_id = u.user_id
         WHERE p.patient_id = $patient_id"
    )
);

/* ================================
   FETCH PRENATAL DATA
================================ */
$records = mysqli_query(
    $conn,
    "SELECT visit_date, weight, blood_pressure
     FROM prenatal_records
     WHERE patient_id = $patient_id
     ORDER BY visit_date ASC"
);

$dates = [];
$weights = [];
$systolic = [];
$diastolic = [];

while ($r = mysqli_fetch_assoc($records)) {
    $dates[] = $r['visit_date'];

    // Weight (strip non-numeric)
    $weights[] = (float) preg_replace('/[^0-9.]/', '', $r['weight']);

    // Blood pressure parsing (120/80)
    if (strpos($r['blood_pressure'], '/') !== false) {
        [$sys, $dia] = explode('/', $r['blood_pressure']);
        $systolic[] = (int) $sys;
        $diastolic[] = (int) $dia;
    } else {
        $systolic[] = null;
        $diastolic[] = null;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Prenatal Charts | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .layout { display: flex; }
        .main { flex: 1; padding: 25px; }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 14px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="layout">
    <?php require_once "../layouts/sidebar.php"; ?>

    <main class="main">
        <?php require_once "../layouts/topbar.php"; ?>

        <h2 style="margin-bottom:15px;">
            Prenatal Charts – <?= htmlspecialchars($patient['full_name']) ?>
        </h2>

        <!-- WEIGHT CHART -->
        <div class="card">
            <h4>Weight Over Time (kg)</h4>
            <canvas id="weightChart"></canvas>
        </div>

        <!-- BLOOD PRESSURE CHART -->
        <div class="card">
            <h4>Blood Pressure Over Time</h4>
            <canvas id="bpChart"></canvas>
        </div>

        <a href="prenatal.php" class="btn btn-primary">Back</a>
    </main>
</div>

<script>
const labels = <?= json_encode($dates) ?>;

// WEIGHT CHART
new Chart(document.getElementById('weightChart'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Weight (kg)',
            data: <?= json_encode($weights) ?>,
            borderColor: '#0f766e',
            backgroundColor: 'rgba(15,118,110,0.2)',
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: false }
        }
    }
});

// BLOOD PRESSURE CHART
new Chart(document.getElementById('bpChart'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [
            {
                label: 'Systolic',
                data: <?= json_encode($systolic) ?>,
                borderColor: '#dc2626',
                tension: 0.3
            },
            {
                label: 'Diastolic',
                data: <?= json_encode($diastolic) ?>,
                borderColor: '#2563eb',
                tension: 0.3
            }
        ]
    },
    options: {
        responsive: true
    }
});
</script>

</body>
</html>
