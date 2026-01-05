<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$users = mysqli_query($conn, "SELECT user_id, full_name, email, role, status FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Management | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">
    <style>
        .page-header {
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:20px;
        }

        .card {
            background:#fff;
            padding:20px;
            border-radius:14px;
            box-shadow:var(--shadow);
        }

        table {
            width:100%;
            border-collapse:collapse;
        }

        th, td {
            padding:14px;
            border-bottom:1px solid #eee;
            text-align:left;
            font-size:14px;
        }

        th {
            background:#f8fafc;
        }

        .status {
            padding:6px 12px;
            border-radius:12px;
            font-size:12px;
            font-weight:600;
        }

        .status.active {
            background:#dcfce7;
            color:#166534;
        }

        .status.inactive {
            background:#fee2e2;
            color:#991b1b;
        }

        /* MODAL */
        .modal {
            display:none;
            position:fixed;
            inset:0;
            background:rgba(0,0,0,0.45);
            justify-content:center;
            align-items:center;
            z-index:999;
        }

        .modal-content {
            background:#fff;
            width:420px;
            border-radius:14px;
            padding:25px;
            box-shadow:var(--shadow);
        }

        .modal-header {
            font-size:18px;
            font-weight:600;
            margin-bottom:15px;
            display:flex;
            justify-content:space-between;
            align-items:center;
        }

        .form-group {
            margin-bottom:14px;
        }

        .form-group input,
        .form-group select {
            width:100%;
            padding:10px;
            border-radius:8px;
            border:1px solid #ccc;
        }

        .form-group input.error {
            border-color:#dc2626;
        }

        .error-text {
            font-size:12px;
            color:#dc2626;
            display:none;
        }

        .modal-actions {
            display:flex;
            justify-content:space-between;
            margin-top:15px;
        }
    </style>
</head>
<body>

<div class="layout">
<?php require_once "../layouts/sidebar.php"; ?>

<main class="main">
<?php require_once "../layouts/topbar.php"; ?>

<div class="page-header">
    <h2>User Management</h2>
    <button class="btn-primary" onclick="openModal()">➕ Create User</button>
</div>

<div class="card">
<table>
<thead>
<tr>
    <th>Name</th>
    <th>Email</th>
    <th>Role</th>
    <th>Status</th>
    <th style="width:120px;">Action</th>
</tr>
</thead>
<tbody>
<?php while ($u = mysqli_fetch_assoc($users)): ?>
<tr>
    <td><?= htmlspecialchars($u['full_name']) ?></td>
    <td><?= htmlspecialchars($u['email']) ?></td>
    <td><?= ucfirst($u['role']) ?></td>
    <td>
        <span class="status <?= $u['status'] ?>">
            <?= ucfirst($u['status']) ?>
        </span>
    </td>
    <td>
        <a href="user_edit.php?id=<?= $u['user_id'] ?>" class="btn btn-success btn-sm">Edit</a>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>

</main>
</div>

<!-- CREATE USER MODAL -->
<div class="modal" id="createUserModal">
<form class="modal-content" method="POST" action="user_create.php" onsubmit="return validateForm()">

<div class="modal-header">
    <span>Create User</span>
    <span style="cursor:pointer;" onclick="closeModal()">✖</span>
</div>

<div class="form-group">
    <input type="text" name="first_name" placeholder="First Name" required>
</div>

<div class="form-group">
    <input type="text" name="middle_initial" placeholder="Middle Initial" maxlength="1">
</div>

<div class="form-group">
    <input type="text" name="last_name" placeholder="Last Name" required>
</div>

<div class="form-group">
    <input type="email" id="email" name="email" placeholder="Email Address" required>
    <div class="error-text" id="emailError">Email must contain @</div>
</div>

<div class="form-group">
    <input type="password" id="password" name="password" placeholder="Password" required>
</div>

<div class="form-group">
    <input type="password" id="confirmPassword" placeholder="Confirm Password" required>
    <div class="error-text" id="passwordError">Passwords do not match</div>
</div>

<div class="form-group">
    <select name="role" required>
        <option value="doctor">Doctor</option>
        <option value="staff">Staff</option>
        <option value="patient">Patient</option>
        <option value="admin">Admin</option>
    </select>
</div>

<div class="modal-actions">
    <button type="button" class="btn-danger" onclick="closeModal()">Cancel</button>
    <button type="submit" class="btn-primary">Create</button>
</div>

</form>
</div>

<script>
function openModal() {
    document.getElementById('createUserModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('createUserModal').style.display = 'none';
}

function validateForm() {
    let valid = true;

    const email = document.getElementById('email');
    const emailError = document.getElementById('emailError');
    const pass = document.getElementById('password').value;
    const confirm = document.getElementById('confirmPassword').value;
    const passError = document.getElementById('passwordError');

    if (!email.value.includes('@')) {
        email.classList.add('error');
        emailError.style.display = 'block';
        valid = false;
    } else {
        email.classList.remove('error');
        emailError.style.display = 'none';
    }

    if (pass !== confirm) {
        passError.style.display = 'block';
        valid = false;
    } else {
        passError.style.display = 'none';
    }

    return valid;
}
</script>

</body>
</html>
