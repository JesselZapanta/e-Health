<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$id = (int)$_GET['id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE user_id=$id"));
?>
<!DOCTYPE html>
<html>
<head>
<title>Edit User | eHEALTH</title>
<link rel="stylesheet" href="../assets/css/ui.css">
</head>
<body>

<div class="layout">
<?php require_once "../layouts/sidebar.php"; ?>

<main class="main">
<?php require_once "../layouts/topbar.php"; ?>

<div class="card" style="max-width:600px;">
<h2>Edit User</h2>

<form method="POST" action="user_update.php">

<input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">

<div class="form-group">
    <label>Full Name</label>
    <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
</div>

<div class="form-group">
    <label>Email (read-only)</label>
    <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
</div>

<div class="form-group">
    <label>Role</label>
    <select name="role">
        <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
        <option value="doctor" <?= $user['role']=='doctor'?'selected':'' ?>>Doctor</option>
        <option value="staff" <?= $user['role']=='staff'?'selected':'' ?>>Staff</option>
        <option value="patient" <?= $user['role']=='patient'?'selected':'' ?>>Patient</option>
    </select>
</div>

<div class="form-group">
    <label>Status</label>
    <select name="status">
        <option value="active" <?= $user['status']=='active'?'selected':'' ?>>Active</option>
        <option value="inactive" <?= $user['status']=='inactive'?'selected':'' ?>>Inactive</option>
    </select>
</div>

<div style="margin-top:20px;">
    <button class="btn-primary">Save Changes</button>
    <a href="users.php" class="btn btn-danger">Back</a>
</div>

</form>
</div>

</main>
</div>

</body>
</html>
