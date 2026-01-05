<?php
require_once "../../config/database.php";

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit();
}

$patientCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM patients"))['total'];
$apptCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM appointments"))['total'];
$prenatalCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM prenatal_records"))['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reports & Analytics</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<div class="main">
    <h2>System Analytics</h2>

    <div class="cards">
        <div class="card">
            <h3>Total Patients</h3>
            <p><?= $patientCount ?></p>
        </div>
        <div class="card">
            <h3>Total Appointments</h3>
            <p><?= $apptCount ?></p>
        </div>
        <div class="card">
            <h3>Prenatal Visits</h3>
            <p><?= $prenatalCount ?></p>
        </div>
    </div>

    <br>
    <a href="print.php" class="btn-primary">Print Report</a>
</div>

</body>
</html>
