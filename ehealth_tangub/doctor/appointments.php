<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];

$appointments = mysqli_query($conn, "
    SELECT 
        a.appointment_id,
        a.appointment_date,
        a.appointment_time,
        u.full_name AS patient_name
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    JOIN users u ON p.user_id = u.user_id
    WHERE a.doctor_id = $doctor_id
    AND a.status = 'approved'
    ORDER BY a.appointment_date, a.appointment_time
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Doctor Appointments | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">
</head>
<body>

<div class="layout">
<?php require "../layouts/sidebar.php"; ?>

<main class="main">
<?php require "../layouts/topbar.php"; ?>

<h2>Approved Appointments</h2>

<div class="card">
<table>
<thead>
<tr>
    <th>Date</th>
    <th>Time</th>
    <th>Patient</th>
    <th style="width:140px;">Action</th>
</tr>
</thead>
<tbody>

<?php if (mysqli_num_rows($appointments) === 0): ?>
<tr>
    <td colspan="4" class="text-center">
        No approved appointments yet.
    </td>
</tr>
<?php endif; ?>

<?php while ($a = mysqli_fetch_assoc($appointments)): ?>
<tr>
    <td><?= $a['appointment_date'] ?></td>
    <td><?= date("h:i A", strtotime($a['appointment_time'])) ?></td>
    <td><?= htmlspecialchars($a['patient_name']) ?></td>
    <td>
        <a href="consultation.php?id=<?= $a['appointment_id'] ?>"
           class="btn btn-success btn-sm">
           Consult
        </a>
    </td>
</tr>
<?php endwhile; ?>

</tbody>
</table>
</div>

</main>
</div>

</body>
</html>
