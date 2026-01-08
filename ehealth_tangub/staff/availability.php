<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

/* Fetch doctors */
$doctors = mysqli_query($conn, "
    SELECT user_id, full_name 
    FROM users 
    WHERE role = 'doctor' AND status = 'active'
");

/* Fetch availability records */
$availability = mysqli_query($conn, "
    SELECT da.*, u.full_name 
    FROM doctor_availability da
    JOIN users u ON da.doctor_id = u.user_id
    ORDER BY da.available_date DESC, da.start_time
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Doctor Availability | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">
</head>
<body>

<div class="layout">
<?php require "../layouts/sidebar.php"; ?>

<main class="main">
<?php require "../layouts/topbar.php"; ?>

<h2>Doctor Availability</h2>

<div class="card" style="margin-bottom:25px;">
<form method="POST" action="save_availabity.php" class="form-grid">
<!-- <form method="POST" action="save_availabity.php" > -->

    <div class="form-group">
        <label>Doctor</label>
        <select name="doctor_id" required>
            <option value="">-- Select Doctor --</option>
            <?php while ($d = mysqli_fetch_assoc($doctors)): ?>
                <option value="<?= $d['user_id'] ?>">
                    <?= htmlspecialchars($d['full_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="form-group">
        <label>Date</label>
        <input type="date" name="available_date" required>
    </div>

    <div class="form-group">
        <label>Time</label>
        <select name="time" required>
            <option value="morning">Morning</option>
            <option value="afternoon">Afternoon</option>
        </select>
    </div>

    <div class="form-group">
        <label>Slots</label>
        <input type="text" name="slots" required>
    </div>

    <div class="form-group full">
        <button class="btn-primary">Save Availability</button>
    </div>
</form>
</div>

<div class="card">
    <!-- Search Input -->
    <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
        <div class="form-group" style="flex: 1; margin-right: 10px;">
            <label>Search Doctor</label>
            <input type="text" id="searchInput" placeholder="Type something..." />
        </div>

        <!-- Status Filter -->
        <div class="form-group">
            <label>Filter by Status</label>
            <select id="statusFilter">
                <option value="all">All</option>
                <option value="available">Available</option>
                <option value="booked">Booked</option>
            </select>
        </div>
    </div>

    <table id="availabilityTable">
        <thead>
            <tr>
                <th>Doctor</th>
                <th>Date</th>
                <th>Time of the Day</th>
                <th>Slots</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($availability) === 0): ?>
            <tr>
                <td colspan="5" style="text-align:center;">No availability records found.</td>
            </tr>
            <?php endif; ?>

            <?php while ($a = mysqli_fetch_assoc($availability)): ?>
            <tr>
                <td><?= htmlspecialchars($a['full_name']) ?></td>
                <td><?= $a['available_date'] ?></td>
                <td><?= strtoupper($a['time']) ?></td>
                <td><?= $a['slots'] ?></td>
                <td>
                    <?php if ($a['slots'] > 0): ?>
                        <span class="status active">Available</span>
                    <?php else: ?>
                        <span class="status">Booked</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</main>
</div>
<script>
const searchInput = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');
const table = document.getElementById('availabilityTable').getElementsByTagName('tbody')[0];

function filterTable() {
    const filterText = searchInput.value.toLowerCase();
    const statusValue = statusFilter.value;
    const rows = table.getElementsByTagName('tr');

    let visibleCount = 0;

    for (let row of rows) {
        const cells = row.getElementsByTagName('td');
        if (cells.length === 0) continue;

        const doctorName = cells[0].textContent.toLowerCase();
        const statusText = cells[4].textContent.toLowerCase().trim();

        const matchesDoctor = doctorName.includes(filterText);
        const matchesStatus = (statusValue === 'all') || (statusText === statusValue);

        const showRow = matchesDoctor && matchesStatus;
        row.style.display = showRow ? '' : 'none';

        if (showRow) visibleCount++;
    }

    // If no visible rows, show a "NO Data found" row
    let noDataRow = document.getElementById('noDataRow');
    if (visibleCount === 0) {
        if (!noDataRow) {
            noDataRow = document.createElement('tr');
            noDataRow.id = 'noDataRow';
            noDataRow.innerHTML = `<td colspan="5" style="text-align:center;">NO Data found</td>`;
            table.appendChild(noDataRow);
        }
    } else {
        if (noDataRow) {
            noDataRow.remove();
        }
    }
}

searchInput.addEventListener('input', filterTable);
statusFilter.addEventListener('change', filterTable);
</script>


</body>
</html>
