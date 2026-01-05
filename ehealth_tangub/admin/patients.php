<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$patients = mysqli_query($conn, "
    SELECT u.user_id, u.full_name, u.email
    FROM users u
    WHERE u.role = 'patient'
    ORDER BY u.created_at DESC
");

$viewId = $_GET['view'] ?? null;
$fullView = isset($_GET['full']) && $viewId;

$patient = null;
if ($viewId) {
    $stmt = $conn->prepare("
        SELECT u.full_name, u.email, p.gender, p.birth_date, p.blood_type, p.medical_history
        FROM users u
        LEFT JOIN patients p ON u.user_id = p.user_id
        WHERE u.user_id = ?
    ");
    $stmt->bind_param("i", $viewId);
    $stmt->execute();
    $patient = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Patient Records | eHEALTH</title>
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
            grid-template-columns: <?= $viewId && !$fullView ? '1.1fr 1fr' : '1fr' ?>;
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
    <h2>Patient Records</h2>
    <p style="color:#64748b;font-size:14px;">Registered patients overview</p>
</div>

<div class="split">

<!-- ================= LEFT: PATIENT LIST ================= -->
<?php if (!$fullView): ?>
<div class="card">
<table>
<thead>
<tr>
    <th>Name</th>
    <th>Email</th>
    <th style="width:120px;">Action</th>
</tr>
</thead>
<tbody>
<?php if (mysqli_num_rows($patients) === 0): ?>
<tr>
    <td colspan="3" style="text-align:center;color:#64748b;">
        No patients found.
    </td>
</tr>
<?php endif; ?>

<?php while ($p = mysqli_fetch_assoc($patients)): ?>
<tr>
    <td><?= htmlspecialchars($p['full_name']) ?></td>
    <td><?= htmlspecialchars($p['email']) ?></td>
    <td>
        <a href="?view=<?= $p['user_id'] ?>" class="btn btn-success btn-sm">View</a>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
<?php endif; ?>

<!-- ================= RIGHT: PATIENT DETAILS ================= -->
<?php if ($patient): ?>
<div class="card">
<div class="detail-header">
    <strong>Patient Information</strong>
    <div class="actions">
        <?php if (!$fullView): ?>
            <a href="?view=<?= $viewId ?>&full=1" class="btn btn-primary btn-sm">⛶ Full View</a>
        <?php endif; ?>
        <a href="patients.php" class="btn btn-danger btn-sm">← Back</a>
    </div>
</div>

<div class="detail-grid">
    <div class="detail-box"><strong>Name</strong><br><?= htmlspecialchars($patient['full_name']) ?></div>
    <div class="detail-box"><strong>Email</strong><br><?= htmlspecialchars($patient['email']) ?></div>
    <div class="detail-box"><strong>Gender</strong><br><?= $patient['gender'] ?? '—' ?></div>
    <div class="detail-box"><strong>Birth Date</strong><br><?= $patient['birth_date'] ?? '—' ?></div>
    <div class="detail-box"><strong>Blood Type</strong><br><?= $patient['blood_type'] ?? '—' ?></div>
    <div class="detail-box"><strong>Medical History</strong><br><?= nl2br($patient['medical_history'] ?? '—') ?></div>
</div>
</div>
<?php endif; ?>

</div>

</main>
</div>

</body>
</html>
