<?php
require_once "../config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

/* ================================
   FETCH USER + PATIENT INFO
================================ */
$user = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT u.full_name, u.email,
                p.patient_id, p.blood_type, p.height, p.weight
         FROM users u
         JOIN patients p ON u.user_id = p.user_id
         WHERE u.user_id = $user_id"
    )
);

/* ================================
   UPDATE PROFILE
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name  = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email      = mysqli_real_escape_string($conn, $_POST['email']);
    $blood_type = mysqli_real_escape_string($conn, $_POST['blood_type']);
    $height     = mysqli_real_escape_string($conn, $_POST['height']);
    $weight     = mysqli_real_escape_string($conn, $_POST['weight']);
    $password   = $_POST['password'];

    // Update users table
    mysqli_query(
        $conn,
        "UPDATE users
         SET full_name = '$full_name',
             email = '$email'
         WHERE user_id = $user_id"
    );

    // Update patients table
    mysqli_query(
        $conn,
        "UPDATE patients
         SET blood_type = '$blood_type',
             height = '$height',
             weight = '$weight'
         WHERE user_id = $user_id"
    );

    // Optional password update
    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        mysqli_query(
            $conn,
            "UPDATE users
             SET password = '$hashed'
             WHERE user_id = $user_id"
        );
    }

    $success = "Profile updated successfully.";

    // Refresh data
    $user = mysqli_fetch_assoc(
        mysqli_query(
            $conn,
            "SELECT u.full_name, u.email,
                    p.patient_id, p.blood_type, p.height, p.weight
             FROM users u
             JOIN patients p ON u.user_id = p.user_id
             WHERE u.user_id = $user_id"
        )
    );
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile Settings | eHEALTH</title>
    <link rel="stylesheet" href="../assets/css/ui.css">

    <style>
        .layout { display: flex; }
        .main { flex: 1; padding: 25px; }

        .card {
            background: #fff;
            padding: 25px;
            border-radius: 14px;
            box-shadow: var(--shadow);
            max-width: 600px;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="layout">

    <!-- SIDEBAR -->
    <?php require_once "../layouts/sidebar.php"; ?>

    <!-- MAIN -->
    <main class="main">

        <!-- TOPBAR -->
        <?php require_once "../layouts/topbar.php"; ?>

        <h2 class="page-title">Profile & Settings</h2>

        <div class="card">

            <?php if ($success): ?>
                <div class="alert-success"><?= $success ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert-error"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name"
                           value="<?= htmlspecialchars($user['full_name']) ?>"
                           required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email"
                           value="<?= htmlspecialchars($user['email']) ?>"
                           required>
                </div>

                <div class="form-group">
                    <label>New Password (leave blank to keep current)</label>
                    <input type="password" name="password">
                </div>

                <hr style="margin:20px 0;">

                <div class="form-group">
                    <label>Blood Type</label>
                    <input type="text" name="blood_type"
                           value="<?= htmlspecialchars($user['blood_type']) ?>">
                </div>

                <div class="form-group">
                    <label>Height (cm)</label>
                    <input type="text" name="height"
                           value="<?= htmlspecialchars($user['height']) ?>">
                </div>

                <div class="form-group">
                    <label>Weight (kg)</label>
                    <input type="text" name="weight"
                           value="<?= htmlspecialchars($user['weight']) ?>">
                </div>

                <div style="margin-top:20px;">
                    <button class="btn-primary">Save Changes</button>
                </div>

            </form>
        </div>

    </main>
</div>

</body>
</html>
