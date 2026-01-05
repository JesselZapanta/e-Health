<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ================================
   GET PATIENT ID
================================ */
$patient = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT patient_id FROM patients WHERE user_id = $user_id")
);

$patient_id = $patient['patient_id'] ?? 0;

/* ================================
   FETCH CONSULTATION RECORDS
================================ */
$consultations = mysqli_query(
    $conn,
    "SELECT 
        a.appointment_date,
        u.full_name AS doctor_name,
        c.symptoms,
        c.diagnosis,
        c.prescription,
        c.notes
     FROM consultations c
     JOIN appointments a ON c.appointment_id = a.appointment_id
     JOIN users u ON a.doctor_id = u.user_id
     WHERE a.patient_id = $patient_id
     ORDER BY a.appointment_date DESC"
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

        table {
            width: 100%;
            background: #fff;
            border-radius: 14px;
            box-shadow: var(--shadow);
            border-collapse: collapse;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
            vertical-align: top;
        }

        th {
            background: #f8fafc;
            text-align: left;
        }

        .empty {
            text-align: center;
            padding: 30px;
            color: #666;
        }
    </style>
</head>
<body>

<div class="layout">

    <!-- SIDEBAR -->
    <?php require_once "../layouts/sidebar.php"; ?>

    <!-- MAIN -->
    <main class="main">

        <!-- TOPBAR -->
        <?php require_once "../layouts/topbar.php"; ?>

        <h2 class="page-title">Consultation Records</h2>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Doctor</th>
                    <th>Symptoms</th>
                    <th>Diagnosis</th>
                    <th>Prescription</th>
                    <th>Notes</th>
                </tr>
            </thead>

            <tbody>
                <?php if (mysqli_num_rows($consultations) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($consultations)): ?>
                        <tr>
                            <td><?= $row['appointment_date'] ?></td>
                            <td><?= htmlspecialchars($row['doctor_name']) ?></td>
                            <td><?= nl2br(htmlspecialchars($row['symptoms'])) ?></td>
                            <td><?= nl2br(htmlspecialchars($row['diagnosis'])) ?></td>
                            <td><?= nl2br(htmlspecialchars($row['prescription'])) ?></td>
                            <td><?= nl2br(htmlspecialchars($row['notes'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="empty">
                            No consultation records available.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </main>
</div>

</body>
</html>
