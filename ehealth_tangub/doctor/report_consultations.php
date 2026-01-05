<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];

/* ================================
   FILTERS
================================ */
$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';
$search = $_GET['search'] ?? '';

$where = "WHERE a.doctor_id = $doctor_id AND a.status = 'Completed'";

if ($from && $to) {
    $where .= " AND a.appointment_date BETWEEN '$from' AND '$to'";
}

if ($search) {
    $safe = mysqli_real_escape_string($conn, $search);
    $where .= " AND u.full_name LIKE '%$safe%'";
}

/* ================================
   EXPORT CSV
================================ */
if (isset($_GET['export']) && $_GET['export'] === 'csv') {

    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=consultation_report.csv");

    $output = fopen("php://output", "w");
    fputcsv($output, ["Date", "Patient", "Symptoms", "Diagnosis", "Prescription"]);

    $exportQuery = mysqli_query(
        $conn,
        "SELECT a.appointment_date, u.full_name,
                c.symptoms, c.diagnosis, c.prescription
         FROM consultations c
         JOIN appointments a ON c.appointment_id = a.appointment_id
         JOIN patients p ON a.patient_id = p.patient_id
         JOIN users u ON p.user_id = u.user_id
         $where
         ORDER BY a.appointment_date DESC"
    );

    while ($row = mysqli_fetch_assoc($exportQuery)) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit();
}

/* ================================
   MAIN QUERY
================================ */
$consultations = mysqli_query(
    $conn,
    "SELECT a.appointment_date, a.appointment_time,
            u.full_name AS patient,
            c.symptoms, c.diagnosis, c.prescription, c.notes
     FROM consultations c
     JOIN appointments a ON c.appointment_id = a.appointment_id
     JOIN patients p ON a.patient_id = p.patient_id
     JOIN users u ON p.user_id = u.user_id
     $where
     ORDER BY a.appointment_date DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Consultation Report | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">

    <style>
        .layout { display: flex; }
        .main { flex: 1; padding: 25px; }

        .page-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .filter-box {
            background: #fff;
            padding: 20px;
            border-radius: 14px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        table {
            width: 100%;
            background: #fff;
            border-radius: 14px;
            box-shadow: var(--shadow);
            border-collapse: collapse;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
            vertical-align: top;
        }

        th {
            background: #f8fafc;
            font-weight: 600;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
    </style>
</head>
<body>

<div class="layout">

    <!-- SIDEBAR -->
    <?php require_once "../layouts/sidebar.php"; ?>

    <!-- MAIN -->
    <main class="main">

        <!-- TOPBAR -->
        <?php require_once "../layouts/topbar.php"; ?>

        <h2 class="page-title">Consultation Report</h2>

        <!-- FILTERS -->
        <form method="GET" class="filter-box">
            <div class="filter-grid">
                <div class="form-group">
                    <label>From Date</label>
                    <input type="date" name="from" value="<?= $from ?>">
                </div>

                <div class="form-group">
                    <label>To Date</label>
                    <input type="date" name="to" value="<?= $to ?>">
                </div>

                <div class="form-group">
                    <label>Patient Name</label>
                    <input type="text" name="search" placeholder="Search patient"
                           value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>

            <div class="actions">
                <button class="btn-primary">Apply Filter</button>

                <a class="btn-primary"
                   href="?from=<?= $from ?>&to=<?= $to ?>&search=<?= urlencode($search) ?>&export=csv">
                    Export CSV
                </a>
            </div>
        </form>

        <!-- TABLE -->
        <table>
            <tr>
                <th>Date</th>
                <th>Patient</th>
                <th>Symptoms</th>
                <th>Diagnosis</th>
                <th>Prescription</th>
                <th>Notes</th>
            </tr>

            <?php if (mysqli_num_rows($consultations) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($consultations)): ?>
                    <tr>
                        <td><?= $row['appointment_date'] ?></td>
                        <td><?= htmlspecialchars($row['patient']) ?></td>
                        <td><?= nl2br(htmlspecialchars($row['symptoms'])) ?></td>
                        <td><?= nl2br(htmlspecialchars($row['diagnosis'])) ?></td>
                        <td><?= nl2br(htmlspecialchars($row['prescription'])) ?></td>
                        <td><?= nl2br(htmlspecialchars($row['notes'])) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No consultation records found.</td>
                </tr>
            <?php endif; ?>
        </table>

    </main>
</div>

</body>
</html>
