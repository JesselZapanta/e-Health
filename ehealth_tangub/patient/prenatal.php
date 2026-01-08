<?php
session_start();
require_once "../config/database.php";

// =====================
// ACCESS CONTROL
// =====================
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

// Logged-in patient user ID
$patient_user_id = $_SESSION['user_id'];

// Get the corresponding patient_id from patients table
$patient_res = mysqli_query($conn, "SELECT patient_id FROM patients WHERE user_id = $patient_user_id");
if (!$patient_res || mysqli_num_rows($patient_res) === 0) {
    die("Patient record not found.");
}

$patient_row = mysqli_fetch_assoc($patient_res);
$patient_id = $patient_row['patient_id'];

// =====================
// FETCH PRENATAL APPOINTMENTS
// =====================
$appointments = mysqli_query(
    $conn,
    "SELECT 
        a.appointment_id,
        a.patient_id,
        a.appointment_date,
        a.appointment_time,
        a.type,
        d.full_name AS doctor_name
    FROM appointments a
    JOIN users d ON a.doctor_id = d.user_id
    WHERE a.patient_id = $patient_id
      AND a.type = 'prenatal'
    ORDER BY a.appointment_date DESC, a.appointment_time DESC"
);


?>

<!DOCTYPE html>
<html>
<head>
    <title>Patient Records | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">

    <style>
        .layout { display: flex; }
        .main { flex: 1; padding: 25px; }

        /* SPLIT VIEW */
        .split-view {
            display: grid;
            grid-template-columns: 1.3fr 1fr;
            gap: 20px;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
            text-align: left;
        }

        th {
            background: #f8fafc;
        }

        .status.active { color: #16a34a; font-weight: bold; }
        .status.inactive { color: #dc2626; font-weight: bold; }

        .btn-view {
            padding: 6px 10px;
            background: var(--primary);
            color: #fff;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            border: none;
        }

        .btn-view:hover {
            background: var(--primary-dark);
        }

        /* DETAILS PANEL */
        .details-empty {
            text-align: center;
            color: #666;
            padding-top: 80px;
        }

        .detail-item {
            margin-bottom: 10px;
        }

        .detail-item span {
            font-weight: bold;
            display: inline-block;
            width: 140px;
        }
    </style>
</head>
<body>

<div class="layout">
    <?php require_once "../layouts/sidebar.php"; ?>

    <main class="main">
        <?php require_once "../layouts/topbar.php"; ?>

        <h3 style="margin-bottom:15px;">Prenatal Records</h3>

        <div class="split-view">

            <!-- LEFT: PATIENT LIST -->
            <div class="card">

                <div class="form-group" style="flex: 1; margin-right: 10px;">
                    <label>Search Doctor</label>
                    <input type="text" id="searchInput" placeholder="Type something..." />
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Doctor Name</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($appointments)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['doctor_name']) ?></td>
                                <td><?= htmlspecialchars($row['appointment_date']) ?></td>
                                <td><?= htmlspecialchars($row['type']) ?></td>
                                
                                <td>
                                    <button class="btn-view" onclick="loadPatient(<?= $row['patient_id'] ?>)">
                                        View
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>

                    </tbody>
                </table>
            </div>

            <!-- RIGHT: PATIENT DETAILS -->
            <div class="card" id="detailsPanel">
                <div class="details-empty">
                    Select a patient to view details
                </div>
            </div>

        </div>
    </main>
</div>

<script>
function loadPatient(id) {
    fetch("patient_details.php?id=" + id)
        .then(res => res.text())
        .then(html => {
            document.getElementById("detailsPanel").innerHTML = html;
        });
}

// =====================
// PATIENT SEARCH
// =====================
const searchInput = document.getElementById('searchInput');
const table = document.querySelector('.split-view .card table');

function filterTable() {
    const searchText = searchInput.value.toLowerCase().trim();
    let anyVisible = false;

    for (let row of table.tBodies[0].rows) {
        const patientName = row.cells[0].textContent.toLowerCase().trim();

        if (patientName.includes(searchText)) {
            row.style.display = '';
            anyVisible = true;
        } else {
            row.style.display = 'none';
        }
    }

    // Handle "No Data Found" row
    let noDataRow = table.tBodies[0].querySelector('.no-data-row');
    if (!noDataRow) {
        noDataRow = document.createElement('tr');
        noDataRow.classList.add('no-data-row');
        noDataRow.innerHTML = '<td colspan="4" class="no-data" style="text-align:center; padding:10px;">No data found.</td>';
        table.tBodies[0].appendChild(noDataRow);
    }
    noDataRow.style.display = anyVisible ? 'none' : '';
}

searchInput.addEventListener('input', filterTable);
</script>

</body>
</html>
