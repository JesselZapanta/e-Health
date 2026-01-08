<?php
require_once "../config/database.php";

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $qr_code = mysqli_real_escape_string($conn, $_POST['qr_code']);

    $query = mysqli_query(
        $conn,
        "SELECT 
            a.appointment_id,
            a.patient_id,
            a.status,
            a.type,
            a.appointment_date,
            a.appointment_time,
            u.full_name AS patient_name
        FROM appointments a
        JOIN patients p ON a.patient_id = p.patient_id
        JOIN users u ON p.user_id = u.user_id
        WHERE a.qr_code = '$qr_code'"
    );

    if (mysqli_num_rows($query) > 0) {

        $result = mysqli_fetch_assoc($query);

        if ($result['status'] === 'Approved') {
            $result['verified'] = true;
        } elseif ($result['status'] === 'Check-in') {
            $result['already_checked'] = true;
        } else {
            $result['invalid'] = true;
        }

    } else {
        $result = ['not_found' => true];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>QR Verification | eHEALTH</title>
    <link rel="stylesheet" href="/ehealth_tangub/assets/css/ui.css">
</head>
<body>

<div class="app-container">
    <?php require_once "../layouts/sidebar.php"; ?>

    <main class="main-content">
        <?php require_once "../layouts/topbar.php"; ?>

        <h3 class="page-title">Code Verification</h3>

        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div class="badge badge-success" style="margin-bottom: 15px;">
                Information saved successfully!
            </div>
        <?php endif; ?>

        <!-- INPUT -->
        <div class="card" style="max-width:420px;margin-bottom:20px;">
            <form method="POST">
                <div class="form-group">
                    <label>Enter Code</label>
                    <input type="text" name="qr_code" required>
                </div>
                <button type="submit" class="btn-primary" style="margin-top:10px;">
                    Verify Appointment
                </button>
            </form>
        </div>

        <!-- RESULT -->
        <?php if ($result): ?>
        <div class="card">

            <?php if (!empty($result['verified'])): ?>
                <span class="badge badge-success">Verified Successfully</span>
            <?php elseif (!empty($result['already_checked'])): ?>
                <span class="badge badge-warning">Already Checked In</span>
            <?php elseif (!empty($result['invalid'])): ?>
                <span class="badge badge-danger">Appointment Not Approved</span>
            <?php elseif (!empty($result['not_found'])): ?>
                <span class="badge badge-danger">Appointment Not Found</span>
            <?php endif; ?>

            <?php if (isset($result['patient_name'])): ?>
                <hr>
                <p><strong>Patient:</strong> <?= htmlspecialchars($result['patient_name']) ?></p>
                <p><strong>Date:</strong> <?= $result['appointment_date'] ?></p>
                <p><strong>Time:</strong> <?= strtoupper($result['appointment_time']) ?></p>
                <p><strong>Status:</strong> <?= strtoupper($result['status']) ?></p>
                <p><strong>Type:</strong> <?= strtoupper($result['type']) ?></p>

                <hr>

                <form method="POST" action="save_information.php">
                    <input type="hidden" name="patient_id" value="<?= $result['patient_id'] ?>">
                    <input type="hidden" name="type" value="<?= $result['type'] ?>">
                    <input type="hidden" name="appointment_id" value="<?= $result['appointment_id'] ?>">

                    <h4>Vital Signs</h4>

                    <div class="form-grid">
                        <div class="form-group" style="flex:1;min-width:150px;">
                            <label>Blood Pressure</label>
                            <input type="text" name="blood_pressure" required>
                        </div>
                        <div class="form-group" style="flex:1;min-width:150px;">
                            <label>Temperature (°C)</label>
                            <input type="text" name="temperature" required>
                        </div>
                        <div class="form-group" style="flex:1;min-width:150px;">
                            <label>Heart Rate</label>
                            <input type="text" name="heart_rate" required>
                        </div>
                        <div class="form-group" style="flex:1;min-width:150px;">
                            <label>Respiratory Rate</label>
                            <input type="text" name="respiratory_rate" required>
                        </div>
                        <div class="form-group" style="flex:1;min-width:150px;">
                            <label>Weight (kg)</label>
                            <input type="text" name="weight" required>
                        </div>
                        <div class="form-group" style="flex:1;min-width:150px;">
                            <label>Height (cm)</label>
                            <input type="text" name="height" required>
                        </div>
                        <div class="form-group" style="flex:1;min-width:150px;" required>
                            <label>SpO₂ (%)</label>
                            <input type="text" name="oxygen_saturation" required>
                        </div>
                        <div class="form-group" style="flex:1;min-width:150px;">
                            <label>Service</label>
                            <select name="service" required>
                                <option value="checkup">Check-up</option>
                                <option value="dental">Dental</option>
                                <option value="laboratory">Laboratory</option>
                                <option value="FP">Family Planning</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="flex:1;min-width:150px;" required>
                        <label>Chief Complaints</label>
                        <input type="text" name="complaints" required>
                    </div>

                    <!-- PRENATAL ONLY -->
                    <?php if ($result['type'] === 'prenatal'): ?>
                    <h4 style="margin-top:20px;">For Prenatal Check Up</h4>

                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:15px;">

                        <div class="form-group" style="flex:1;min-width:150px;">
                            <label>LMP (Last Menstrual Period)</label>
                            <input type="date" name="lmp">
                        </div>

                        <div class="form-group" style="flex:1;min-width:150px;">
                            <label>EDC (Estimated Date of Confinement)</label>
                            <input type="date" name="edc">
                        </div>

                        <div class="form-group" style="flex:1;min-width:150px;">
                            <label>Pangidaron a Gimabdos (Gestational Age)</label>
                            <input type="text" name="gestational_age" placeholder="e.g., 24 weeks">
                        </div>

                        <div class="form-group" style="flex:1;min-width:150px;">
                            <label>Gidugo (Bleeding)</label>
                            <select name="bleeding">
                                <option value="">Select</option>
                                <option value="oo">Oo</option>
                                <option value="dili">Dili</option>
                            </select>
                        </div>

                        <div class="form-group" style="flex:1;min-width:150px;">
                            <label>Impeksyon sa Ihi (Urinary Infection)</label>
                            <select name="urinary_infection">
                                <option value="">Select</option>
                                <option value="oo">Oo</option>
                                <option value="dili">Dili</option>
                            </select>
                        </div>

                        <div class="form-group" style="flex:1;min-width:150px;">
                            <label>Pagpangluspad (Discharge)</label>
                            <input type="text" name="discharge" placeholder="Details if any">
                        </div>

                        <div class="form-group" style="flex:1;min-width:150px;">
                            <label>Abnormal a kadak-on sa tiyan</label>
                            <select name="abnormal_abdomen">
                                <option value="">Select</option>
                                <option value="oo">Oo</option>
                                <option value="dili">Dili</option>
                            </select>
                        </div>

                        <div class="form-group" style="flex:1;min-width:150px;">
                            <label>Suhi ang Bata (Malpresentation)</label>
                            <select name="malpresentation">
                                <option value="">Select</option>
                                <option value="oo">Oo</option>
                                <option value="dili">Dili</option>
                            </select>
                        </div>

                        <div class="form-group" style="flex:1;min-width:150px;">
                            <label>Wala naka pitik ang kasing-kasing</label>
                            <select name="absent_fetal_heartbeat">
                                <option value="">Select</option>
                                <option value="oo">Oo</option>
                                <option value="dili">Dili</option>
                            </select>
                        </div>

                        <div class="form-group" style="flex:1;min-width:150px;">
                            <label>Impeksyon sa kinatawo</label>
                            <select name="genital_infection">
                                <option value="">Select</option>
                                <option value="oo">Oo</option>
                                <option value="dili">Dili</option>
                            </select>
                        </div>

                        <div class="form-group" style="flex:1;min-width:150px;">
                            <label>Fundal Height (cm)</label>
                            <input type="text" name="fundal_height" placeholder="e.g., 24">
                        </div>

                        <div class="form-group" style="flex:1;min-width:150px;">
                            <label>Fetal Movement Count</label>
                            <input type="text" name="fetal_movement_count" placeholder="e.g., 10/day">
                        </div>

                        <div class="form-group" style="flex:1;min-width:150px;">
                            <label>Weight Gain (kg)</label>
                            <input type="text" name="weight_gain" placeholder="e.g., 3">
                        </div>

                        <div class="form-group" style="flex:1;min-width:150px;">
                            <label>Edema</label>
                            <select name="edema">
                                <option value="">Select</option>
                                <option value="oo">Oo</option>
                                <option value="dili">Dili</option>
                            </select>
                        </div>

                        <div class="form-group" style="flex:1;min-width:150px;">
                            <label>Blood Type</label>
                            <input type="text" name="blood_type" placeholder="e.g., O+">
                        </div>

                        <div class="form-group" style="flex:1;min-width:150px;">
                            <label>Hemoglobin Level (g/dL)</label>
                            <input type="text" name="hemoglobin_level" placeholder="e.g., 12.5">
                        </div>

                        <div class="form-group" style="flex:1;min-width:150px;">
                            <label>Urine Protein</label>
                            <input type="text" name="urine_protein" placeholder="e.g., Negative">
                        </div>

                    </div>
                    <?php endif; ?>

                    <button class="btn-primary" style="margin-top:20px;">Save Information</button>
                </form>
            <?php endif; ?>

        </div>
        <?php endif; ?>

    </main>
</div>

</body>
</html>
