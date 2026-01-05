<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /ehealth_tangub/auth/login.php");
    exit();
}

$totalPatients = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM patients"))[0];
$totalAppointments = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM appointments"))[0];
$totalConsultations = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM consultations"))[0];
$totalPrenatal = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM prenatal_records"))[0];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reports | eHEALTH</title>
    <link rel="stylesheet" href="/ehealth_tangub/assets/css/ui.css">
</head>
<body>

<div style="display:flex">
    <?php require_once "../layouts/sidebar.php"; ?>

    <main style="flex:1;padding:25px">
        <?php require_once "../layouts/topbar.php"; ?>

        <h3>System Reports</h3>

        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px">
            <div class="card"><h2><?= $totalPatients ?></h2><p>Total Patients</p></div>
            <div class="card"><h2><?= $totalAppointments ?></h2><p>Total Appointments</p></div>
            <div class="card"><h2><?= $totalConsultations ?></h2><p>Total Consultations</p></div>
            <div class="card"><h2><?= $totalPrenatal ?></h2><p>Prenatal Cases</p></div>
        </div>

    </main>
</div>

</body>
</html>
