<?php
require_once "../config/database.php";

/* ================================
   ACCESS CONTROL
================================ */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    exit("Unauthorized");
}

if (!isset($_GET['id'])) {
    exit("No patient selected");
}

$patient_id = (int) $_GET['id'];

/* ================================
   FETCH PATIENT BASIC DETAILS
================================ */
$patientQuery = mysqli_query(
    $conn,
    "
    SELECT 
        p.patient_id,
        u.full_name,
        u.email,
        u.status,
        p.gender,
        p.birth_date,
        p.address,
        p.contact_number,
        p.blood_type,
        p.medical_history,
        p.is_pregnant
    FROM patients p
    JOIN users u ON p.user_id = u.user_id
    WHERE p.patient_id = $patient_id
    LIMIT 1
    "
);

if (mysqli_num_rows($patientQuery) === 0) {
    exit("Patient not found");
}

$patient = mysqli_fetch_assoc($patientQuery);

/* ================================
   FETCH COMPLETED APPOINTMENTS
================================ */
$appointments = mysqli_query(
    $conn,
    "
    SELECT
        a.appointment_id,
        a.appointment_date,
        a.type AS appointment_type,

        i.blood_pressure,
        i.temperature,
        i.heart_rate,
        i.respiratory_rate,
        i.weight,
        i.height,
        i.oxygen_saturation,

        i.lmp,
        i.edc,
        i.gestational_age,
        i.bleeding,
        i.urinary_infection,
        i.discharge,
        i.abnormal_abdomen,
        i.malpresentation,
        i.absent_fetal_heartbeat,
        i.genital_infection,
        i.fundal_height,
        i.fetal_movement_count,
        i.weight_gain,
        i.edema,
        i.blood_type AS prenatal_blood_type,
        i.hemoglobin_level,
        i.urine_protein,
        i.blood_sugar,

        c.symptoms,
        c.diagnosis,
        c.prescription,
        c.notes,
        c.created_at AS consultation_date

    FROM appointments a
    LEFT JOIN informations i ON i.appointment_id = a.appointment_id
    LEFT JOIN consultations c ON c.appointment_id = a.appointment_id
    WHERE a.patient_id = $patient_id
      AND a.status = 'Completed'
    ORDER BY a.appointment_date DESC
    "
);
?>

<style>
.card {
    background: #fff;
    padding: 20px;
    border-radius: 14px;
    box-shadow: var(--shadow, 0 4px 12px rgba(0,0,0,.06));
    margin-bottom: 15px;
}

.row {
    display: flex;
    justify-content: space-between;
    padding: 6px 0;
}

.row span {
    font-weight: 600;
    color: #555;
    min-width: 180px;
}

.row p {
    margin: 0;
    text-align: right;
}

/* ================================
   SIMPLE VERTICAL LINE
================================ */
.card-wrap {
    position: relative;
    padding-left: 25px;
    margin-top: 15px;
}

.card-wrap::before {
    content: "";
    position: absolute;
    left: 8px;
    top: 0;
    bottom: 0;
    width: 3px;
    background: #117336ff;
    border-radius: 10px;
}

.card-wrap .card {
    margin-left: 15px;
}

.prenatal {
    display: flex;
    gap: 40px;
    flex-wrap: wrap;
}

.prenatal div {
    flex: 1;
    min-width: 220px;
}

.col {
    margin-bottom: 15px;
}
</style>

<h3>Patient Details</h3>

<div class="card-wrap">
    <div class="card">
        <div class="row"><span>Name</span><p><?= htmlspecialchars($patient['full_name']) ?></p></div>
        <div class="row"><span>Email</span><p><?= htmlspecialchars($patient['email']) ?></p></div>
        <div class="row">
            <span>Status</span>
            <p style="color:<?= $patient['status'] === 'active' ? '#16a34a' : '#dc2626' ?>">
                <?= ucfirst($patient['status']) ?>
            </p>
        </div>
        <div class="row"><span>Gender</span><p><?= $patient['gender'] ?: '—' ?></p></div>
        <div class="row"><span>Birth Date</span><p><?= $patient['birth_date'] ?: '—' ?></p></div>
        <div class="row"><span>Address</span><p><?= $patient['address'] ?: '—' ?></p></div>
        <div class="row"><span>Contact No</span><p><?= $patient['contact_number'] ?: '—' ?></p></div>
        <div class="row"><span>Blood Type</span><p><?= $patient['blood_type'] ?: '—' ?></p></div>
        <div class="row"><span>Pregnant</span><p><?= $patient['is_pregnant'] ? 'Yes' : 'No' ?></p></div>
    </div>
</div>


<h3>Completed Appointments</h3>

<?php if (mysqli_num_rows($appointments) > 0): ?>
<div class="card-wrap">

<?php while ($row = mysqli_fetch_assoc($appointments)): ?>

<div class="card">
    <h4>Medical Information (<?= $row['appointment_date'] ?>)</h4>
    <div class="row"><span>Blood Pressure</span><p><?= $row['blood_pressure'] ?? '—' ?></p></div>
    <div class="row"><span>Temperature</span><p><?= $row['temperature'] ?? '—' ?></p></div>
    <div class="row"><span>Heart Rate</span><p><?= $row['heart_rate'] ?? '—' ?></p></div>
    <div class="row"><span>Respiratory Rate</span><p><?= $row['respiratory_rate'] ?? '—' ?></p></div>
    <div class="row"><span>Weight</span><p><?= $row['weight'] ?? '—' ?></p></div>
    <div class="row"><span>Height</span><p><?= $row['height'] ?? '—' ?></p></div>
    <div class="row"><span>Oxygen Saturation</span><p><?= $row['oxygen_saturation'] ?? '—' ?></p></div>
</div>

<?php if ($row['appointment_type'] === 'prenatal'): ?>
<div class="card">
    <h4>Prenatal Information</h4>
    <div class="prenatal">
    <div>
        <div class="row"><span>LMP</span><p><?= $row['lmp'] ?? '—' ?></p></div>
        <div class="row"><span>EDC</span><p><?= $row['edc'] ?? '—' ?></p></div>
        <div class="row"><span>Gestational Age</span><p><?= $row['gestational_age'] ?? '—' ?></p></div>
        <div class="row"><span>Bleeding</span><p><?= $row['bleeding'] ?? '—' ?></p></div>
        <div class="row"><span>Urinary Infection</span><p><?= $row['urinary_infection'] ?? '—' ?></p></div>
        <div class="row"><span>Discharge</span><p><?= $row['discharge'] ?? '—' ?></p></div>
        <div class="row"><span>Abnormal Abdomen</span><p><?= $row['abnormal_abdomen'] ?? '—' ?></p></div>
    </div>
    <div>
        <div class="row"><span>Malpresentation</span><p><?= $row['malpresentation'] ?? '—' ?></p></div>
        <div class="row"><span>Absent Fetal Heartbeat</span><p><?= $row['absent_fetal_heartbeat'] ?? '—' ?></p></div>
        <div class="row"><span>Genital Infection</span><p><?= $row['genital_infection'] ?? '—' ?></p></div>
        <div class="row"><span>Fundal Height</span><p><?= $row['fundal_height'] ?? '—' ?></p></div>
        <div class="row"><span>Fetal Movement Count</span><p><?= $row['fetal_movement_count'] ?? '—' ?></p></div>
        <div class="row"><span>Weight Gain</span><p><?= $row['weight_gain'] ?? '—' ?></p></div>
        <div class="row"><span>Edema</span><p><?= $row['edema'] ?? '—' ?></p></div>
    </div>
    <div>
        <div class="row"><span>Blood Type</span><p><?= $row['prenatal_blood_type'] ?? '—' ?></p></div>
        <div class="row"><span>Hemoglobin Level</span><p><?= $row['hemoglobin_level'] ?? '—' ?></p></div>
        <div class="row"><span>Urine Protein</span><p><?= $row['urine_protein'] ?? '—' ?></p></div>
        <div class="row"><span>Blood Sugar</span><p><?= $row['blood_sugar'] ?? '—' ?></p></div>
    </div>
</div>

</div>
<?php endif; ?>

<div class="card">
    <h4>Consultation</h4>

    <div class="col">
        <span>Date</span>
        <p><?= $row['consultation_date'] ?? '—' ?></p>
    </div>

    <div class="col">
        <span>Symptoms</span>
        <p><?= nl2br(htmlspecialchars($row['symptoms'] ?? '—')) ?></p>
    </div>

    <div class="col">
        <span>Diagnosis</span>
        <p><?= nl2br(htmlspecialchars($row['diagnosis'] ?? '—')) ?></p>
    </div>

    <div class="col">
        <span>Treatment</span>
        <p><?= nl2br(htmlspecialchars($row['prescription'] ?? '—')) ?></p>
    </div>

    <div class="col">
        <span>Notes</span>
        <p><?= nl2br(htmlspecialchars($row['notes'] ?? '—')) ?></p>
    </div>
</div>



<?php endwhile; ?>
</div>

<?php else: ?>
<div class="card">No completed appointments found.</div>
<?php endif; ?>
