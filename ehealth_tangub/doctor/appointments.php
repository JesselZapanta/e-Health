<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];

$appointments = mysqli_query($conn, "
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
    ORDER BY i.appointment_id DESC
");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Doctor Appointments | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">
</head>
<body>

<div class="layout">
<?php require "../layouts/sidebar.php"; ?>
<main class="main">
<?php require "../layouts/topbar.php"; ?>

<h2 style="margin-bottom: 2rem;">Approved and Check-in Appointments</h2>

<div class="card">
    <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
        <div class="form-group" style="flex: 1; margin-right: 10px;">
            <label>Search Patient</label>
            <input type="text" id="searchInput" placeholder="Type something..." />
        </div>

        <div class="form-group">
            <label>Filter by Type</label>
            <select id="typeFilter">
                <option value="all">All</option>
                <option value="GENERAL">General</option>
                <option value="PRENATAL">Prenatal</option>
            </select>
        </div>
    </div>

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
        <?php if (mysqli_num_rows($appointments) === 0): ?>
            <tr class="no-data">
                <td colspan="4" class="text-center">No approved appointments yet.</td>
            </tr>
        <?php else: ?>
            <?php while ($a = mysqli_fetch_assoc($appointments)): ?>
            <tr>
                <td><?= htmlspecialchars($a['patient_name']) ?></td>
                <td><?= $a['appointment_date'] ?></td>
                <td><?= strtoupper($a['type']) ?></td>
                <td>
                    <a href="consultation.php?id=<?= $a['appointment_id'] ?>"
                       class="btn btn-success btn-sm">Consult</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
const searchInput = document.getElementById('searchInput');
const typeFilter = document.getElementById('typeFilter');
const tableBody = document.getElementById('appointmentsTable').getElementsByTagName('tbody')[0];

function filterTable() {
    const searchText = searchInput.value.toLowerCase().trim();
    const typeValue = typeFilter.value.toLowerCase().trim();

    let anyVisible = false;

    for (let row of tableBody.rows) {
        if(row.classList.contains('no-data-row')) continue; // skip placeholder row

        const patientName = row.cells[0].textContent.toLowerCase().trim();
        const rowType = row.cells[2].textContent.toLowerCase().trim();

        const matchesSearch = patientName.includes(searchText);
        const matchesType = typeValue === 'all' || rowType === typeValue;

        if (matchesSearch && matchesType) {
            row.style.display = '';
            anyVisible = true;
        } else {
            row.style.display = 'none';
        }
    }

    // Handle No Data Found row
    let noDataRow = tableBody.querySelector('.no-data-row');
    if (!noDataRow) {
        noDataRow = document.createElement('tr');
        noDataRow.classList.add('no-data-row');
        noDataRow.innerHTML = '<td colspan="4" class="text-center no-data">No data found.</td>';
        tableBody.appendChild(noDataRow);
    }
    noDataRow.style.display = anyVisible ? 'none' : '';
}

// Attach events
searchInput.addEventListener('input', filterTable);
typeFilter.addEventListener('change', filterTable);
</script>


</main>
</div>

</body>
</html>
