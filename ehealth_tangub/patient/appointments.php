<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* Get patient_id */
$p = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT patient_id FROM patients WHERE user_id = $user_id")
);
$patient_id = $p['patient_id'];

/* Fetch appointments */
$appointments = mysqli_query($conn, "
    SELECT 
        a.appointment_date,
        a.appointment_time,
        a.status,
        a.qr_code,
        u.full_name AS doctor_name
    FROM appointments a
    JOIN users u ON a.doctor_id = u.user_id
    WHERE a.patient_id = $patient_id
    ORDER BY a.appointment_id DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Appointments | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">
    <style>
        .status.approved { color: green; font-weight: bold; }
        .status.completed { color: blue; font-weight: bold; }
        .status.cancelled { color: red; font-weight: bold; }
        .no-data { text-align: center; font-style: italic; }
    </style>
</head>
<body>

<div class="layout">
    <?php require "../layouts/sidebar.php"; ?>
    <main class="main">
        <?php require "../layouts/topbar.php"; ?>

        <div class="card">
            <div class="page-header">
                <h2>My Appointments</h2>
                <a href="request_appointment.php" class="btn-primary">
                    ➕ Request Appointment
                </a>
            </div>

            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                <div class="form-group" style="flex: 1; margin-right: 10px;">
                    <label>Search Doctor</label>
                    <input type="text" id="searchInput" placeholder="Type something..." />
                </div>

                <div class="form-group">
                    <label>Filter by Status</label>
                    <select id="statusFilter">
                        <option value="all">All</option>
                        <option value="approved">Approved</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>

            <table class="data-table" id="appointmentsTable">
                <thead>
                    <tr>
                        <th>Doctor</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Code</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($appointments) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($appointments)): ?>
                            <tr>
                                <td class="doctor"><?= htmlspecialchars($row['doctor_name']) ?></td>
                                <td><?= htmlspecialchars($row['appointment_date']) ?></td>
                                <td><?= htmlspecialchars(strtoupper($row['appointment_time'])) ?></td>
                                <td><?= htmlspecialchars($row['qr_code']) ?></td>
                                <td>
                                    <span 
                                        class="status <?= strtolower(trim($row['status'])) ?>" 
                                        data-status="<?= strtolower(trim($row['status'])) ?>"
                                    >
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr class="no-data-row">
                            <td colspan="5" class="text-center">No appointments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        </div>
    </main>
</div>

<script>
const searchInput = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');
const table = document.getElementById('appointmentsTable').getElementsByTagName('tbody')[0];

function filterTable() {
    const searchText = searchInput.value.toLowerCase().trim();
    const statusValue = statusFilter.value.toLowerCase().trim();

    let anyVisible = false;

    for (let row of table.rows) {
        if(row.classList.contains('no-data-row')) continue; // skip placeholder row

        const doctorName = row.querySelector('.doctor').textContent.toLowerCase().trim();
        const statusText = row.querySelector('.status').dataset.status;

        const matchesSearch = doctorName.includes(searchText);
        const matchesStatus = statusValue === 'all' || statusText === statusValue;

        if (matchesSearch && matchesStatus) {
            row.style.display = '';
            anyVisible = true;
        } else {
            row.style.display = 'none';
        }
    }

    // Handle No Data Found row
    let noDataRow = table.querySelector('.no-data-row');
    if (!noDataRow) {
        noDataRow = document.createElement('tr');
        noDataRow.classList.add('no-data-row');
        noDataRow.innerHTML = '<td colspan="5" class="no-data">No data found.</td>';
        table.appendChild(noDataRow);
    }
    noDataRow.style.display = anyVisible ? 'none' : '';
}

// Attach events
searchInput.addEventListener('input', filterTable);
statusFilter.addEventListener('change', filterTable);
</script>

</body>
</html>
