<?php
require_once "../config/database.php";

/* ================================
   ACCESS CONTROL
================================ */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

/* ================================
   FETCH PATIENT LIST
================================ */
$patients = mysqli_query(
    $conn,
    "SELECT 
        p.patient_id,
        u.full_name,
        u.email,
        u.status
     FROM patients p
     JOIN users u ON p.user_id = u.user_id
     ORDER BY u.full_name ASC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Patient Records | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">

    <style>
        .layout { display: flex; }
        .main { flex: 1; padding: 25px; }

        /* SPLIT VIEW */
        .split-view {
            display: grid;
            grid-template-columns: 1fr 1.3fr;
            gap: 20px;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
            text-align: left;
        }

        th {
            background: #f8fafc;
        }

        .status.active { color: #16a34a; font-weight: bold; }
        .status.inactive { color: #dc2626; font-weight: bold; }

        .btn-view {
            padding: 6px 10px;
            background: var(--primary);
            color: #fff;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            border: none;
        }

        .btn-view:hover {
            background: var(--primary-dark);
        }

        /* DETAILS PANEL */
        .details-empty {
            text-align: center;
            color: #666;
            padding-top: 80px;
        }

        .detail-item {
            margin-bottom: 10px;
        }

        .detail-item span {
            font-weight: bold;
            display: inline-block;
            width: 140px;
        }
    </style>
</head>
<body>

<div class="layout">
    <?php require_once "../layouts/sidebar.php"; ?>

    <main class="main">
        <?php require_once "../layouts/topbar.php"; ?>

        <h3 style="margin-bottom:15px;">Patient Records</h3>

        <div class="split-view">

            <!-- LEFT: PATIENT LIST -->
            <div class="card">
                <table>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>

                    <?php while ($p = mysqli_fetch_assoc($patients)): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['full_name']) ?></td>
                        <td><?= htmlspecialchars($p['email']) ?></td>
                        <td class="status <?= $p['status'] ?>">
                            <?= ucfirst($p['status']) ?>
                        </td>
                        <td>
                            <button class="btn-view"
                                onclick="loadPatient(<?= $p['patient_id'] ?>)">
                                View
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>

            <!-- RIGHT: PATIENT DETAILS -->
            <div class="card" id="detailsPanel">
                <div class="details-empty">
                    Select a patient to view details
                </div>
            </div>

        </div>
    </main>
</div>

<script>
function loadPatient(id) {
    fetch("patient_details.php?id=" + id)
        .then(res => res.text())
        .then(html => {
            document.getElementById("detailsPanel").innerHTML = html;
        });
}
</script>

</body>
</html>
