<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

$patient_user_id = $_SESSION['user_id'];

/* Get patient_id */
$p = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT patient_id FROM patients WHERE user_id = $patient_user_id")
);
$patient_id = $p['patient_id'];

/* Doctors list */
$doctors = mysqli_query($conn, "
    SELECT user_id, full_name 
    FROM users 
    WHERE role = 'doctor' AND status = 'active'
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Request Appointment | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">
    <style>
        .form-card {
            max-width: 520px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            font-weight: 600;
            display: block;
            margin-bottom: 6px;
        }
        .form-group input,
        .form-group select {
            width: 100%;
        }
        .form-actions {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="layout">
<?php require "../layouts/sidebar.php"; ?>

<main class="main">
<?php require "../layouts/topbar.php"; ?>

<h2>Request Appointment</h2>

<div class="card form-card">

<form method="POST" action="submit_appointment.php">

    <div class="form-group">
        <label>Doctor</label>
        <select name="doctor_id" required>
            <option value="">-- Select Doctor --</option>
            <?php while ($d = mysqli_fetch_assoc($doctors)): ?>
                <option value="<?= $d['user_id'] ?>">
                    <?= htmlspecialchars($d['full_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="form-group">
        <label>Date</label>
        <input type="date" name="appointment_date" required>
    </div>

    <div class="form-group">
        <label>Preferred Time</label>
        <!-- todo -->
        <!-- change to option select get from the database -->
        <input type="time" name="appointment_time" required>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">
            Submit Request
        </button>
    </div>

</form>

</div>

</main>
</div>

</body>
</html>
