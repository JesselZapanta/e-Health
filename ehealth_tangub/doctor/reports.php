<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Doctor Reports | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">

    <style>
        .layout {
            display: flex;
        }

        .main {
            flex: 1;
            padding: 25px;
        }

        /* PAGE TITLE */
        .page-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 25px;
        }

        /* REPORT GRID */
        .report-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 25px;
        }

        /* REPORT CARD */
        .card {
            background: #fff;
            padding: 24px;
            border-radius: 14px;
            box-shadow: var(--shadow);

            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card h3 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .card p {
            font-size: 14px;
            color: #555;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .card a {
            align-self: flex-start;
        }
    </style>
</head>
<body>

<div class="layout">

    <!-- SIDEBAR -->
    <?php require_once "../layouts/sidebar.php"; ?>

    <!-- MAIN CONTENT -->
    <main class="main">

        <!-- TOPBAR -->
        <?php require_once "../layouts/topbar.php"; ?>

        <h2 class="page-title">Doctor Reports</h2>

        <div class="report-grid">

            <!-- CONSULTATION REPORT -->
            <div class="card">
                <h3>Consultation Report</h3>
                <p>
                    Summary of all completed consultations handled by you,
                    including patient diagnosis, prescriptions, and notes.
                </p>

                <a href="report_consultations.php" class="btn-primary">
                    View Report
                </a>
            </div>

            <!-- PRENATAL REPORT -->
            <div class="card">
                <h3>Prenatal Report</h3>
                <p>
                    Overview of prenatal visits, maternal health records,
                    and pregnancy monitoring under your care.
                </p>

                <a href="report_prenatal.php" class="btn-primary">
                    View Report
                </a>
            </div>

        </div>

    </main>
</div>

</body>
</html>
