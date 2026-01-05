<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];
$patient_id = intval($_GET['patient_id'] ?? 0);

if (!$patient_id) {
    die("Invalid patient.");
}

/* ================================
   FETCH PATIENT INFO
================================ */
$patient = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT u.full_name
         FROM patients p
         JOIN users u ON p.user_id = u.user_id
         WHERE p.patient_id = $patient_id"
    )
);

/* ================================
   CONSULTATION HISTORY
================================ */
$consultations = mysqli_query(
    $conn,
    "SELECT a.appointment_date,
            c.symptoms,
            c.diagnosis,
            c.prescription,
            c.notes
     FROM consultations c
     JOIN appointments a ON c.appointment_id = a.appointment_id
     WHERE a.patient_id = $patient_id
     AND a.doctor_id = $doctor_id
     ORDER BY a.appointment_date DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Consultation History | eHEALTH</title>
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

        <h2 class="page-title">
            Consultation History — <?= htmlspecialchars($patient['full_name']) ?>
        </h2>

        <a href="patients.php" class="btn btn-sm btn-danger" style="margin-bottom:15px;">
            ← Back to Patients
        </a>

        <?php if (mysqli_num_rows($consultations) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($consultations)): ?>
                <div class="card">
                    <strong>Date:</strong> <?= $row['appointment_date'] ?><br><br>

                    <strong>Symptoms:</strong><br>
                    <?= nl2br(htmlspecialchars($row['symptoms'])) ?><br><br>

                    <strong>Diagnosis:</strong><br>
                    <?= nl2br(htmlspecialchars($row['diagnosis'])) ?><br><br>

                    <strong>Prescription:</strong><br>
                    <?= nl2br(htmlspecialchars($row['prescription'])) ?><br><br>

                    <?php if ($row['notes']): ?>
                        <strong>Notes:</strong><br>
                        <?= nl2br(htmlspecialchars($row['notes'])) ?>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="card">
                No consultation history available.
            </div>
        <?php endif; ?>

    </main>
</div>

</body>
</html>
