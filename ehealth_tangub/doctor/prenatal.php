<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];

/* ================================
   FETCH PRENATAL PATIENTS
================================ */
$patients = mysqli_query(
    $conn,
    "SELECT 
        p.patient_id,
        u.full_name,
        COUNT(pr.prenatal_id) AS total_visits,
        MAX(pr.visit_date) AS last_visit
     FROM prenatal_records pr
     JOIN patients p ON pr.patient_id = p.patient_id
     JOIN users u ON p.user_id = u.user_id
     JOIN appointments a ON a.patient_id = p.patient_id
     WHERE a.doctor_id = $doctor_id
     GROUP BY p.patient_id
     ORDER BY last_visit DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Prenatal History | eHEALTH</title>
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
        }

        th {
            background: #f8fafc;
        }

        .btn-sm {
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 12px;
            text-decoration: none;
            background: var(--primary);
            color: #fff;
            margin-right: 5px;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }
    </style>
</head>
<body>

<div class="layout">
    <?php require_once "../layouts/sidebar.php"; ?>

    <main class="main">
        <?php require_once "../layouts/topbar.php"; ?>

        <h2 style="margin-bottom:15px;">Prenatal History</h2>

        <a href="prenatal_add.php" class="btn-primary" style="margin-bottom:15px;display:inline-block;">
    ➕ Add Prenatal Record
</a>

<a href="prenatal_chart.php?patient_id=<?= $row['patient_id'] ?>"
   class="btn-sm">
   📈 View Chart
</a>


        <table>
            <tr>
                <th>Patient</th>
                <th>Total Visits</th>
                <th>Last Visit</th>
                <th>Action</th>
            </tr>

            <?php if (mysqli_num_rows($patients) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($patients)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                        <td><?= $row['total_visits'] ?></td>
                        <td><?= $row['last_visit'] ?></td>
                        <td>
                            <a href="prenatal_view.php?id=<?= $row['patient_id'] ?>" class="btn-sm">
                                View Prenatal
                            </a>
                            <a href="patients.php?id=<?= $row['patient_id'] ?>"
                               class="btn-sm btn-outline">
                                Patient History
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No prenatal records found.</td>
                </tr>
            <?php endif; ?>
        </table>

    </main>
</div>

</body>
</html>
