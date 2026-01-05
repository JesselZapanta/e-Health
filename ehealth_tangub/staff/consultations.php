<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

/* ================================
   FETCH CONSULTATION LIST
================================ */
$consultations = mysqli_query(
    $conn,
    "SELECT 
        c.consultation_id,
        c.created_at,
        u_patient.full_name AS patient_name,
        u_doctor.full_name AS doctor_name
     FROM consultations c
     JOIN appointments a ON c.appointment_id = a.appointment_id
     JOIN patients p ON a.patient_id = p.patient_id
     JOIN users u_patient ON p.user_id = u_patient.user_id
     JOIN users u_doctor ON a.doctor_id = u_doctor.user_id
     ORDER BY c.created_at DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Consultation Records | eHEALTH</title>
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
            width: 150px;
        }
    </style>
</head>
<body>

<div class="layout">
    <?php require_once "../layouts/sidebar.php"; ?>

    <main class="main">
        <?php require_once "../layouts/topbar.php"; ?>

        <h3 style="margin-bottom:15px;">Consultation Records</h3>

        <div class="split-view">

            <!-- LEFT: CONSULTATION LIST -->
            <div class="card">
                <table>
                    <tr>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Action</th>
                    </tr>

                    <?php while ($c = mysqli_fetch_assoc($consultations)): ?>
                    <tr>
                        <td><?= date("M d, Y", strtotime($c['created_at'])) ?></td>
                        <td><?= htmlspecialchars($c['patient_name']) ?></td>
                        <td><?= htmlspecialchars($c['doctor_name']) ?></td>
                        <td>
                            <button class="btn-view"
                                onclick="loadConsultation(<?= $c['consultation_id'] ?>)">
                                View
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>

            <!-- RIGHT: CONSULTATION DETAILS -->
            <div class="card" id="detailsPanel">
                <div class="details-empty">
                    Select a consultation to view details
                </div>
            </div>

        </div>
    </main>
</div>

<script>
function loadConsultation(id) {
    fetch("consultation_details.php?id=" + id)
        .then(res => res.text())
        .then(html => {
            document.getElementById("detailsPanel").innerHTML = html;
        });
}
</script>

</body>
</html>
