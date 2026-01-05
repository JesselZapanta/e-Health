<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];

/* ================================
   FETCH PATIENTS HANDLED BY DOCTOR
================================ */
$patients = mysqli_query(
    $conn,
    "SELECT DISTINCT
        p.patient_id,
        u.full_name,
        u.email,
        p.is_pregnant
     FROM appointments a
     JOIN patients p ON a.patient_id = p.patient_id
     JOIN users u ON p.user_id = u.user_id
     WHERE a.doctor_id = $doctor_id
     ORDER BY u.full_name ASC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Patient History | eHEALTH</title>
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
        }

        th {
            background: #f8fafc;
            text-align: left;
        }

        .actions a {
            margin-right: 6px;
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

        <h2 class="page-title">Patient History</h2>

        <table>
            <thead>
                <tr>
                    <th>Patient Name</th>
                    <th>Email</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php if (mysqli_num_rows($patients) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($patients)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td>
                                <?php if ($row['is_pregnant']): ?>
                                    <span class="badge badge-warning">Prenatal</span>
                                <?php else: ?>
                                    <span class="badge badge-success">General</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a class="btn btn-sm btn-primary"
                                   href="patient_consultations.php?patient_id=<?= $row['patient_id'] ?>">
                                    Consultations
                                </a>

                                <?php if ($row['is_pregnant']): ?>
                                    <a class="btn btn-sm btn-success"
                                       href="prenatal.php?patient_id=<?= $row['patient_id'] ?>">
                                        Prenatal
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">
                            No patient records found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </main>
</div>

</body>
</html>
