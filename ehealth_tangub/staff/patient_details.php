<?php
require_once "../config/database.php";

/* ================================
   ACCESS CONTROL
================================ */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    exit("Unauthorized");
}

if (!isset($_GET['id'])) {
    exit("No patient selected");
}

$patient_id = (int) $_GET['id'];

/* ================================
   FETCH PATIENT DETAILS
================================ */
$q = mysqli_query(
    $conn,
    "SELECT 
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
     LIMIT 1"
);

if (mysqli_num_rows($q) === 0) {
    exit("Patient not found");
}

$p = mysqli_fetch_assoc($q);
?>

<h4 style="margin-bottom:15px;">Patient Details</h4>

<div class="detail-item">
    <span>Name:</span> <?= htmlspecialchars($p['full_name']) ?>
</div>

<div class="detail-item">
    <span>Email:</span> <?= htmlspecialchars($p['email']) ?>
</div>

<div class="detail-item">
    <span>Status:</span>
    <strong style="color:<?= $p['status'] === 'active' ? '#16a34a' : '#dc2626' ?>">
        <?= ucfirst($p['status']) ?>
    </strong>
</div>

<hr style="margin:15px 0">

<div class="detail-item">
    <span>Gender:</span> <?= htmlspecialchars($p['gender']) ?>
</div>

<div class="detail-item">
    <span>Birth Date:</span> <?= htmlspecialchars($p['birth_date']) ?>
</div>

<div class="detail-item">
    <span>Address:</span> <?= htmlspecialchars($p['address']) ?>
</div>

<div class="detail-item">
    <span>Contact No:</span> <?= htmlspecialchars($p['contact_number']) ?>
</div>

<div class="detail-item">
    <span>Blood Type:</span> <?= htmlspecialchars($p['blood_type']) ?>
</div>

<div class="detail-item">
    <span>Pregnant:</span>
    <?= $p['is_pregnant'] ? 'Yes' : 'No' ?>
</div>

<div class="detail-item">
    <span>Medical History:</span><br>
    <?= nl2br(htmlspecialchars($p['medical_history'])) ?>
</div>
