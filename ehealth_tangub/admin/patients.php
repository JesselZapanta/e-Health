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

        .form-group input {
            width:100%;
            padding:8px 10px;
            border-radius:6px;
            border:1px solid #ccc;
            font-size:14px;
            margin-bottom:10px;
        }

        .btn {
            padding:6px 12px;
            border-radius:6px;
            text-decoration:none;
            font-size:14px;
            cursor:pointer;
        }

        .btn-sm {
            padding:4px 8px;
            font-size:12px;
        }

        .btn-success {
            background:#22c55e;
            color:#fff;
            border:none;
        }

        .btn-danger {
            background:#ef4444;
            color:#fff;
            border:none;
        }

        .btn-primary {
            background:#3b82f6;
            color:#fff;
            border:none;
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
    <div class="form-group">
        <label>Search patient</label>
        <input type="text" id="searchInput" placeholder="Type name or email..." />
    </div>
    <table id="patientTable">
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
        <td class="name"><?= htmlspecialchars($p['full_name']) ?></td>
        <td class="email"><?= htmlspecialchars($p['email']) ?></td>
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

<script>
const searchInput = document.getElementById('searchInput');
const tableBody = document.querySelector('#patientTable tbody');

searchInput.addEventListener('input', function() {
    const filter = searchInput.value.toLowerCase();

    let anyVisible = false;

    for (let row of tableBody.rows) {
        const name = row.querySelector('.name').textContent.toLowerCase();
        const email = row.querySelector('.email').textContent.toLowerCase();

        if (name.includes(filter) || email.includes(filter)) {
            row.style.display = '';
            anyVisible = true;
        } else {
            row.style.display = 'none';
        }
    }

    // Handle no data found row
    let noDataRow = tableBody.querySelector('.no-data-row');
    if (!noDataRow) {
        noDataRow = document.createElement('tr');
        noDataRow.classList.add('no-data-row');
        noDataRow.innerHTML = '<td colspan="3" style="text-align:center;color:#64748b;">No patients found.</td>';
        tableBody.appendChild(noDataRow);
    }
    noDataRow.style.display = anyVisible ? 'none' : '';
});
</script>
</body>
</html>
