<?php
require_once "../../config/database.php";

if ($_SESSION['role'] !== 'doctor' && $_SESSION['role'] !== 'staff') {
    header("Location: ../../auth/login.php");
    exit();
}

$msg = "";

if (isset($_POST['save'])) {
    $patient_id = $_POST['patient_id'];
    $bp = $_POST['blood_pressure'];
    $weight = $_POST['weight'];
    $fhr = $_POST['fetal_heart_rate'];
    $notes = $_POST['notes'];
    $next = $_POST['next_visit'];

    mysqli_query($conn,
        "INSERT INTO prenatal_records
         (patient_id, visit_date, blood_pressure, weight, fetal_heart_rate, notes, next_visit)
         VALUES ($patient_id, CURDATE(), '$bp', $weight, '$fhr', '$notes', '$next')"
    );

    mysqli_query($conn,
        "UPDATE patients SET is_pregnant=1 WHERE patient_id=$patient_id"
    );

    $msg = "Prenatal record saved.";
}

$patients = mysqli_query($conn, "SELECT patient_id FROM patients");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Prenatal Tracker</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<div class="main">
    <h2>Prenatal Visit Record</h2>

    <?php if ($msg): ?>
        <div class="alert success"><?= $msg ?></div>
    <?php endif; ?>

    <form method="POST">
        <select name="patient_id" required>
            <option value="">Select Patient</option>
            <?php while ($p = mysqli_fetch_assoc($patients)): ?>
                <option value="<?= $p['patient_id'] ?>">Patient #<?= $p['patient_id'] ?></option>
            <?php endwhile; ?>
        </select>

        <input name="blood_pressure" placeholder="Blood Pressure">
        <input name="weight" type="number" step="0.01" placeholder="Weight (kg)">
        <input name="fetal_heart_rate" placeholder="Fetal Heart Rate">
        <textarea name="notes" placeholder="Notes"></textarea>
        <input type="date" name="next_visit">

        <button name="save" class="btn-primary">Save Record</button>
    </form>
</div>

</body>
</html>
