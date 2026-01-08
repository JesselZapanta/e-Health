<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

/* Fetch all appointments */
$appointments = mysqli_query($conn, "
    SELECT 
        a.appointment_id,
        a.appointment_date,
        a.appointment_time,
        a.status,
        u.full_name AS patient_name,
        d.full_name AS doctor_name
    FROM appointments a
    JOIN patients p ON a.patient_id = p.patient_id
    JOIN users u ON p.user_id = u.user_id
    JOIN users d ON a.doctor_id = d.user_id
    WHERE a.status IN ('Approved')
    ORDER BY a.appointment_id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Appointment Management | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">
    <style>
        .btn { cursor: pointer; }
    </style>
</head>
<body>

<div class="layout">
<?php require "../layouts/sidebar.php"; ?>
<main class="main">
<?php require "../layouts/topbar.php"; ?>

<h2>Appointment Management</h2>

<div class="card">
    <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
        <div class="form-group" style="flex: 1; margin-right: 10px;">
            <label>Search Patient</label>
            <input type="text" id="searchInput" placeholder="Type something..." />
        </div>

        <!-- Status Filter -->
        <div class="form-group">
            <label>Filter by Status</label>
            <select id="statusFilter">
                <option value="all">All</option>    
                <option value="Approved">Approved</option>
                <option value="Cancelled">Cancelled</option>
                <!-- <option value="Completed">Completed</option> -->
            </select>
        </div>
    </div>

    <table>
    <thead>
        <tr>
            <th>Patient</th>
            <th>Doctor</th>
            <th>Date</th>
            <th>Time of the day</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php if (mysqli_num_rows($appointments) === 0): ?>
        <tr>
            <td colspan="6" class="text-center">No pending appointment requests.</td>
        </tr>
    <?php endif; ?>

    <?php while ($a = mysqli_fetch_assoc($appointments)): ?>
        <tr>
            <td><?= htmlspecialchars($a['patient_name']) ?></td>
            <td><?= htmlspecialchars($a['doctor_name']) ?></td>
            <td><?= $a['appointment_date'] ?></td>
            <td><?= strtoupper($a['appointment_time']) ?></td>
            <td>
                <span style="
                    padding: 0.25em 0.5em;
                    border-radius: 0.25rem;
                    color: white;
                    <?= $a['status'] === 'Approved' ? 'background-color: blue;' : '' ?>
                    <?= $a['status'] === 'Completed' ? 'background-color: green;' : '' ?>
                    <?= $a['status'] === 'Cancelled' ? 'background-color: red;' : '' ?>
                ">
                    <?= $a['status'] ?>
                </span>
            </td>
            <td>
                <form method="POST" action="update_appointment.php" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $a['appointment_id'] ?>">
                    <?php if ($a['status'] === 'Approved'): ?>
                        <input type="hidden" name="action" value="cancel">
                        <button class="btn btn-danger btn-sm">Cancel</button>
                    <?php elseif ($a['status'] === 'Cancelled'): ?>
                        <input type="hidden" name="action" value="approve">
                        <button class="btn btn-success btn-sm">Re-Approve</button>
                    <?php else: ?>
                        <span><?= $a['status'] ?></span>
                    <?php endif; ?>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
    </table>
</div>

</main>
</div>

<script>
// References
const searchInput = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');
const tableBody = document.querySelector('table tbody');

function filterAppointments() {
    const searchValue = searchInput.value.toLowerCase().trim();
    const statusValue = statusFilter.value.toLowerCase();

    const rows = tableBody.querySelectorAll('tr');
    let anyVisible = false;

    rows.forEach(row => {
        // Skip "No pending appointment requests" row
        if (row.querySelector('td[colspan]')) return;

        const patientName = row.children[0].textContent.toLowerCase().trim();
        const status = row.children[4].textContent.toLowerCase().trim();

        const matchesSearch = patientName.includes(searchValue);
        const matchesStatus = (statusValue === 'all') || (status === statusValue);

        if (matchesSearch && matchesStatus) {
            row.style.display = '';
            anyVisible = true;
        } else {
            row.style.display = 'none';
        }
    });

    // Show "No data found" if nothing matches
    let noDataRow = document.getElementById('noDataRow');
    if (!anyVisible) {
        if (!noDataRow) {
            noDataRow = document.createElement('tr');
            noDataRow.id = 'noDataRow';
            noDataRow.innerHTML = '<td colspan="6" class="text-center">No data found.</td>';
            tableBody.appendChild(noDataRow);
        }
    } else if (noDataRow) {
        noDataRow.remove();
    }
}

// Event listeners
searchInput.addEventListener('input', filterAppointments);
statusFilter.addEventListener('change', filterAppointments);
</script>

</body>
</html>
