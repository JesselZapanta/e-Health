<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit();
}

$id = (int)$_GET['id'];

$q = mysqli_query(
    $conn,
    "SELECT 
        pr.visit_date,
        pr.blood_pressure,
        pr.weight,
        pr.fetal_heart_rate,
        pr.notes,
        pr.next_visit,
        u.full_name AS patient_name
     FROM prenatal_records pr
     JOIN patients p ON pr.patient_id = p.patient_id
     JOIN users u ON p.user_id = u.user_id
     WHERE pr.prenatal_id = $id"
);

$r = mysqli_fetch_assoc($q);
?>

<h3 style="margin-bottom:15px;">Prenatal Details</h3>

<div class="detail-item"><span>Patient:</span><?= htmlspecialchars($r['patient_name']) ?></div>
<div class="detail-item"><span>Visit Date:</span><?= $r['visit_date'] ?></div>
<div class="detail-item"><span>Next Visit:</span><?= $r['next_visit'] ?: '—' ?></div>

<hr style="margin:15px 0">

<div class="detail-item"><strong>Blood Pressure:</strong> <?= $r['blood_pressure'] ?: '—' ?></div>
<div class="detail-item"><strong>Weight (kg):</strong> <?= $r['weight'] ?: '—' ?></div>
<div class="detail-item"><strong>Fetal Heart Rate:</strong> <?= $r['fetal_heart_rate'] ?: '—' ?></div>

<hr style="margin:15px 0">

<div class="detail-item">
    <strong>Notes</strong><br>
    <?= nl2br(htmlspecialchars($r['notes'] ?: '—')) ?>
</div>
