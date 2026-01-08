<?php
require_once "../config/database.php";

/* ================================
   SESSION SAFE START
================================ */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: verify_qr.php");
    exit();
}

/* ================================
   REQUIRED
================================ */
$patient_id     = (int) ($_POST['patient_id'] ?? 0);
$appointment_id = (int) ($_POST['appointment_id'] ?? 0);
$type           = $_POST['type'] ?? null;

if ($patient_id === 0) {
    die("Invalid patient ID");
}

/* ================================
   VITAL SIGNS
================================ */
$blood_pressure    = $_POST['blood_pressure'] ?? null;
$temperature       = $_POST['temperature'] ?? null;
$heart_rate        = $_POST['heart_rate'] ?? null;
$respiratory_rate  = $_POST['respiratory_rate'] ?? null;
$weight            = $_POST['weight'] ?? null;
$height            = $_POST['height'] ?? null;
$oxygen_saturation = $_POST['oxygen_saturation'] ?? null;
$service           = $_POST['service'] ?? null;
$complaints        = $_POST['complaints'] ?? null;

/* ================================
   PRENATAL
================================ */
$lmp                    = $_POST['lmp'] ?? null;
$edc                    = $_POST['edc'] ?? null;
$gestational_age        = $_POST['gestational_age'] ?? null;
$bleeding               = $_POST['bleeding'] ?? null;
$urinary_infection      = $_POST['urinary_infection'] ?? null;
$discharge              = $_POST['discharge'] ?? null;
$abnormal_abdomen       = $_POST['abnormal_abdomen'] ?? null;
$malpresentation        = $_POST['malpresentation'] ?? null;
$absent_fetal_heartbeat = $_POST['absent_fetal_heartbeat'] ?? null;
$genital_infection      = $_POST['genital_infection'] ?? null;
$fundal_height          = $_POST['fundal_height'] ?? null;
$fetal_movement_count   = $_POST['fetal_movement_count'] ?? null;
$weight_gain            = $_POST['weight_gain'] ?? null;
$edema                  = $_POST['edema'] ?? null;
$blood_type             = $_POST['blood_type'] ?? null;
$hemoglobin_level       = $_POST['hemoglobin_level'] ?? null;
$urine_protein          = $_POST['urine_protein'] ?? null;

/* ================================
   CLEAR IF NOT PRENATAL
================================ */
if ($type !== 'prenatal') {
    $lmp = $edc = $gestational_age = null;
    $bleeding = $urinary_infection = $discharge = null;
    $abnormal_abdomen = $malpresentation = null;
    $absent_fetal_heartbeat = $genital_infection = null;
    $fundal_height = $fetal_movement_count = null;
    $weight_gain = $edema = null;
    $blood_type = $hemoglobin_level = $urine_protein = null;
}

/* ================================
   INSERT
================================ */
$sql = "
INSERT INTO informations (
    patients_id,
    appointment_id,
    blood_pressure,
    temperature,
    heart_rate,
    respiratory_rate,
    weight,
    height,
    oxygen_saturation,
    service,
    complaints,
    lmp,
    edc,
    gestational_age,
    bleeding,
    urinary_infection,
    discharge,
    abnormal_abdomen,
    malpresentation,
    absent_fetal_heartbeat,
    genital_infection,
    fundal_height,
    fetal_movement_count,
    weight_gain,
    edema,
    blood_type,
    hemoglobin_level,
    urine_protein
) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    die("Prepare failed: " . mysqli_error($conn));
}

/* ================================
   BIND PARAMETERS
================================ */
mysqli_stmt_bind_param(
    $stmt,
    "iissssssssssssssssssssssssss",
    $patient_id,
    $appointment_id,
    $blood_pressure,
    $temperature,
    $heart_rate,
    $respiratory_rate,
    $weight,
    $height,
    $oxygen_saturation,
    $service,
    $complaints,
    $lmp,
    $edc,
    $gestational_age,
    $bleeding,
    $urinary_infection,
    $discharge,
    $abnormal_abdomen,
    $malpresentation,
    $absent_fetal_heartbeat,
    $genital_infection,
    $fundal_height,
    $fetal_movement_count,
    $weight_gain,
    $edema,
    $blood_type,
    $hemoglobin_level,
    $urine_protein
);

/* ================================
   EXECUTE INSERT
================================ */
if (!mysqli_stmt_execute($stmt)) {
    die("Insert failed: " . mysqli_stmt_error($stmt));
}
mysqli_stmt_close($stmt);

/* ================================
   UPDATE APPOINTMENT STATUS
================================ */
if ($appointment_id > 0) {
    $update_sql = "UPDATE appointments SET status = 'Check-in' WHERE appointment_id = ?";
    $update_stmt = mysqli_prepare($conn, $update_sql);
    if ($update_stmt) {
        mysqli_stmt_bind_param($update_stmt, "i", $appointment_id);
        mysqli_stmt_execute($update_stmt);
        mysqli_stmt_close($update_stmt);
    } else {
        error_log("Failed to prepare appointment update: " . mysqli_error($conn));
    }
}

/* ================================
   REDIRECT WITH SUCCESS
================================ */
header("Location: verify_qr.php?success=1");
exit();
