<?php
require_once "../config/database.php";

//qr code func
function generateQRCode($length = 10) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $code;
}

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

/* Doctors list */
$doctors = mysqli_query($conn, "
    SELECT user_id, full_name 
    FROM users 
    WHERE role = 'doctor' AND status = 'active'
");


/* Form handling */
$message = '';
$availability_ranges = [];
$doctor_id = '';
$appointment_date = '';
$appointment_time = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = $_POST['doctor_id'] ?? '';
    $appointment_date = $_POST['appointment_date'] ?? '';
    $appointment_time = $_POST['appointment_time'] ?? '';

    /* Populate available ranges for selected doctor & date */
    if ($doctor_id && $appointment_date) {
        $q = mysqli_query($conn, "
            SELECT start_time, end_time, slots
            FROM doctor_availability
            WHERE doctor_id = $doctor_id
              AND available_date = '$appointment_date'
              AND status = 'available'
              AND slots > 0
            ORDER BY start_time
        ");

        $availability_ranges = [];
        while ($r = mysqli_fetch_assoc($q)) {
            $start = date("g:i A", strtotime($r['start_time']));
            $end   = date("g:i A", strtotime($r['end_time']));
            $slots = $r['slots'];

            $availability_ranges[] = [
                'start_time' => $r['start_time'],
                'display'    => "$start – $end (Slots: $slots)"
            ];
        }
    }

    /* If user selected a range, save appointment */
    if ($appointment_time) {
        $check = mysqli_query($conn, "
            SELECT *
            FROM doctor_availability
            WHERE doctor_id = $doctor_id
              AND available_date = '$appointment_date'
              AND start_time = '$appointment_time'
              AND status = 'available'
              AND slots > 0
            LIMIT 1
        ");

        if (mysqli_num_rows($check) === 0) {
            $message = "Invalid appointment time or slot already full.";
        } else {
            $r = mysqli_fetch_assoc($check);
            $start_time = $r['start_time'];

            // Save appointment
            $qr_code = generateQRCode(12); // generate a 12-character QR code

            mysqli_query($conn, "
                INSERT INTO appointments
                (patient_id, doctor_id, appointment_date, appointment_time, status, qr_code)
                VALUES
                ($patient_id, $doctor_id, '$appointment_date', '$start_time', 'Approved', '$qr_code')
            ");

            // Step 1: Decrease slots
            mysqli_query($conn, "
                UPDATE doctor_availability
                SET slots = slots - 1
                WHERE doctor_id = $doctor_id
                AND available_date = '$appointment_date'
                AND start_time = '$start_time'
                AND status = 'available'
            ");

            // Step 2: Mark as booked if slots = 0
            mysqli_query($conn, "
                UPDATE doctor_availability
                SET status = 'booked'
                WHERE doctor_id = $doctor_id
                AND available_date = '$appointment_date'
                AND start_time = '$start_time'
                AND slots = 0
            ");

            $message = "Appointment requested successfully!";

            // Reset form
            $doctor_id = '';
            $appointment_date = '';
            $appointment_time = '';
            $availability_ranges = [];

            header("Location: appointments.php");
            exit();
        }
    }
} else {
    $doctor_id = '';
    $appointment_date = '';
    $appointment_time = '';
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

    <!-- Select Date -->
    <div class="form-group">
        <label>Date</label>
        <input type="date" name="appointment_date" value="<?= htmlspecialchars($appointment_date) ?>" required onchange="this.form.submit()">
    </div>

    <!-- Show Available Time Ranges -->
    <?php if ($doctor_id && $appointment_date): ?>
        <div class="form-group">
            <label>Available Time</label>
            <select name="appointment_time" required>
                <option value="">-- Select Time --</option>
                <?php if (empty($availability_ranges)): ?>
                    <option value="">No available slots</option>
                <?php else: ?>
                    <?php foreach ($availability_ranges as $slot): ?>
                        <option value="<?= $slot['start_time'] ?>" <?= ($appointment_time == $slot['start_time']) ? 'selected' : '' ?>>
                            <?= $slot['display'] ?>
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
