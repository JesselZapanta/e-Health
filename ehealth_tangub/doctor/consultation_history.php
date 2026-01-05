<?php
require_once "../config/database.php";

$patient_id = intval($_GET['patient_id']);

$history = mysqli_query(
    $conn,
    "SELECT diagnosis, prescription, notes, created_at
     FROM consultations
     WHERE patient_id = $patient_id
     ORDER BY created_at DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Consultation History</title>
    <link rel="stylesheet" href="../assets/css/ui.css">
</head>
<body>

<div class="layout">
<?php require_once "../layouts/sidebar.php"; ?>

<main class="main">
<?php require_once "../layouts/topbar.php"; ?>

<h3>Consultation History</h3>

<table>
<tr>
    <th>Date</th>
    <th>Diagnosis</th>
    <th>Prescription</th>
    <th>Notes</th>
</tr>

<?php while ($row = mysqli_fetch_assoc($history)): ?>
<tr>
    <td><?= $row['created_at'] ?></td>
    <td><?= nl2br($row['diagnosis']) ?></td>
    <td><?= nl2br($row['prescription']) ?></td>
    <td><?= nl2br($row['notes']) ?></td>
</tr>
<?php endwhile; ?>

</table>

</main>
</div>

</body>
</html>
