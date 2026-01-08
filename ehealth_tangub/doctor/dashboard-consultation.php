<?php
require_once "../config/database.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

$doctor_id = (int) $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: consultations.php");
    exit();
}

$appointment_id = (int) $_GET['id'];

/* ================================
   VALIDATE APPOINTMENT
================================ */
$appointment = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT 
        a.appointment_id,
        a.patient_id,
        a.type,
        u.full_name AS patient_name
     FROM appointments a
     JOIN patients p ON a.patient_id = p.patient_id
     JOIN users u ON p.user_id = u.user_id
     WHERE a.appointment_id = $appointment_id
       AND a.doctor_id = $doctor_id
       AND a.status = 'Check-in'
     LIMIT 1"
));

if (!$appointment) {
    $invalid = true;
}

/* ================================
   FETCH INFORMATIONS
================================ */
$information = null;

if (empty($invalid)) {
    $patient_id = (int) $appointment['patient_id'];

    $information = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT *
         FROM informations
         WHERE patients_id = $patient_id
           AND appointment_id = $appointment_id
         LIMIT 1"
    ));
}

/* ================================
   SAVE CONSULTATION
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($invalid)) {

    $symptoms     = mysqli_real_escape_string($conn, $_POST['symptoms']);
    $diagnosis    = mysqli_real_escape_string($conn, $_POST['diagnosis']);
    $prescription = mysqli_real_escape_string($conn, $_POST['prescription']);
    $notes        = mysqli_real_escape_string($conn, $_POST['notes']);

    mysqli_query($conn,
        "INSERT INTO consultations
         (appointment_id, symptoms, diagnosis, prescription, notes)
         VALUES
         ($appointment_id, '$symptoms', '$diagnosis', '$prescription', '$notes')"
    );

    mysqli_query($conn,
        "UPDATE appointments
         SET status = 'Completed'
         WHERE appointment_id = $appointment_id"
    );

    header("Location: dashboard.php?success=1");
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
            padding: 20px 24px;
            border-radius: 14px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }

        h2 { margin-bottom: 1.5rem; }

        /* FLEX ROW FOR RECORDS */
        .row-layout {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .row-layout .card {
            flex: 1;
            min-width: 280px;
        }

        /* RECORD STYLE */
        .record {
            border-left: 4px solid #187b34ff;
            padding-left: 16px;
            margin-top: 12px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 14px;
        }

        .row span {
            color: #555;
            font-weight: 600;
            min-width: 160px;
        }

        .row p {
            margin: 0;
            color: #111;
            text-align: right;
            max-width: 60%;
        }

        .section {
            margin-top: 20px;
            padding-top: 12px;
            border-top: 1px dashed #ddd;
        }

        .section h4 {
            margin-bottom: 10px;
            font-size: 15px;
            color: #333;
        }

        /* PRENATAL INFO ROW */
        .prenatal {
            background: #f8fafc;
            border-radius: 10px;
            padding: 14px;
            margin-top: 16px;
            border: 1px solid #e5e7eb;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .prenatal .row {
            flex: 1 1 200px; /* flexible but not too small */
            margin: 0;
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
        <p>No checked-in appointment found.</p>
        <a href="consultations.php" class="btn btn-primary">Back</a>
    </div>

<?php else: ?>

    <!-- ROW: Patient + Medical -->
    <div class="row-layout">

        <!-- PATIENT RECORD -->
        <div class="card">
            <h3>Patient Record</h3>
            <div class="record">
                <div class="row"><span>Patient Name</span><p><?= htmlspecialchars($appointment['patient_name']) ?></p></div>
                <div class="row"><span>Appointment Type</span><p><?= ucfirst($appointment['type']) ?></p></div>
            </div>
        </div>

        <!-- MEDICAL RECORD -->
        <div class="card">
            <h3>Medical Record</h3>

            <?php if ($information): ?>
                <div class="record">
                    <div class="row"><span>Blood Pressure</span><p><?= $information['blood_pressure'] ?></p></div>
                    <div class="row"><span>Temperature</span><p><?= $information['temperature'] ?></p></div>
                    <div class="row"><span>Heart Rate</span><p><?= $information['heart_rate'] ?></p></div>
                    <div class="row"><span>Respiratory Rate</span><p><?= $information['respiratory_rate'] ?></p></div>
                    <div class="row"><span>Weight</span><p><?= $information['weight'] ?></p></div>
                    <div class="row"><span>Height</span><p><?= $information['height'] ?></p></div>
                    <div class="row"><span>Oxygen Saturation</span><p><?= $information['oxygen_saturation'] ?></p></div>
                </div>
            <?php else: ?>
                <p>No medical information recorded.</p>
            <?php endif; ?>
        </div>

    </div>

    <!-- ROW: Consultation Details -->
    <div class="card section">
        <h3>Consultation Details</h3>
        <?php if ($information): ?>
            <div class="record">
                <div class="row"><span>Service</span><p><?= $information['service'] ?></p></div>
                <div class="row"><span>Complaints</span><p><?= $information['complaints'] ?></p></div>
            </div>
        <?php endif; ?>
    </div>

    <!-- ROW: Prenatal Info -->
    <?php if ($appointment['type'] === 'prenatal' && $information): ?>
        <div class="card section">
            <h3>Prenatal Information</h3>
            <div class="record" style="display: flex; gap: 40px; flex-wrap: wrap;">

                <!-- LEFT COLUMN -->
                <div style="flex: 1; min-width: 220px;">
                    <div class="row"><span>LMP</span><p><?= $information['lmp'] ?></p></div>
                    <div class="row"><span>EDC</span><p><?= $information['edc'] ?></p></div>
                    <div class="row"><span>Gestational Age</span><p><?= $information['gestational_age'] ?></p></div>
                    <div class="row"><span>Bleeding</span><p><?= $information['bleeding'] ?></p></div>
                    <div class="row"><span>Urinary Infection</span><p><?= $information['urinary_infection'] ?></p></div>
                    <div class="row"><span>Discharge</span><p><?= $information['discharge'] ?></p></div>
                    <div class="row"><span>Abnormal Abdomen</span><p><?= $information['abnormal_abdomen'] ?></p></div>
                    <div class="row"><span>Malpresentation</span><p><?= $information['malpresentation'] ?></p></div>
                </div>

                <!-- RIGHT COLUMN -->
                <div style="flex: 1; min-width: 220px;">
                    <div class="row"><span>Absent Fetal Heartbeat</span><p><?= $information['absent_fetal_heartbeat'] ?></p></div>
                    <div class="row"><span>Genital Infection</span><p><?= $information['genital_infection'] ?></p></div>
                    <div class="row"><span>Fundal Height</span><p><?= $information['fundal_height'] ?></p></div>
                    <div class="row"><span>Fetal Movement Count</span><p><?= $information['fetal_movement_count'] ?></p></div>
                    <div class="row"><span>Weight Gain</span><p><?= $information['weight_gain'] ?></p></div>
                    <div class="row"><span>Edema</span><p><?= $information['edema'] ?></p></div>
                    <div class="row"><span>Blood Type</span><p><?= $information['blood_type'] ?></p></div>
                    <div class="row"><span>Hemoglobin Level</span><p><?= $information['hemoglobin_level'] ?></p></div>
                    <div class="row"><span>Urine Protein</span><p><?= $information['urine_protein'] ?></p></div>
                    <div class="row"><span>Blood Sugar</span><p><?= $information['blood_sugar'] ?></p></div>
                </div>

            </div>
        </div>
    <?php endif; ?>

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
