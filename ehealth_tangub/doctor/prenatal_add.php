<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];

/* ================================
   FETCH APPROVED PATIENTS
================================ */
$patients = mysqli_query(
    $conn,
    "SELECT DISTINCT
        p.patient_id,
        u.full_name
     FROM appointments a
     JOIN patients p ON a.patient_id = p.patient_id
     JOIN users u ON p.user_id = u.user_id
     WHERE a.doctor_id = $doctor_id
     AND a.status = 'Approved'
     ORDER BY u.full_name ASC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Prenatal Record | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">
    <style>
        .layout { display: flex; }
        .main { flex: 1; padding: 25px; }

        .card {
            background: #fff;
            padding: 25px;
            border-radius: 14px;
            box-shadow: var(--shadow);
            max-width: 600px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 6px;
            display: block;
        }

        input, textarea, select {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        textarea { resize: vertical; }

        .actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>

<div class="layout">
    <?php require_once "../layouts/sidebar.php"; ?>

    <main class="main">
        <?php require_once "../layouts/topbar.php"; ?>

        <h2 style="margin-bottom:15px;">Add Prenatal Record</h2>

        <div class="card">

            <?php if (mysqli_num_rows($patients) === 0): ?>
                <p>No approved appointments available.</p>
            <?php else: ?>

                <form method="POST" action="prenatal_save.php">

                    <div class="form-group">
                        <label>Patient</label>
                        <select name="patient_id" required>
                            <option value="">-- Select Patient --</option>
                            <?php while ($p = mysqli_fetch_assoc($patients)): ?>
                                <option value="<?= $p['patient_id'] ?>">
                                    <?= htmlspecialchars($p['full_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Visit Date</label>
                        <input type="date" name="visit_date" required>
                    </div>

                    <div class="form-group">
                        <label>Weight</label>
                        <input type="text" name="weight" placeholder="e.g. 60 kg">
                    </div>

                    <div class="form-group">
                        <label>Blood Pressure</label>
                        <input type="text" name="blood_pressure" placeholder="e.g. 120/80">
                    </div>

                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" rows="4"></textarea>
                    </div>

                    <div class="actions">
                        <button type="submit" class="btn-primary">Save Record</button>
                        <a href="prenatal.php" class="btn btn-danger">Cancel</a>
                    </div>

                </form>

            <?php endif; ?>

        </div>

    </main>
</div>

</body>
</html>
