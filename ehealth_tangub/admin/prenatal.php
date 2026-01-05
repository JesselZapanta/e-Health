<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* ================= LIST QUERY ================= */
$prenatals = mysqli_query($conn, "
    SELECT 
        pr.prenatal_id,
        u.full_name AS patient_name,
        pr.visit_date
    FROM prenatal_records pr
    JOIN patients p ON pr.patient_id = p.patient_id
    JOIN users u ON p.user_id = u.user_id
    ORDER BY pr.visit_date DESC
");

/* ================= VIEW LOGIC ================= */
$viewId   = $_GET['view'] ?? null;
$fullView = isset($_GET['full']) && $viewId;

$record = null;
if ($viewId) {
    $stmt = $conn->prepare("
        SELECT 
            u.full_name AS patient_name,
            pr.visit_date,
            pr.weight,
            pr.blood_pressure,
            pr.notes
        FROM prenatal_records pr
        JOIN patients p ON pr.patient_id = p.patient_id
        JOIN users u ON p.user_id = u.user_id
        WHERE pr.prenatal_id = ?
    ");
    $stmt->bind_param("i", $viewId);
    $stmt->execute();
    $record = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Prenatal Records | eHEALTH</title>
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
    <h2>Prenatal Records</h2>
    <p style="color:#64748b;font-size:14px;">
        Prenatal visit history (view-only)
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
    <th>Visit Date</th>
    <th style="width:120px;">Action</th>
</tr>
</thead>
<tbody>
<?php if (mysqli_num_rows($prenatals) === 0): ?>
<tr>
    <td colspan="3" style="text-align:center;color:#64748b;">
        No prenatal records found.
    </td>
</tr>
<?php endif; ?>

<?php while ($p = mysqli_fetch_assoc($prenatals)): ?>
<tr>
    <td><?= htmlspecialchars($p['patient_name']) ?></td>
    <td><?= date("M d, Y", strtotime($p['visit_date'])) ?></td>
    <td>
        <a href="?view=<?= $p['prenatal_id'] ?>" class="btn btn-success btn-sm">
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
<?php if ($record): ?>
<div class="card">
<div class="detail-header">
    <strong>Prenatal Visit Details</strong>
    <div class="actions">
        <?php if (!$fullView): ?>
            <a href="?view=<?= $viewId ?>&full=1" class="btn btn-primary btn-sm">
                ⛶ Full View
            </a>
        <?php endif; ?>
        <a href="prenatal.php" class="btn btn-danger btn-sm">
            ← Back
        </a>
    </div>
</div>

<div class="detail-grid">
    <div class="detail-box">
        <strong>Patient</strong><br>
        <?= htmlspecialchars($record['patient_name']) ?>
    </div>

    <div class="detail-box">
        <strong>Visit Date</strong><br>
        <?= date("M d, Y", strtotime($record['visit_date'])) ?>
    </div>

    <div class="detail-box">
        <strong>Weight</strong><br>
        <?= $record['weight'] ?: '—' ?> kg
    </div>

    <div class="detail-box">
        <strong>Blood Pressure</strong><br>
        <?= $record['blood_pressure'] ?: '—' ?>
    </div>

    <div class="detail-box full">
        <strong>Notes</strong><br>
        <?= nl2br($record['notes'] ?: '—') ?>
    </div>
</div>
</div>
<?php endif; ?>

</div>

</main>
</div>

</body>
</html>
