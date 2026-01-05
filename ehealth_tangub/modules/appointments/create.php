<?php
require_once "../../config/database.php";

if ($_SESSION['role'] !== 'patient') {
    header("Location: ../../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$patient = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT patient_id FROM patients WHERE user_id=$user_id"
));

$doctor_id = $_POST['doctor_id'];
$date = $_POST['appointment_date'];
$time = $_POST['appointment_time'];
$reason = $_POST['reason'];

mysqli_query($conn,
    "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason)
     VALUES ({$patient['patient_id']}, $doctor_id, '$date', '$time', '$reason')"
);

$appointment_id = mysqli_insert_id($conn);

/* QR CODE GENERATION */
$qr_text = "APPT-ID:$appointment_id|PATIENT:{$patient['patient_id']}";
$qr_file = "../../assets/qr/appointment_$appointment_id.png";

if (!file_exists("../../assets/qr")) {
    mkdir("../../assets/qr", 0777, true);
}

include_once "../../assets/js/qr.php";
QRcode::png($qr_text, $qr_file, QR_ECLEVEL_L, 4);

mysqli_query($conn,
    "UPDATE appointments SET qr_code='$qr_file' WHERE appointment_id=$appointment_id"
);

header("Location: ../../patient/dashboard.php");
exit();
