<?php
require_once "../config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $first_name = trim($_POST['first_name']);
    $middle_initial = trim($_POST['middle_initial']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    // Validation
    if (!str_contains($email, "@")) {
        $error = "Invalid email address.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {

        // Check if email already exists
        $check = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = "Email already registered.";
        } else {

            // Build full name safely
            $full_name = $first_name . " " . $middle_initial . " " . $last_name;

            // Hash password
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Insert user (PATIENT)
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO users (role, full_name, email, password, status)
                 VALUES ('patient', ?, ?, ?, 'active')"
            );
            mysqli_stmt_bind_param($stmt, "sss", $full_name, $email, $hash);
            mysqli_stmt_execute($stmt);

            $user_id = mysqli_insert_id($conn);

            // Create patient profile (optional health fields can be updated later)
            $stmt2 = mysqli_prepare(
                $conn,
                "INSERT INTO patients (user_id) VALUES (?)"
            );
            mysqli_stmt_bind_param($stmt2, "i", $user_id);
            mysqli_stmt_execute($stmt2);

            header("Location: login.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Patient Registration</title>
    <link rel="stylesheet" href="../assets/css/ui.css">
    <style>
        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #0f766e, #134e4a);
        }
        .auth-card {
            width: 420px;
            background: #fff;
            padding: 30px;
            border-radius: 14px;
            box-shadow: var(--shadow);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="auth-card">
    <h2>Patient Registration</h2>

    <?php if ($error): ?>
        <script>
            window.onload = () => showModal("<?= htmlspecialchars($error) ?>");
        </script>
    <?php endif; ?>

    <form method="POST">

        <div class="form-group">
            <label>First Name *</label>
            <input type="text" name="first_name" required>
        </div>

        <div class="form-group">
            <label>Middle Initial</label>
            <input type="text" name="middle_initial" maxlength="1">
        </div>

        <div class="form-group">
            <label>Last Name *</label>
            <input type="text" name="last_name" required>
        </div>

        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Password *</label>
            <input type="password" name="password" required>
        </div>

        <div class="form-group">
            <label>Confirm Password *</label>
            <input type="password" name="confirm" required>
        </div>

        <button class="btn-primary" style="width:100%">Register</button>
    </form>
</div>

<!-- MODAL -->
<div class="modal" id="ui-modal">
    <div class="modal-content">
        <p id="ui-modal-text"></p>
        <button class="btn-primary" onclick="closeModal()">OK</button>
    </div>
</div>

<script src="../assets/js/ui.js"></script>
</body>
</html>
