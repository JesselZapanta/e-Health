<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

/* ================================
   FETCH PRENATAL RECORDS
================================ */
$records = mysqli_query(
    $conn,
    "SELECT 
        pr.prenatal_id,
        pr.visit_date,
        u.full_name AS patient_name
     FROM prenatal_records pr
     JOIN patients p ON pr.patient_id = p.patient_id
     JOIN users u ON p.user_id = u.user_id
     ORDER BY pr.visit_date DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Prenatal Records | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">
    <style>
        .layout { display: flex; }
        .main { flex: 1; padding: 25px; }

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

        .btn-view {
            padding: 6px 10px;
            background: var(--primary);
            color: #fff;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            border: none;
        }

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
            width: 160px;
        }
    </style>
</head>
<body>

<div class="layout">
    <?php require_once "../layouts/sidebar.php"; ?>

    <main class="main">
        <?php require_once "../layouts/topbar.php"; ?>

        <h3 style="margin-bottom:15px;">Prenatal Records</h3>

        <div class="split-view">

            <!-- LEFT: RECORD LIST -->
            <div class="card">
                <table>
                    <tr>
                        <th>Visit Date</th>
                        <th>Patient</th>
                        <th>Action</th>
                    </tr>

                    <?php while ($r = mysqli_fetch_assoc($records)): ?>
                    <tr>
                        <td><?= date("M d, Y", strtotime($r['visit_date'])) ?></td>
                        <td><?= htmlspecialchars($r['patient_name']) ?></td>
                        <td>
                            <button class="btn-view"
                                onclick="loadPrenatal(<?= $r['prenatal_id'] ?>)">
                                View
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>

            <!-- RIGHT: DETAILS -->
            <div class="card" id="detailsPanel">
                <div class="details-empty">
                    Select a prenatal visit to view details
                </div>
            </div>

        </div>
    </main>
</div>

<script>
function loadPrenatal(id) {
    fetch("prenatal_details.php?id=" + id)
        .then(res => res.text())
        .then(html => {
            document.getElementById("detailsPanel").innerHTML = html;
        });
}
</script>

</body>
</html>
