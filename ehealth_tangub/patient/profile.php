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
                p.patient_id, p.birth_date, p.gender, p.blood_type, p.height, p.weight, 
                p.address, p.contact_number, p.is_pregnant
         FROM users u
         JOIN patients p ON u.user_id = p.user_id
         WHERE u.user_id = $user_id"
    )
);

// Split full name
$names = explode(' ', $user['full_name']);
$first_name = $names[0] ?? '';
$middle_name = $names[1] ?? '';
$last_name = $names[2] ?? '';

/* ================================
   UPDATE PROFILE
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $first_name  = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name   = trim($_POST['last_name']);
    $email       = trim($_POST['email']);
    $password    = $_POST['password'];
    $confirm     = $_POST['confirm'];

    $birth_date  = $_POST['birth_date'];
    $gender      = $_POST['gender'];
    $blood_type  = trim($_POST['blood_type']);
    $height      = trim($_POST['height']);
    $weight      = trim($_POST['weight']);
    $address     = trim($_POST['address']);
    $contact     = trim($_POST['contact']);
    $is_pregnant = isset($_POST['is_pregnant']) && $_POST['is_pregnant'] === '1' ? 1 : 0;

    // Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif ($password && $password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $full_name = trim("$first_name $middle_name $last_name");

        // Check email uniqueness
        $check = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        mysqli_stmt_bind_param($check, "si", $email, $user_id);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = "Email already registered.";
        } else {
            mysqli_query(
                $conn,
                "UPDATE users SET full_name='$full_name', email='$email' WHERE user_id=$user_id"
            );

            mysqli_query(
                $conn,
                "UPDATE patients SET birth_date='$birth_date', gender='$gender', blood_type='$blood_type',
                 height='$height', weight='$weight', address='$address', contact_number='$contact',
                 is_pregnant=$is_pregnant
                 WHERE user_id=$user_id"
            );

            if ($password) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                mysqli_query(
                    $conn,
                    "UPDATE users SET password='$hashed' WHERE user_id=$user_id"
                );
            }

            $success = "Profile updated successfully.";

            $user = mysqli_fetch_assoc(
                mysqli_query(
                    $conn,
                    "SELECT u.full_name, u.email,
                            p.patient_id, p.birth_date, p.gender, p.blood_type, p.height, p.weight, 
                            p.address, p.contact_number, p.is_pregnant
                     FROM users u
                     JOIN patients p ON u.user_id = p.user_id
                     WHERE u.user_id = $user_id"
                )
            );

            $names = explode(' ', $user['full_name']);
            $first_name = $names[0] ?? '';
            $middle_name = $names[1] ?? '';
            $last_name = $names[2] ?? '';
        }
    }
}

// Calculate age if birthdate exists
$age = '';
if (!empty($user['birth_date'])) {
    $dob = new DateTime($user['birth_date']);
    $today = new DateTime();
    $age = $dob->diff($today)->y;
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

        .alert-success { background:#dcfce7;color:#166534;padding:12px;border-radius:8px;margin-bottom:15px; }
        .alert-error   { background:#fee2e2;color:#991b1b;padding:12px;border-radius:8px;margin-bottom:15px; }

        .email-input:invalid,
        .email-input:focus:invalid { border-color: #dc2626; }
        input.input-error,
        input.input-error:focus { border-color: #dc2626; }

        #pregnant-field { display: none; }
    </style>
</head>
<body>

<div class="layout">
    <?php require_once "../layouts/sidebar.php"; ?>
    <main class="main">
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
                    <label>First Name *</label>
                    <input type="text" name="first_name" required value="<?= htmlspecialchars($first_name) ?>">
                </div>

                <div class="form-group">
                    <label>Middle Name</label>
                    <input type="text" name="middle_name" value="<?= htmlspecialchars($middle_name) ?>">
                </div>

                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" name="last_name" required value="<?= htmlspecialchars($last_name) ?>">
                </div>

                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" class="email-input" required value="<?= htmlspecialchars($user['email']) ?>">
                </div>

                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" id="password" name="password">
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" id="confirm" name="confirm">
                </div>

                <hr style="margin:20px 0;">

                <div class="form-group">
                    <label>Birthdate *</label>
                    <input type="date" name="birth_date" required value="<?= $user['birth_date'] ?>">
                </div>

                <?php if ($age !== ''): ?>
                    <div class="form-group">
                        <label>Age(Read only)</label>
                        <input type="text" value="<?= $age ?> years old" readonly>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Blood Type *</label>
                    <input type="text" name="blood_type" required value="<?= htmlspecialchars($user['blood_type']) ?>">
                </div>

                <div class="form-group">
                    <label>Gender *</label>
                    <select name="gender" required id="gender-select">
                        <option value="">Select Gender</option>
                        <option value="male" <?= $user['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
                        <option value="female" <?= $user['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>

                <div class="form-group" id="pregnant-field">
                    <label>Are you pregnant?</label>
                    <select name="is_pregnant">
                        <option value="0" <?= $user['is_pregnant'] == 0 ? 'selected' : '' ?>>No</option>
                        <option value="1" <?= $user['is_pregnant'] == 1 ? 'selected' : '' ?>>Yes</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Height (CM) *</label>
                    <input type="text" name="height" required value="<?= htmlspecialchars($user['height']) ?>">
                </div>

                <div class="form-group">
                    <label>Weight (KG) *</label>
                    <input type="text" name="weight" required value="<?= htmlspecialchars($user['weight']) ?>">
                </div>

                <div class="form-group">
                    <label>Address *</label>
                    <input type="text" name="address" required value="<?= htmlspecialchars($user['address']) ?>">
                </div>

                <div class="form-group">
                    <label>Contact *</label>
                    <input type="text" name="contact" required value="<?= htmlspecialchars($user['contact_number']) ?>">
                </div>

                <div style="margin-top:20px;">
                    <button class="btn-primary" style="width:100%">Save Changes</button>
                </div>

            </form>
        </div>
    </main>
</div>

<script>
const password = document.getElementById('password');
const confirm  = document.getElementById('confirm');

function checkPasswordMatch() {
    if (!password.value || !confirm.value) {
        password.classList.remove('input-error');
        confirm.classList.remove('input-error');
        return;
    }
    const mismatch = password.value !== confirm.value;
    password.classList.toggle('input-error', mismatch);
    confirm.classList.toggle('input-error', mismatch);
}

password.addEventListener('input', checkPasswordMatch);
confirm.addEventListener('input', checkPasswordMatch);

// Pregnant field toggle
const genderSelect = document.getElementById('gender-select');
const pregnantField = document.getElementById('pregnant-field');

function togglePregnancyField() {
    if (genderSelect.value === 'female') {
        pregnantField.style.display = 'block';
    } else {
        pregnantField.style.display = 'none';
        pregnantField.querySelector('select').value = '0';
    }
}

togglePregnancyField();
genderSelect.addEventListener('change', togglePregnancyField);
</script>

</body>
</html>
