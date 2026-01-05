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

$appointment_id = (int) $_GET['id'];

/* ================================
   VALIDATE APPOINTMENT
================================ */
$appointment = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT a.appointment_id, a.appointment_date, a.appointment_time,
                u.full_name AS patient_name
         FROM appointments a
         JOIN patients p ON a.patient_id = p.patient_id
         JOIN users u ON p.user_id = u.user_id
         WHERE a.appointment_id = $appointment_id
         AND a.doctor_id = $doctor_id
         AND a.status = 'Approved'"
    )
);

if (!$appointment) {
    $invalid = true;
}

/* ================================
   SAVE CONSULTATION
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($invalid)) {

    $symptoms    = mysqli_real_escape_string($conn, $_POST['symptoms']);
    $diagnosis   = mysqli_real_escape_string($conn, $_POST['diagnosis']);
    $prescription= mysqli_real_escape_string($conn, $_POST['prescription']);
    $notes       = mysqli_real_escape_string($conn, $_POST['notes']);

    mysqli_query(
        $conn,
        "INSERT INTO consultations
         (appointment_id, symptoms, diagnosis, prescription, notes)
         VALUES
         ($appointment_id, '$symptoms', '$diagnosis', '$prescription', '$notes')"
    );

    mysqli_query(
        $conn,
        "UPDATE appointments
         SET status = 'Completed'
         WHERE appointment_id = $appointment_id"
    );

    header("Location: consultations.php?success=1");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Consult Patient | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">
    <style>
        .layout { display: flex; }
        .main { flex: 1; padding: 25px; }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 14px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }

        .form-group textarea {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            min-height: 90px;
        }

        .actions {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>

<div class="layout">
    <?php require_once "../layouts/sidebar.php"; ?>

    <main class="main">
        <?php require_once "../layouts/topbar.php"; ?>

        <h2>Consultation</h2>

        <?php if (!empty($invalid)): ?>
            <div class="card">
                <p>No approved appointment found.</p>
                <a href="consultations.php" class="btn btn-primary">Back</a>
            </div>
        <?php else: ?>

        <!-- PATIENT INFO -->
        <div class="card">
            <h3>Patient Information</h3>
            <p><strong>Name:</strong> <?= htmlspecialchars($appointment['patient_name']) ?></p>
            <p><strong>Date:</strong> <?= $appointment['appointment_date'] ?></p>
            <p><strong>Time:</strong> <?= $appointment['appointment_time'] ?></p>
        </div>

        <!-- CONSULTATION FORM -->
        <form method="POST" class="card">

            <div class="form-group">
                <label>Symptoms</label>
                <textarea name="symptoms" required></textarea>
            </div>

            <div class="form-group">
                <label>Diagnosis</label>
                <textarea name="diagnosis" required></textarea>
            </div>

            <div class="form-group">
                <label>Prescription</label>
                <textarea name="prescription"></textarea>
            </div>

            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes"></textarea>
            </div>

            <div class="actions">
                <a href="consultations.php" class="btn btn-danger">Back</a>
                <button type="submit" class="btn btn-success">Save Consultation</button>
            </div>

        </form>

        <?php endif; ?>

    </main>
</div>

</body>
</html>
