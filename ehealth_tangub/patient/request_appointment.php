<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

$patient_user_id = $_SESSION['user_id'];

/* Get patient_id */
$p = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT patient_id FROM patients WHERE user_id = $patient_user_id")
);
$patient_id = $p['patient_id'];

/* Get patient info */
$patient = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT gender, is_pregnant FROM patients WHERE patient_id = $patient_id")
);

/* Doctors list */
$doctors = mysqli_query($conn, "
    SELECT user_id, full_name 
    FROM users 
    WHERE role = 'doctor' AND status = 'active'
");

/* Form handling */
$message = '';
$availability_times = [];
$doctor_id = '';
$appointment_date = '';
$appointment_time = '';
$type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = $_POST['doctor_id'] ?? '';
    $appointment_date = $_POST['appointment_date'] ?? '';
    $appointment_time = $_POST['appointment_time'] ?? '';
    $type = $_POST['type'] ?? 'general';

    /* Populate available times for selected doctor & date */
    if ($doctor_id && $appointment_date) {
        $q = mysqli_query($conn, "
            SELECT time, slots
            FROM doctor_availability
            WHERE doctor_id = $doctor_id
              AND available_date = '$appointment_date'
              AND status = 'available'
              AND slots > 0
            ORDER BY time
        ");

        $availability_times = [];
        while ($r = mysqli_fetch_assoc($q)) {
            $availability_times[] = [
                'time' => $r['time'],
                'display' => "{$r['time']} (Slots: {$r['slots']})"
            ];
        }
    }

    /* If user selected a time, save appointment */
    if ($appointment_time) {

        // --- CHECK FOR EXISTING APPOINTMENT ON SAME DAY ---
        $existing = mysqli_query($conn, "
            SELECT * FROM appointments
            WHERE patient_id = $patient_id
              AND appointment_date = '$appointment_date'
            LIMIT 1
        ");

        if (mysqli_num_rows($existing) > 0) {
            $message = "You already have an appointment on this date.";
        } else {

            // --- CHECK DOCTOR AVAILABILITY ---
            $check = mysqli_query($conn, "
                SELECT *
                FROM doctor_availability
                WHERE doctor_id = $doctor_id
                  AND available_date = '$appointment_date'
                  AND time = '$appointment_time'
                  AND status = 'available'
                  AND slots > 0
                LIMIT 1
            ");

            if (mysqli_num_rows($check) === 0) {
                $message = "Invalid appointment time or slot already full.";
            } else {
                $r = mysqli_fetch_assoc($check);

                // --- SEQUENTIAL 6-DIGIT QR CODE GENERATION ---
                $last_qr = mysqli_query($conn, "SELECT qr_code FROM appointments ORDER BY appointment_id DESC LIMIT 1");
                $row = mysqli_fetch_assoc($last_qr);

                if ($row && is_numeric($row['qr_code'])) {
                    $qr_code = $row['qr_code'] + 1;
                } else {
                    $qr_code = 100000;
                }
                // --- END QR CODE ---

                // --- INSERT APPOINTMENT ---
                mysqli_query($conn, "
                    INSERT INTO appointments
                    (patient_id, doctor_id, appointment_date, appointment_time, type, status, qr_code)
                    VALUES
                    ($patient_id, $doctor_id, '$appointment_date', '$appointment_time', '$type', 'Approved', '$qr_code')
                ");

                // --- UPDATE SLOTS ---
                mysqli_query($conn, "
                    UPDATE doctor_availability
                    SET slots = slots - 1
                    WHERE doctor_id = $doctor_id
                    AND available_date = '$appointment_date'
                    AND time = '$appointment_time'
                    AND status = 'available'
                ");

                mysqli_query($conn, "
                    UPDATE doctor_availability
                    SET status = 'booked'
                    WHERE doctor_id = $doctor_id
                    AND available_date = '$appointment_date'
                    AND time = '$appointment_time'
                    AND slots = 0
                ");

                $message = "Appointment requested successfully!";

                // Reset form
                $doctor_id = '';
                $appointment_date = '';
                $appointment_time = '';
                $availability_times = [];

                header("Location: appointments.php");
                exit();
            }
        }
    }
} else {
    $doctor_id = '';
    $appointment_date = '';
    $appointment_time = '';
    $type = '';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Request Appointment | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">
</head>
<body>

<div class="layout">
<?php require "../layouts/sidebar.php"; ?>
<main class="main">
<?php require "../layouts/topbar.php"; ?>

<h2>Request Appointment</h2>

<?php if ($message): ?>
    <div class="card" style="color:green; padding:10px; margin-bottom:15px;">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<div class="card" style="max-width:520px;">
<form method="POST">

    <!-- Select Doctor -->
    <div class="form-group">
        <label>Doctor</label>
        <select name="doctor_id" required onchange="this.form.submit()">
            <option value="">-- Select Doctor --</option>
            <?php foreach ($doctors as $d): ?>
                <option value="<?= $d['user_id'] ?>" <?= ($doctor_id == $d['user_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($d['full_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Appointment Type -->
    <div class="form-group">
        <label>Type</label>
        <select name="type">
            <option value="general" <?= ($type === 'general') ? 'selected' : '' ?>>General Check-up</option>
            <?php if ($patient['gender'] === 'female' && $patient['is_pregnant'] == 1): ?>
                <option value="prenatal" <?= ($type === 'prenatal') ? 'selected' : '' ?>>Prenatal Check-up</option>
            <?php endif; ?>
        </select>
    </div>

    <!-- Select Date -->
    <div class="form-group">
        <label>Date</label>
        <input type="date" name="appointment_date" value="<?= htmlspecialchars($appointment_date) ?>" required onchange="this.form.submit()">
    </div>

    <!-- Show Available Times -->
    <?php if ($doctor_id && $appointment_date): ?>
        <div class="form-group">
            <label>Available Time</label>
            <select name="appointment_time" required>
                <option value="">-- Select Time --</option>
                <?php if (empty($availability_times)): ?>
                    <option value="">No available slots</option>
                <?php else: ?>
                    <?php foreach ($availability_times as $slot): ?>
                        <option value="<?= $slot['time'] ?>" <?= ($appointment_time == $slot['time']) ? 'selected' : '' ?>>
                            <?= strtoupper($slot['display']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <button class="btn-primary">Submit Request</button>
    <?php endif; ?>

</form>
</div>

</main>
</div>

</body>
</html>
