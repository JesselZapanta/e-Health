<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
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

<h4 style="margin-bottom:10px;">Prenatal Visit Details</h4>

<div class="detail-item"><span>Patient:</span> <?= htmlspecialchars($r['patient_name']) ?></div>
<div class="detail-item"><span>Visit Date:</span> <?= date("M d, Y", strtotime($r['visit_date'])) ?></div>
<div class="detail-item"><span>Blood Pressure:</span> <?= $r['blood_pressure'] ?: '-' ?></div>
<div class="detail-item"><span>Weight:</span> <?= $r['weight'] ?: '-' ?></div>
<div class="detail-item"><span>Fetal Heart Rate:</span> <?= $r['fetal_heart_rate'] ?: '-' ?></div>

<hr style="margin:15px 0">

<div class="detail-item"><span>Notes:</span><br><?= nl2br(htmlspecialchars($r['notes'])) ?></div>

<?php if ($r['next_visit']): ?>
<hr style="margin:15px 0">
<div class="detail-item"><span>Next Visit:</span> <?= date("M d, Y", strtotime($r['next_visit'])) ?></div>
<?php endif; ?>
