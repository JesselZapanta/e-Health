<?php
require_once "../config/database.php";

if ($_SESSION['role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];

$data_toggle = mysqli_query(
    $conn,
    "SELECT 
        u.full_name AS patient,
        COUNT(pr.prenatal_id) AS visits,
        MAX(pr.visit_date) AS last_visit
     FROM prenatal_records pr
     JOIN patients p ON pr.patient_id = p.patient_id
     JOIN users u ON p.user_id = u.user_id
     JOIN appointments a ON a.patient_id = p.patient_id
     WHERE a.doctor_id = $doctor_id
     GROUP BY p.patient_id"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Prenatal Report</title>
    <link rel="stylesheet" href="../assets/css/ui.css">
    <style>
        .layout { display: flex; }
        .main { flex: 1; padding: 25px; }

        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="layout">
<?php require_once "../layouts/sidebar.php"; ?>

<main class="main">
<?php require_once "../layouts/topbar.php"; ?>

<h2>Prenatal Report</h2>

<button onclick="window.print()" class="btn-primary no-print">
    Print
</button>

<table class="table">
<thead>
<tr>
    <th>Patient</th>
    <th>Total Visits</th>
    <th>Last Visit</th>
</tr>
</thead>
<tbody>
<?php if (mysqli_num_rows($data_toggle) > 0): ?>
    <?php while ($r = mysqli_fetch_assoc($data_toggle)): ?>
        <tr>
            <td><?= htmlspecialchars($r['patient']) ?></td>
            <td><?= $r['visits'] ?></td>
            <td><?= $r['last_visit'] ?></td>
        </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr><td colspan="3">No prenatal data.</td></tr>
<?php endif; ?>
</tbody>
</table>

</main>
</div>

</body>
</html>
