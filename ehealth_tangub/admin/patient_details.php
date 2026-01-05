<?php
require_once "../config/database.php";

$id = (int)$_GET['id'];

$q = mysqli_query(
    $conn,
    "SELECT u.full_name, u.email,
            p.gender, p.address, p.contact_number,
            p.blood_type, p.height, p.weight,
            p.medical_history, p.is_pregnant
     FROM patients p
     JOIN users u ON p.user_id = u.user_id
     WHERE p.patient_id = $id"
);

$patient = mysqli_fetch_assoc($q);
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
    <h3>Patient Information</h3>
    <div>
        <button class="btn btn-sm" onclick="fullView()">⛶</button>
        <button class="btn btn-sm" onclick="splitView()">🔀</button>
        <button class="btn btn-danger btn-sm" onclick="backToList()">Back</button>
    </div>
</div>

<div class="section">
<h4>Basic Information</h4>
<p><strong>Name:</strong> <?= htmlspecialchars($patient['full_name']) ?></p>
<p><strong>Email:</strong> <?= htmlspecialchars($patient['email']) ?></p>
<p><strong>Gender:</strong> <?= $patient['gender'] ?: '—' ?></p>
<p><strong>Contact:</strong> <?= $patient['contact_number'] ?: '—' ?></p>
<p><strong>Address:</strong> <?= $patient['address'] ?: '—' ?></p>
</div>

<div class="section">
<h4>Health Summary</h4>
<p><strong>Blood Type:</strong> <?= $patient['blood_type'] ?: '—' ?></p>
<p><strong>Height:</strong> <?= $patient['height'] ?: '—' ?> cm</p>
<p><strong>Weight:</strong> <?= $patient['weight'] ?: '—' ?> kg</p>
<p><strong>Pregnant:</strong> <?= $patient['is_pregnant'] ? 'Yes' : 'No' ?></p>
</div>

<div class="section">
<h4>Medical History</h4>
<p><?= nl2br(htmlspecialchars($patient['medical_history'] ?: 'No records available.')) ?></p>
</div>
