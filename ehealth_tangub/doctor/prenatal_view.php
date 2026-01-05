<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: prenatal.php");
    exit();
}

$patient_id = (int) $_GET['id'];
$doctor_id = $_SESSION['user_id'];

/* ================================
   VERIFY DOCTOR ACCESS
================================ */
$verify = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT 1
         FROM appointments
         WHERE patient_id = $patient_id
         AND doctor_id = $doctor_id
         LIMIT 1"
    )
);

if (!$verify) {
    die("Access denied.");
}

/* ================================
   FETCH PRENATAL RECORDS
================================ */
$records = mysqli_query(
    $conn,
    "SELECT *
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
        .layout { display: flex; }
        .main { flex: 1; padding: 25px; }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 14px;
            box-shadow: var(--shadow);
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="layout">
    <?php require_once "../layouts/sidebar.php"; ?>

    <main class="main">
        <?php require_once "../layouts/topbar.php"; ?>

        <h2>Prenatal Records</h2>

        <?php if (mysqli_num_rows($records) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($records)): ?>
                <div class="card">
                    <p><strong>Visit Date:</strong> <?= $row['visit_date'] ?></p>
                    <p><strong>Weight:</strong> <?= $row['weight'] ?></p>
                    <p><strong>Blood Pressure:</strong> <?= $row['blood_pressure'] ?></p>
                    <p><strong>Notes:</strong><br><?= nl2br(htmlspecialchars($row['notes'])) ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No prenatal records available.</p>
        <?php endif; ?>

        <a href="prenatal.php" class="btn btn-primary">Back</a>

    </main>
</div>

</body>
</html>
