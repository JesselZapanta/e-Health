<?php
require_once "../config/database.php";

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $qr_code = mysqli_real_escape_string($conn, $_POST['qr_code']); // sanitize input

    $query = mysqli_query(
        $conn,
        "SELECT 
            a.appointment_id,
            a.status,
            a.appointment_date,
            a.appointment_time,
            u.full_name AS patient_name
         FROM appointments a
         JOIN patients p ON a.patient_id = p.patient_id
         JOIN users u ON p.user_id = u.user_id
         WHERE a.qr_code = '$qr_code'"
    );

    if (mysqli_num_rows($query) > 0) {

        $result = mysqli_fetch_assoc($query);

        if ($result['status'] === 'Approved') {

            mysqli_query(
                $conn,
                "UPDATE appointments 
                 SET status = 'Completed' 
                 WHERE appointment_id = {$result['appointment_id']}"
            );

            $result['verified'] = true;

        } elseif ($result['status'] === 'Completed') {

            $result['already_checked'] = true;

        } else {

            $result['invalid'] = true;
        }

    } else {
        $result = ['not_found' => true];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>QR Verification | eHEALTH</title>
    <link rel="stylesheet" href="/ehealth_tangub/assets/css/ui.css">
</head>
<body>

<div class="app-container">

    <?php require_once "../layouts/sidebar.php"; ?>

    <main class="main-content">

        <?php require_once "../layouts/topbar.php"; ?>

        <div>
            <h3 class="page-title">Code Verification</h3>

            <!-- INPUT CARD -->
            <div class="card" style="max-width:420px;margin-bottom:20px;">
                <form method="POST">
                    <div class="form-group">
                        <label>Enter Code</label>
                        <input type="text" name="qr_code" required>
                    </div>

                    <button type="submit" class="btn-primary" style="margin-top:10px;">
                        Verify Appointment
                    </button>
                </form>
            </div>

            <!-- RESULT -->
            <?php if ($result): ?>
                <div class="card">

                    <?php if (!empty($result['verified'])): ?>
                        <span class="badge badge-success">Checked In Successfully</span>

                    <?php elseif (!empty($result['already_checked'])): ?>
                        <span class="badge badge-warning">Already Checked In</span>

                    <?php elseif (!empty($result['invalid'])): ?>
                        <span class="badge badge-danger">Appointment Not Approved</span>

                    <?php elseif (!empty($result['not_found'])): ?>
                        <span class="badge badge-danger">Appointment Not Found</span>

                    <?php endif; ?>

                    <?php if (isset($result['patient_name'])): ?>
                        <hr style="margin:15px 0;">
                        <p><strong>Patient:</strong> <?= htmlspecialchars($result['patient_name']) ?></p>
                        <p><strong>Date:</strong> <?= $result['appointment_date'] ?></p>
                        <p><strong>Time:</strong> <?= $result['appointment_time'] ?></p>
                        <p><strong>Status:</strong> <?= $result['status'] ?></p>
                    <?php endif; ?>

                </div>
            <?php endif; ?>
        </div>

    </main>
</div>

</body>
</html>
