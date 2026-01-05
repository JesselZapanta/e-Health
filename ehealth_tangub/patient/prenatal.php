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
    mysqli_query(
        $conn,
        "SELECT patient_id FROM patients WHERE user_id = $user_id"
    )
);

$patient_id = $patient['patient_id'] ?? 0;

/* ================================
   FETCH PRENATAL RECORDS
================================ */
$records = mysqli_query(
    $conn,
    "SELECT visit_date, weight, blood_pressure, notes
     FROM prenatal_records
     WHERE patient_id = $patient_id
     ORDER BY visit_date DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Prenatal Records | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">

    <style>
        .layout {
            display: flex;
        }

        .main {
            flex: 1;
            padding: 25px;
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 14px;
            box-shadow: var(--shadow);
            margin-bottom: 15px;
        }

        .card h4 {
            margin-bottom: 10px;
        }

        .empty {
            background: #fff;
            padding: 30px;
            border-radius: 14px;
            box-shadow: var(--shadow);
            text-align: center;
            color: #666;
        }
    </style>
</head>
<body>

<div class="layout">

    <!-- SIDEBAR -->
    <?php require_once "../layouts/sidebar.php"; ?>

    <!-- MAIN CONTENT -->
    <main class="main">

        <!-- TOPBAR -->
        <?php require_once "../layouts/topbar.php"; ?>

        <h2 class="page-title">Prenatal Records</h2>

        <?php if (mysqli_num_rows($records) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($records)): ?>
                <div class="card">
                    <h4>Visit Date: <?= $row['visit_date'] ?></h4>

                    <p><strong>Weight:</strong>
                        <?= $row['weight'] ?: '—' ?>
                    </p>

                    <p><strong>Blood Pressure:</strong>
                        <?= $row['blood_pressure'] ?: '—' ?>
                    </p>

                    <p><strong>Notes:</strong><br>
                        <?= nl2br(htmlspecialchars($row['notes'])) ?: '—' ?>
                    </p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty">
                No prenatal records available.
            </div>
        <?php endif; ?>

    </main>
</div>

</body>
</html>
