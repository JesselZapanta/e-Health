<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];

/* ================================
   DASHBOARD COUNTS
================================ */
$totalAppointments = mysqli_fetch_assoc(
    mysqli_query($conn,
        "SELECT COUNT(*) total
         FROM appointments
         WHERE doctor_id = $doctor_id"
    )
)['total'];

$todayAppointments = mysqli_fetch_assoc(
    mysqli_query($conn,
        "SELECT COUNT(*) total
         FROM appointments
         WHERE doctor_id = $doctor_id
         AND status = 'Check-in'
         AND appointment_date = CURDATE()"
    )
)['total'];

$generalAppointments = mysqli_fetch_assoc(
    mysqli_query($conn,
        "SELECT COUNT(*) AS total
         FROM appointments
         WHERE doctor_id = $doctor_id
         AND status = 'Check-in'
         AND appointment_date = CURDATE()
         AND type = 'general'"
    )
)['total'];

$prenatalAppointments = mysqli_fetch_assoc(
    mysqli_query($conn,
        "SELECT COUNT(*) AS total
         FROM appointments
         WHERE doctor_id = $doctor_id
         AND status = 'Check-in'
         AND appointment_date = CURDATE()
         AND type = 'prenatal'"
    )
)['total'];


/* ================================
   TODAY'S APPOINTMENTS
================================ */


$todayList  = mysqli_query($conn, "
    SELECT 
        a.appointment_id,
        a.appointment_date,
        a.appointment_time,
        a.type,
        a.qr_code,
        u.full_name AS patient_name,
        i.lmp,
        i.edc,
        i.gestational_age
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    JOIN users u ON p.user_id = u.user_id
    LEFT JOIN informations i ON i.appointment_id = a.appointment_id
    WHERE a.doctor_id = $doctor_id
      AND a.status = 'Check-in'
      AND a.appointment_date = CURDATE()
    ORDER BY i.appointment_id DESC
");

?>
<!DOCTYPE html>
<html>
<head>
    <title>Doctor Dashboard | eHEALTH</title>
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

        /* DASHBOARD CARDS */
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

        /* TABLE */
        table {
            width: 100%;
            background: #fff;
            border-radius: 12px;
            box-shadow: var(--shadow);
            border-collapse: collapse;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
            text-align: left;
        }

        th {
            background: #f8fafc;
        }

        .status.Pending { color: #f59e0b; font-weight: bold; }
        .status.Approved { color: #0284c7; font-weight: bold; }
        .status.Completed { color: #16a34a; font-weight: bold; }

        .btn-sm {
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 12px;
            text-decoration: none;
            background: var(--primary);
            color: #fff;
        }
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

            <!-- <div class="card">
                <div class="card-icon">📅</div>
                <div>
                    <h4>Total Appointments</h4>
                    <p><?= $totalAppointments ?></p>
                </div>
            </div> -->

            <div class="card">
                <div class="card-icon">🕒</div>
                <div>
                    <h4>Today's Appointments</h4>
                    <p><?= $todayAppointments ?></p>
                </div>
            </div>

            <div class="card">
                <div class="card-icon">🤰</div>
                <div>
                    <h4>Today's General Appointments</h4>
                    <p><?= $generalAppointments ?></p>
                </div>
            </div>
            
            <div class="card">
                <div class="card-icon">🤱</div>
                <div>
                    <h4>Today's Prenatal Appointments</h4>
                    <p><?= $prenatalAppointments ?></p>
                </div>
            </div>


        </div>

        <!-- TODAY'S APPOINTMENTS TABLE -->
        <h3 style="margin-bottom:15px;">Today's Appointment Queue</h3>

      <table id="appointmentsTable">
        <thead>
        <tr>
            <th>Patient Name</th>
            <th>Date</th>
            <th>Type</th>
            <th style="width:140px;">Action</th>
        </tr>
        </thead>
        <tbody>
        <?php if (mysqli_num_rows($todayList ) === 0): ?>
            <tr class="no-data">
                <td colspan="4" class="text-center">No checked-in appointments yet.</td>
            </tr>
        <?php else: ?>
            <?php while ($a = mysqli_fetch_assoc($todayList )): ?>
            <tr>
                <td><?= htmlspecialchars($a['patient_name']) ?></td>
                <td><?= $a['appointment_date'] ?></td>
                <td><?= strtoupper($a['type']) ?></td>
                <td>
                    <a href="dashboard-consultation.php?id=<?= $a['appointment_id'] ?>"
                       class="btn btn-success btn-sm">Consult</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
    </table>

    </main>
</div>

</body>
</html>
