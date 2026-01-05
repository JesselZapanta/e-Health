<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: consultations.php");
    exit();
}

$consultation_id = (int) $_GET['id'];

$consultation = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT 
            c.*,
            a.appointment_date,
            a.appointment_time,
            u.full_name AS patient_name
         FROM consultations c
         JOIN appointments a ON c.appointment_id = a.appointment_id
         JOIN patients p ON a.patient_id = p.patient_id
         JOIN users u ON p.user_id = u.user_id
         WHERE c.consultation_id = $consultation_id
         AND a.doctor_id = $doctor_id"
    )
);

if (!$consultation) {
    die("Consultation not found.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Consultation Details | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">
    <style>
        .layout { display: flex; }
        .main { flex: 1; padding: 25px; }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 14px;
            box-shadow: var(--shadow);
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="layout">
    <?php require_once "../layouts/sidebar.php"; ?>

    <main class="main">
        <?php require_once "../layouts/topbar.php"; ?>

        <h2>Consultation Details</h2>

        <div class="card">
            <p><strong>Patient:</strong> <?= htmlspecialchars($consultation['patient_name']) ?></p>
            <p><strong>Date:</strong> <?= $consultation['appointment_date'] ?></p>
            <p><strong>Time:</strong> <?= $consultation['appointment_time'] ?></p>
        </div>

        <div class="card">
            <h4>Symptoms</h4>
            <p><?= nl2br(htmlspecialchars($consultation['symptoms'])) ?></p>
        </div>

        <div class="card">
            <h4>Diagnosis</h4>
            <p><?= nl2br(htmlspecialchars($consultation['diagnosis'])) ?></p>
        </div>

        <div class="card">
            <h4>Prescription</h4>
            <p><?= nl2br(htmlspecialchars($consultation['prescription'])) ?></p>
        </div>

        <div class="card">
            <h4>Notes</h4>
            <p><?= nl2br(htmlspecialchars($consultation['notes'])) ?></p>
        </div>

        <a href="consultations.php" class="btn btn-primary">Back to History</a>

    </main>
</div>

</body>
</html>
