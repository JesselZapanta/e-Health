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
        <label>Start Time</label>
        <input type="time" name="start_time" required>
    </div>

    <div class="form-group">
        <label>End Time</label>
        <input type="time" name="end_time" required>
    </div>

    <div class="form-group full">
        <button class="btn-primary">Save Availability</button>
    </div>

</form>
</div>

<div class="card">
<table>
<thead>
<tr>
    <th>Doctor</th>
    <th>Date</th>
    <th>Time Range</th>
    <th>Status</th>
</tr>
</thead>
<tbody>
<?php if (mysqli_num_rows($availability) === 0): ?>
<tr>
    <td colspan="4" style="text-align:center;">No availability records found.</td>
</tr>
<?php endif; ?>

<?php while ($a = mysqli_fetch_assoc($availability)): ?>
<tr>
    <td><?= htmlspecialchars($a['full_name']) ?></td>
    <td><?= $a['available_date'] ?></td>
    <td><?= date("h:i A", strtotime($a['start_time'])) ?> – <?= date("h:i A", strtotime($a['end_time'])) ?></td>
    <td>
        <span class="status active">Available</span>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>

</main>
</div>

</body>
</html>
