<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}


if (!isset($_GET['id'])) {
    header("Location: consultations.php");
    exit();
}

$consultation_id = (int) $_GET['id'];

/* ================================
   FETCH CONSULTATION
================================ */
//get the doctors name from appointments table and users table
$consultation = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT 
            c.*,
            a.appointment_id,
            a.appointment_date,
            a.appointment_time,
            a.type AS appointment_type,
            u.full_name AS patient_name,
            d.full_name AS doctor_name,
            p.patient_id
         FROM consultations c
         JOIN appointments a ON c.appointment_id = a.appointment_id
         JOIN patients p ON a.patient_id = p.patient_id
         JOIN users u ON p.user_id = u.user_id
         JOIN users d ON a.doctor_id = d.user_id
         WHERE c.consultation_id = $consultation_id
         LIMIT 1"
    )
);


if (!$consultation) {
    die("Consultation not found.");
}

/* ================================
   FETCH MEDICAL INFORMATION
================================ */
$information = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT *
         FROM informations
         WHERE patients_id = {$consultation['patient_id']}
           AND appointment_id = {$consultation['appointment_id']}
         LIMIT 1"
    )
);
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
        h2, h3, h4 { margin-bottom: 10px; }
        .row { display: flex; justify-content: space-between; padding: 6px 0; }
        .row span { font-weight: 600; color: #555; min-width: 160px; }
        .row p { margin: 0; text-align: right; color: #111; }
        .prenatal { display: flex; gap: 40px; flex-wrap: wrap; margin-top: 10px; }
        .prenatal div { flex: 1; min-width: 220px; }
    </style>
</head>
<body>

<div class="layout">
<?php require_once "../layouts/sidebar.php"; ?>

<main class="main">
<?php require_once "../layouts/topbar.php"; ?>

<h2>Consultation Details</h2>

<!-- Patient Info -->
<!-- Patient Info -->
<div class="card">
    <p><strong>Patient:</strong> <?= htmlspecialchars($consultation['patient_name']) ?></p>
    <p><strong>Doctor:</strong> <?= htmlspecialchars($consultation['doctor_name']) ?></p>
    <p><strong>Date:</strong> <?= $consultation['appointment_date'] ?></p>
    <p><strong>Time:</strong> <?= $consultation['appointment_time'] ?></p>
    <p><strong>Appointment Type:</strong> <?= ucfirst($consultation['appointment_type']) ?></p>
</div>


<!-- Medical Information -->
<?php if ($information): ?>
<div class="card">
    <h3>Medical Information</h3>
    <div class="row"><span>Blood Pressure</span><p><?= $information['blood_pressure'] ?></p></div>
    <div class="row"><span>Temperature</span><p><?= $information['temperature'] ?></p></div>
    <div class="row"><span>Heart Rate</span><p><?= $information['heart_rate'] ?></p></div>
    <div class="row"><span>Respiratory Rate</span><p><?= $information['respiratory_rate'] ?></p></div>
    <div class="row"><span>Weight</span><p><?= $information['weight'] ?></p></div>
    <div class="row"><span>Height</span><p><?= $information['height'] ?></p></div>
    <div class="row"><span>Oxygen Saturation</span><p><?= $information['oxygen_saturation'] ?></p></div>
</div>

<?php if ($consultation['appointment_type'] === 'prenatal'): ?>
<div class="card">
    <h3>Prenatal Information</h3>
    <div class="prenatal">
        <div>
            <div class="row"><span>LMP</span><p><?= $information['lmp'] ?></p></div>
            <div class="row"><span>EDC</span><p><?= $information['edc'] ?></p></div>
            <div class="row"><span>Gestational Age</span><p><?= $information['gestational_age'] ?></p></div>
            <div class="row"><span>Bleeding</span><p><?= $information['bleeding'] ?></p></div>
            <div class="row"><span>Urinary Infection</span><p><?= $information['urinary_infection'] ?></p></div>
            <div class="row"><span>Discharge</span><p><?= $information['discharge'] ?></p></div>
            <div class="row"><span>Abnormal Abdomen</span><p><?= $information['abnormal_abdomen'] ?></p></div>
            <div class="row"><span>Malpresentation</span><p><?= $information['malpresentation'] ?></p></div>
        </div>
        <div>
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
<?php endif; ?>

<!-- Consultation Details -->
<div class="card">
    <h3>Consultation</h3>

    <div style="margin-bottom:12px;">
        <span style="font-weight:bold;">Symptoms</span>
        <p style="margin:4px 0 0 0;"><?= nl2br(htmlspecialchars($consultation['symptoms'])) ?></p>
    </div>

    <div style="margin-bottom:12px;">
        <span style="font-weight:bold;">Diagnosis</span>
        <p style="margin:4px 0 0 0;"><?= nl2br(htmlspecialchars($consultation['diagnosis'])) ?></p>
    </div>

    <div style="margin-bottom:12px;">
        <span style="font-weight:bold;">Prescription</span>
        <p style="margin:4px 0 0 0;"><?= nl2br(htmlspecialchars($consultation['prescription'])) ?></p>
    </div>

    <div style="margin-bottom:12px;">
        <span style="font-weight:bold;">Notes</span>
        <p style="margin:4px 0 0 0;"><?= nl2br(htmlspecialchars($consultation['notes'])) ?></p>
    </div>
</div>


<a href="consultations.php" class="btn btn-primary">Back to History</a>

</main>
</div>

</body>
</html>
