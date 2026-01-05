<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* ================= LIST QUERY ================= */
$consultations = mysqli_query($conn, "
    SELECT 
        c.consultation_id,
        u.full_name AS patient_name,
        d.full_name AS doctor_name,
        a.appointment_date,
        a.appointment_time
    FROM consultations c
    JOIN appointments a ON c.appointment_id = a.appointment_id
    JOIN users u ON a.patient_id = u.user_id
    JOIN users d ON a.doctor_id = d.user_id
    ORDER BY c.created_at DESC
");

/* ================= VIEW LOGIC ================= */
$viewId   = $_GET['view'] ?? null;
$fullView = isset($_GET['full']) && $viewId;

$consultation = null;
if ($viewId) {
    $stmt = $conn->prepare("
        SELECT 
            u.full_name AS patient_name,
            d.full_name AS doctor_name,
            a.appointment_date,
            a.appointment_time,
            c.symptoms,
            c.diagnosis,
            c.prescription,
            c.notes
        FROM consultations c
        JOIN appointments a ON c.appointment_id = a.appointment_id
        JOIN users u ON a.patient_id = u.user_id
        JOIN users d ON a.doctor_id = d.user_id
        WHERE c.consultation_id = ?
    ");
    $stmt->bind_param("i", $viewId);
    $stmt->execute();
    $consultation = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Consultation Records | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">
    <style>
        .page-header {
            margin-bottom:20px;
        }

        .card {
            background:#fff;
            padding:20px;
            border-radius:14px;
            box-shadow:var(--shadow);
        }

        .split {
            display:grid;
            grid-template-columns: <?= $viewId && !$fullView ? '1.2fr 1fr' : '1fr' ?>;
            gap:20px;
        }

        table {
            width:100%;
            border-collapse:collapse;
        }

        th, td {
            padding:14px;
            border-bottom:1px solid #eee;
            text-align:left;
            font-size:14px;
        }

        th {
            background:#f8fafc;
        }

        .detail-header {
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:15px;
        }

        .detail-grid {
            display:grid;
            grid-template-columns: repeat(2, 1fr);
            gap:15px;
            font-size:14px;
        }

        .detail-box {
            background:#f8fafc;
            padding:12px;
            border-radius:8px;
            line-height:1.5;
        }

        .detail-box.full {
            grid-column:1 / -1;
        }

        .actions {
            display:flex;
            gap:10px;
        }
    </style>
</head>
<body>

<div class="layout">
<?php require_once "../layouts/sidebar.php"; ?>

<main class="main">
<?php require_once "../layouts/topbar.php"; ?>

<div class="page-header">
    <h2>Consultation Records</h2>
    <p style="color:#64748b;font-size:14px;">
        Completed consultations overview
    </p>
</div>

<div class="split">

<!-- ================= LEFT: TABLE ================= -->
<?php if (!$fullView): ?>
<div class="card">
<table>
<thead>
<tr>
    <th>Patient</th>
    <th>Doctor</th>
    <th>Date</th>
    <th style="width:120px;">Action</th>
</tr>
</thead>
<tbody>
<?php if (mysqli_num_rows($consultations) === 0): ?>
<tr>
    <td colspan="4" style="text-align:center;color:#64748b;">
        No consultation records found.
    </td>
</tr>
<?php endif; ?>

<?php while ($c = mysqli_fetch_assoc($consultations)): ?>
<tr>
    <td><?= htmlspecialchars($c['patient_name']) ?></td>
    <td><?= htmlspecialchars($c['doctor_name']) ?></td>
    <td><?= date("M d, Y", strtotime($c['appointment_date'])) ?></td>
    <td>
        <a href="?view=<?= $c['consultation_id'] ?>" class="btn btn-success btn-sm">
            View
        </a>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
<?php endif; ?>

<!-- ================= RIGHT: DETAILS ================= -->
<?php if ($consultation): ?>
<div class="card">
<div class="detail-header">
    <strong>Consultation Details</strong>
    <div class="actions">
        <?php if (!$fullView): ?>
            <a href="?view=<?= $viewId ?>&full=1" class="btn btn-primary btn-sm">
                ⛶ Full View
            </a>
        <?php endif; ?>
        <a href="consultations.php" class="btn btn-danger btn-sm">
            ← Back
        </a>
    </div>
</div>

<div class="detail-grid">
    <div class="detail-box"><strong>Patient</strong><br><?= $consultation['patient_name'] ?></div>
    <div class="detail-box"><strong>Doctor</strong><br><?= $consultation['doctor_name'] ?></div>
    <div class="detail-box"><strong>Date</strong><br><?= $consultation['appointment_date'] ?></div>
    <div class="detail-box"><strong>Time</strong><br><?= $consultation['appointment_time'] ?></div>

    <div class="detail-box full"><strong>Symptoms</strong><br><?= nl2br($consultation['symptoms'] ?? '—') ?></div>
    <div class="detail-box full"><strong>Diagnosis</strong><br><?= nl2br($consultation['diagnosis'] ?? '—') ?></div>
    <div class="detail-box full"><strong>Prescription</strong><br><?= nl2br($consultation['prescription'] ?? '—') ?></div>
    <div class="detail-box full"><strong>Notes</strong><br><?= nl2br($consultation['notes'] ?? '—') ?></div>
</div>
</div>
<?php endif; ?>

</div>

</main>
</div>

</body>
</html>
