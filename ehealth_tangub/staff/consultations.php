<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

/* ================================
   FETCH CONSULTATION LIST
================================ */

/* ================================
   FETCH CONSULTATION HISTORY
================================ */
$consultations = mysqli_query(
    $conn,
    "SELECT 
        c.consultation_id,
        a.appointment_date,
        a.appointment_time,
        u.full_name AS patient_name,
        a.type,
        c.diagnosis
     FROM consultations c
     JOIN appointments a ON c.appointment_id = a.appointment_id
     JOIN patients p ON a.patient_id = p.patient_id
     JOIN users u ON p.user_id = u.user_id
     ORDER BY a.appointment_date DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Consultation Records | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">
   <style>
        .layout { display: flex; }
        .main { flex: 1; padding: 25px; }

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

        th { background: #f8fafc; }

        .btn-sm {
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 12px;
            background: var(--primary);
            color: #fff;
            text-decoration: none;
        }

        .no-data { text-align: center; font-style: italic; color: #777; }
    </style>
</head>
<body>

<div class="layout">
    <?php require_once "../layouts/sidebar.php"; ?>

    <main class="main">
        <?php require_once "../layouts/topbar.php"; ?>
        <h2 style="margin-bottom:15px;">Consultation History</h2>

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

        <table id="consultationTable">
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Date</th>
                    <th>Diagnosis</th>
                    <th>Type</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (mysqli_num_rows($consultations) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($consultations)): ?>
                    <tr>
                        <td class="patient"><?= htmlspecialchars($row['patient_name']) ?></td>    
                        <td><?= $row['appointment_date'] ?></td>
                        <td><?= htmlspecialchars($row['diagnosis']) ?></td>
                        <td><?= strtoupper($row['type']) ?></td>
                        <td>
                            <a href="consultation_view.php?id=<?= $row['consultation_id'] ?>"
                               class="btn-sm">View</a>
                        </td>
                        <td class="type" style="display:none;" data-type="<?= strtoupper($row['type']) ?>"></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr class="no-data-row">
                    <td colspan="5" class="no-data">No consultation history found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

        </div>
        
    </main>
</div>

<script>
        const searchInput = document.getElementById('searchInput');
        const typeFilter = document.getElementById('typeFilter');
        const tableBody = document.getElementById('consultationTable').getElementsByTagName('tbody')[0];

        function filterTable() {
            const searchText = searchInput.value.toLowerCase().trim();
            const typeValue = typeFilter.value.toLowerCase().trim();
            let anyVisible = false;

            for (let row of tableBody.rows) {
                if (row.classList.contains('no-data-row')) continue;

                const patientName = row.querySelector('.patient').textContent.toLowerCase().trim();
                const rowType = row.querySelector('.type').dataset.type.toLowerCase().trim();

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
                noDataRow.innerHTML = '<td colspan="5" class="no-data">No data found.</td>';
                tableBody.appendChild(noDataRow);
            }
            noDataRow.style.display = anyVisible ? 'none' : '';
        }

        searchInput.addEventListener('input', filterTable);
        typeFilter.addEventListener('change', filterTable);
    </script>

</body>
</html>
