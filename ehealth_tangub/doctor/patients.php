<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];

/* ================================
   FETCH PATIENTS HANDLED BY DOCTOR
================================ */
$patients = mysqli_query(
    $conn,
    "SELECT 
        p.patient_id,
        u.full_name,
        -- Latest appointment date with this doctor
        (SELECT a.appointment_date 
         FROM appointments a 
         WHERE a.patient_id = p.patient_id AND a.doctor_id = $doctor_id
         ORDER BY a.appointment_date DESC
         LIMIT 1) AS appointment_date,
        -- Latest appointment type with this doctor
        (SELECT a.type 
         FROM appointments a 
         WHERE a.patient_id = p.patient_id AND a.doctor_id = $doctor_id
         ORDER BY a.appointment_date DESC
         LIMIT 1) AS appointment_type
    FROM patients p
    JOIN users u 
        ON p.user_id = u.user_id
    WHERE EXISTS (
        SELECT 1
        FROM appointments a
        WHERE a.patient_id = p.patient_id
          AND a.doctor_id = $doctor_id
    )
    ORDER BY u.full_name ASC"
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

        .details-empty {
            text-align: center;
            color: #666;
            padding-top: 80px;
        }
    </style>
</head>
<body>

<div class="layout">
    <?php require_once "../layouts/sidebar.php"; ?>

    <main class="main">
        <?php require_once "../layouts/topbar.php"; ?>

        <h3 style="margin-bottom:15px;">Patient Records</h3>

        <div class="split-view">

            <!-- LEFT: PATIENT LIST -->
            <div class="card">

                <div class="form-group" style="flex: 1; margin-bottom:10px;">
                    <label>Search Patient</label>
                    <input type="text" id="searchInput" placeholder="Type something..." />
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $hasPatients = false;
                        while ($p = mysqli_fetch_assoc($patients)):
                            $hasPatients = true;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($p['full_name']) ?></td>
                            <td><?= htmlspecialchars($p['appointment_date']) ?></td>
                            <td class="status <?= $p['appointment_type'] ?>"><?= ucfirst($p['appointment_type']) ?></td>
                            <td>
                                <button class="btn-view" onclick="loadPatient(<?= $p['patient_id'] ?>)">
                                    View
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>

                        <?php if (!$hasPatients): ?>
                        <tr class="no-data-row">
                            <td colspan="4" style="text-align:center; padding:10px;">No data found.</td>
                        </tr>
                        <?php endif; ?>
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
        if (row.classList.contains('no-data-row')) continue;

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
        noDataRow.innerHTML = '<td colspan="4" style="text-align:center; padding:10px;">No data found.</td>';
        table.tBodies[0].appendChild(noDataRow);
    }
    noDataRow.style.display = anyVisible ? 'none' : '';
}

searchInput.addEventListener('input', filterTable);
</script>

</body>
</html>
