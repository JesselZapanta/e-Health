<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit();
}

$id = (int)$_GET['id'];

$q = mysqli_query(
    $conn,
    "SELECT 
        DATE(c.created_at) AS consultation_date,
        c.symptoms,
        c.diagnosis,
        c.prescription,
        c.notes,
        up.full_name AS patient_name,
        ud.full_name AS doctor_name
     FROM consultations c
     JOIN appointments a ON c.appointment_id = a.appointment_id
     JOIN patients p ON a.patient_id = p.patient_id
     JOIN users up ON p.user_id = up.user_id
     JOIN users ud ON a.doctor_id = ud.user_id
     WHERE c.consultation_id = $id"
);

$c = mysqli_fetch_assoc($q);
?>

<h3 style="margin-bottom:15px;">Consultation Details</h3>

<div class="detail-item"><span>Date:</span><?= $c['consultation_date'] ?></div>
<div class="detail-item"><span>Patient:</span><?= htmlspecialchars($c['patient_name']) ?></div>
<div class="detail-item"><span>Doctor:</span><?= htmlspecialchars($c['doctor_name']) ?></div>

<hr style="margin:15px 0">

<div class="detail-item">
    <strong>Symptoms</strong><br>
    <?= nl2br(htmlspecialchars($c['symptoms'])) ?>
</div>

<div class="detail-item">
    <strong>Diagnosis</strong><br>
    <?= nl2br(htmlspecialchars($c['diagnosis'])) ?>
</div>

<div class="detail-item">
    <strong>Prescription</strong><br>
    <?= nl2br(htmlspecialchars($c['prescription'])) ?>
</div>

<div class="detail-item">
    <strong>Notes</strong><br>
    <?= nl2br(htmlspecialchars($c['notes'] ?: '—')) ?>
</div>
