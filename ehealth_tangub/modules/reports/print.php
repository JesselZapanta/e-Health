<?php
require_once "../../config/database.php";

$patients = mysqli_query($conn, "SELECT COUNT(*) total FROM patients");
$appointments = mysqli_query($conn, "SELECT COUNT(*) total FROM appointments");
?>

<!DOCTYPE html>
<html>
<head>
    <title>eHEALTH Report</title>
    <style>
        body { font-family: Arial; }
        h2 { text-align:center; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        td { padding:10px; border:1px solid #000; }
    </style>
</head>
<body onload="window.print()">

<h2>Tangub City Health Office<br>System Report</h2>
<p>Date Generated: <?= date("Y-m-d") ?></p>

<table>
    <tr>
        <td>Total Patients</td>
        <td><?= mysqli_fetch_assoc($patients)['total'] ?></td>
    </tr>
    <tr>
        <td>Total Appointments</td>
        <td><?= mysqli_fetch_assoc($appointments)['total'] ?></td>
    </tr>
</table>

</body>
</html>
