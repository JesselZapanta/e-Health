<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* ================================
   DASHBOARD METRICS
================================ */

$totalDoctors = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM users WHERE role='doctor'")
)['total'];

$totalStaff = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM users WHERE role='staff'")
)['total'];

$totalUsers = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM users")
)['total'];

$todayAppointments = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total 
         FROM appointments 
         WHERE appointment_date = CURDATE() 
         AND status = 'Approved'"
    )
)['total'];


$prenatalCases = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM appointments WHERE type = 'prenatal' AND status = 'Completed'")
)['total'];

$lowStock = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) total FROM inventory WHERE quantity <= minimum_stock")
)['total'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard | eHEALTH</title>
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

        .right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        /* DASHBOARD CARDS */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
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
    </style>
</head>
<body>

<div class="layout">

    <!-- ✅ REUSABLE SIDEBAR ONLY -->
    <?php require_once "../layouts/sidebar.php"; ?>

    <!-- MAIN CONTENT -->
    <main class="main">

        <!-- TOPBAR -->
        <?php require_once "../layouts/topbar.php"; ?>

        <!-- DASHBOARD CARDS -->
        <div class="dashboard-grid">

            <div class="card">
                <div class="card-icon">👥</div>
                <div>
                    <h4>Total Users</h4>
                    <p><?= $totalUsers ?></p>
                </div>
            </div>

            <div class="card">
                <div class="card-icon">🧑‍⚕️</div>
                <div>
                    <h4>Total Doctors</h4>
                    <p><?= $totalDoctors ?></p>
                </div>
            </div>

            <div class="card">
                <div class="card-icon">🧑‍⚕️</div>
                <div>
                    <h4>Total Health Staff</h4>
                    <p><?= $totalStaff ?></p>
                </div>
            </div>


            <div class="card">
                <div class="card-icon">📅</div>
                <div>
                    <h4>Today's Approved Appointments</h4>
                    <p><?= $todayAppointments ?></p>
                </div>
            </div>

            <div class="card">
                <div class="card-icon">🤰</div>
                <div>
                    <h4>Total Completed Prenatal</h4>
                    <p><?= $prenatalCases ?></p>
                </div>
            </div>

            <div class="card">
                <div class="card-icon">⚠️</div>
                <div>
                    <h4>Low Stock Alerts</h4>
                    <p><?= $lowStock ?></p>
                </div>
            </div>

        </div>

    </main>
</div>

</body>
</html>
