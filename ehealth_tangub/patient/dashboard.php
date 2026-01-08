<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ================================
   FETCH PATIENT ID
================================ */
$patient = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT patient_id FROM patients WHERE user_id = $user_id")
);
$patient_id = $patient['patient_id'];

/* Fetch gender */
$patientData = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT gender FROM patients WHERE user_id = $user_id")
);
$gender = $patientData['gender'] ?? null;


/* ================================
   DASHBOARD DATA
================================ */
$upcoming = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) total
         FROM appointments
         WHERE patient_id = $patient_id
         AND appointment_date >= CURDATE()
         AND status IN ('Approved', 'Check-in')"
    )
)['total'];

$completed = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) total
         FROM appointments
         WHERE patient_id = $patient_id
         AND status = 'Completed'"
    )
)['total'];
$prenatal = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) total
         FROM appointments
         WHERE patient_id = $patient_id
         AND type = 'prenatal'
         AND status = 'Completed'"
    )
)['total'];

/* ================================
   NEXT APPOINTMENT
================================ */
$next = mysqli_query(
    $conn,
    "SELECT appointment_date, appointment_time, type, status
     FROM appointments
     WHERE patient_id = $patient_id
       AND appointment_date >= CURDATE()
       AND status IN ('Approved', 'Check-in')
     LIMIT 1"
);

$nextAppointment = mysqli_fetch_assoc($next);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Patient Dashboard | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">
    <style>
        .layout {
            display: flex;
        }

        .main {
            flex: 1;
            padding: 25px;
        }

        /* TOPBAR */
        .topbar {
            background: #fff;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        /* DASHBOARD GRID */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .card {
            background: #fff;
            border-radius: 14px;
            padding: 20px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .card-icon {
            font-size: 30px;
            color: var(--primary);
        }

        .card h4 {
            font-size: 13px;
            color: #555;
        }

        .card p {
            font-size: 26px;
            font-weight: bold;
            margin-top: 5px;
        }

        /* NEXT APPOINTMENT CARD */
        .info-card {
            background: #fff;
            padding: 20px;
            border-radius: 14px;
            box-shadow: var(--shadow);
        }

        .info-card h3 {
            margin-bottom: 15px;
        }

        .status {
            font-weight: bold;
        }

        .status.Check-in { color: #0284c7; }
        .status.Approved  { color: #16a34a; }
    </style>
</head>
<body>

<div class="layout">

    <!-- ✅ REUSABLE SIDEBAR -->
    <?php require_once "../layouts/sidebar.php"; ?>

    <!-- MAIN CONTENT -->
    <main class="main">

        <!-- TOPBAR -->
       <?php require_once "../layouts/topbar.php"; ?>

        <!-- SUMMARY CARDS -->
        <div class="dashboard-grid">

            <div class="card">
                <div class="card-icon">📅</div>
                <div>
                    <h4>Upcoming Appointments</h4>
                    <p><?= $upcoming ?></p>
                </div>
            </div>

            <div class="card">
                <div class="card-icon">✅</div>
                <div>
                    <h4>Completed Consultations</h4>
                    <p><?= $completed ?></p>
                </div>
            </div>
        <?php if (strtolower($gender) === 'female'): ?>
                <div class="card">
                    <div class="card-icon">🤰</div>
                    <div>
                        <h4>Completed Prenatal Visits</h4>
                        <p><?= $prenatal ?></p>
                    </div>
                </div>
        <?php endif; ?>


        </div>

        <!-- NEXT APPOINTMENT -->
        <div class="info-card">
            <h3>Next Appointment</h3>

            <?php if ($nextAppointment): ?>
                <p><strong>Type:</strong> <?= strtoupper($nextAppointment['type']) ?></p>
                <p><strong>Date:</strong> <?= $nextAppointment['appointment_date'] ?></p>
                <p><strong>Time of the Day:</strong> <?= strtoupper($nextAppointment['appointment_time']) ?></p>
                <p>
                    <strong>Status:</strong>
                    <span class="status <?= $nextAppointment['status'] ?>">
                        <?= $nextAppointment['status'] ?>
                    </span>
                </p>
            <?php else: ?>
                <p>No upcoming appointments.</p>
            <?php endif; ?>
        </div>

    </main>
</div>

</body>
</html>
