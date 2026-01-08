<?php
require_once "../config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // User fields
    $first_name  = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name   = trim($_POST['last_name']);
    $email       = trim($_POST['email']);
    $password    = $_POST['password'];
    $confirm     = $_POST['confirm'];

    // Patient fields
    $birth_date  = $_POST['birth_date'];
    $gender      = $_POST['gender'];
    $height      = trim($_POST['height']);
    $weight      = trim($_POST['weight']);
    $blood_type  = trim($_POST['blood_type']);
    $address     = trim($_POST['address']);
    $contact     = trim($_POST['contact']);

    // Set default for pregnancy
    $is_pregnant = 0;
    if ($gender === 'female' && isset($_POST['is_pregnant'])) {
        $is_pregnant = $_POST['is_pregnant'] === '1' ? 1 : 0;
    }

    // Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {

        // Check if email exists
        $check = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = "Email already registered.";
        } else {

            $full_name = trim("$first_name $middle_name $last_name");
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO users (role, full_name, email, password, status)
                 VALUES ('patient', ?, ?, ?, 'active')"
            );
            mysqli_stmt_bind_param($stmt, "sss", $full_name, $email, $hash);
            mysqli_stmt_execute($stmt);

            $user_id = mysqli_insert_id($conn);

            // Insert patient
            $stmt2 = mysqli_prepare(
                $conn,
                "INSERT INTO patients
                (user_id, birth_date, gender, blood_type, height, weight, address, contact_number, is_pregnant)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param(
                $stmt2,
                "isssssssi",
                $user_id,
                $birth_date,
                $gender,
                $blood_type,
                $height,
                $weight,
                $address,
                $contact,
                $is_pregnant
            );
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
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 40px 0;
            background: linear-gradient(135deg, #0f766e, #134e4a);
        }

        .auth-card {
            width: 420px;
            max-height: calc(100vh - 80px);
            overflow-y: auto;
            background: #fff;
            padding: 30px;
            border-radius: 14px;
            box-shadow: var(--shadow);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .line {
            height: 1px;
            background: #09975c;
            margin: 20px 0;
        }

        /* EMAIL INVALID */
        .email-input:invalid,
        .email-input:focus:invalid {
            border-color: #dc2626;
        }

        /* PASSWORD MISMATCH (OVERRIDES ui.css) */
        input.input-error,
        input.input-error:focus {
            border-color: #dc2626;
        }
        a {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: var(--primary);
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
            <label>Middle Name</label>
            <input type="text" name="middle_name">
        </div>

        <div class="form-group">
            <label>Last Name *</label>
            <input type="text" name="last_name" required>
        </div>

        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" class="email-input" required>
        </div>

        <div class="form-group">
            <label>Password *</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
            <label>Confirm Password *</label>
            <input type="password" id="confirm" name="confirm" required>
        </div>

        <div class="line"></div>

        <div class="form-group">
            <label>Birthdate *</label>
            <input type="date" name="birth_date" required>
        </div>

        <div class="form-group">
            <label>Blood Type *</label>
            <input type="text" name="blood_type" required>
        </div>

        <div class="form-group">
            <label>Gender *</label>
            <select name="gender" required>
                <option value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
            </select>
        </div>

        <!-- PREGNANT FIELD -->
        <div class="form-group" id="pregnant-field" style="display:none;">
            <label>Are you pregnant?</label>
            <select name="is_pregnant">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>

        <div class="form-group">
            <label>Height (CM) *</label>
            <input type="text" name="height" required>
        </div>

        <div class="form-group">
            <label>Weight (KG) *</label>
            <input type="text" name="weight" required>
        </div>

        <div class="form-group">
            <label>Address *</label>
            <input type="text" name="address" required>
        </div>

        <div class="form-group">
            <label>Contact *</label>
            <input type="text" name="contact" required>
        </div>

        <button class="btn-primary" style="width:100%">Register</button>
    </form>
    <a href="login.php">back to login</a>
</div>

<!-- MODAL -->
<div class="modal" id="ui-modal">
    <div class="modal-content">
        <p id="ui-modal-text"></p>
        <button class="btn-primary" onclick="closeModal()">OK</button>
    </div>
</div>

<script>
/* PASSWORD MATCH */
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

/* SHOW/HIDE PREGNANT FIELD */
const genderSelect = document.querySelector('select[name="gender"]');
const pregnantField = document.getElementById('pregnant-field');

function togglePregnancyField() {
    if (genderSelect.value === 'female') {
        pregnantField.style.display = 'block';
    } else {
        pregnantField.style.display = 'none';
        const select = pregnantField.querySelector('select');
        if (select) select.value = '0';
    }
}

genderSelect.addEventListener('change', togglePregnancyField);
</script>

<script src="../assets/js/ui.js"></script>

</body>
</html>
