<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];

/* ================================
   FETCH CONSULTATION HISTORY
================================ */
$consultations = mysqli_query(
    $conn,
    "SELECT 
        c.consultation_id,
        a.appointment_date,
        a.appointment_time,
        u.full_name AS patient_name,
        c.diagnosis
     FROM consultations c
     JOIN appointments a ON c.appointment_id = a.appointment_id
     JOIN patients p ON a.patient_id = p.patient_id
     JOIN users u ON p.user_id = u.user_id
     WHERE a.doctor_id = $doctor_id
     ORDER BY c.created_at DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Consultation History | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">
    <style>
        .layout { display: flex; }
        .main { flex: 1; padding: 25px; }

        table {
            width: 100%;
            background: #fff;
            border-radius: 12px;
            box-shadow: var(--shadow);
            border-collapse: collapse;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
            text-align: left;
        }

        th {
            background: #f8fafc;
        }

        .btn-sm {
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 12px;
            background: var(--primary);
            color: #fff;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="layout">
    <?php require_once "../layouts/sidebar.php"; ?>

    <main class="main">
        <?php require_once "../layouts/topbar.php"; ?>

        <h2 style="margin-bottom:15px;">Consultation History</h2>

        <table>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Patient</th>
                <th>Diagnosis</th>
                <th>Action</th>
            </tr>

            <?php if (mysqli_num_rows($consultations) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($consultations)): ?>
                    <tr>
                        <td><?= $row['appointment_date'] ?></td>
                        <td><?= $row['appointment_time'] ?></td>
                        <td><?= htmlspecialchars($row['patient_name']) ?></td>
                        <td><?= htmlspecialchars($row['diagnosis']) ?></td>
                        <td>
                            <a href="consultation_view.php?id=<?= $row['consultation_id'] ?>"
                               class="btn-sm">
                               View
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No consultation history found.</td>
                </tr>
            <?php endif; ?>
        </table>

    </main>
</div>

</body>
</html>
