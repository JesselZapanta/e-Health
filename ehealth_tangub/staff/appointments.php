<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

/* Fetch pending appointments */
$appointments = mysqli_query($conn, "
    SELECT 
        a.appointment_id,
        a.appointment_date,
        a.appointment_time,
        a.status,
        u.full_name AS patient_name,
        d.full_name AS doctor_name
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    JOIN users u ON p.user_id = u.user_id
    JOIN users d ON a.doctor_id = d.user_id
    WHERE a.status = 'pending'
    ORDER BY a.created_at ASC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Appointment Management | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">
</head>
<body>

<div class="layout">
<?php require "../layouts/sidebar.php"; ?>

<main class="main">
<?php require "../layouts/topbar.php"; ?>

<h2>Appointment Management</h2>

<div class="card">
<table>
<thead>
<tr>
    <th>Patient</th>
    <th>Doctor</th>
    <th>Date</th>
    <th>Time</th>
    <th>Status</th>
    <th style="width:180px;">Action</th>
</tr>
</thead>
<tbody>

<?php if (mysqli_num_rows($appointments) === 0): ?>
<tr>
    <td colspan="6" class="text-center">No pending appointment requests.</td>
</tr>
<?php endif; ?>

<?php while ($a = mysqli_fetch_assoc($appointments)): ?>
<tr>
    <td><?= htmlspecialchars($a['patient_name']) ?></td>
    <td><?= htmlspecialchars($a['doctor_name']) ?></td>
    <td><?= $a['appointment_date'] ?></td>
    <td><?= date("h:i A", strtotime($a['appointment_time'])) ?></td>
    <td>
        <span class="badge badge-warning">Pending</span>
    </td>
    <td>
        <form method="POST" action="update_appointment.php" style="display:inline;">
            <input type="hidden" name="id" value="<?= $a['appointment_id'] ?>">
            <input type="hidden" name="action" value="approve">
            <button class="btn btn-success btn-sm">Approve</button>
        </form>

        <form method="POST" action="update_appointment.php" style="display:inline;">
            <input type="hidden" name="id" value="<?= $a['appointment_id'] ?>">
            <input type="hidden" name="action" value="deny">
            <button class="btn btn-danger btn-sm">Deny</button>
        </form>
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
