<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* Get patient_id */
$p = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT patient_id FROM patients WHERE user_id = $user_id")
);
$patient_id = $p['patient_id'];

/* Fetch appointments */
$appointments = mysqli_query($conn, "
    SELECT 
        a.appointment_date,
        a.appointment_time,
        a.status,
        u.full_name AS doctor_name
    FROM appointments a
    JOIN users u ON a.doctor_id = u.user_id
    WHERE a.patient_id = $patient_id
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Appointments | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">
</head>
<body>

<div class="layout">
<?php require "../layouts/sidebar.php"; ?>

<main class="main">
<?php require "../layouts/topbar.php"; ?>

<!-- PAGE HEADER -->
<div class="card">

    <div class="page-header">
        <h2>My Appointments</h2>
        <a href="request_appointment.php" class="btn-primary">
            ➕ Request Appointment
        </a>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Doctor</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($appointments) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($appointments)): ?>
                    <tr>
                        <td><?= $row['doctor_name'] ?></td>
                        <td><?= date('Y-m-d', strtotime($row['appointment_date'])) ?></td>
                        <td><?= date('h:i A', strtotime($row['appointment_time'])) ?></td>
                        <td>
                            <span class="status <?= strtolower($row['status']) ?>">
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center">No appointments found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>


</body>
</html>
