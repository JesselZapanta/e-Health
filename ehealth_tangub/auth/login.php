<?php
require_once "../config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email=? AND status='active'");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($res)) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            header("Location: ../index.php");
            exit();
        }
    }
    $error = "Invalid email or password.";
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>eHEALTH Login</title>
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
      width: 360px;
      background: #fff;
      padding: 30px;
      border-radius: 14px;
      box-shadow: var(--shadow);
    }
    .auth-header {
      text-align: center;
      margin-bottom: 20px;
    }
    .auth-header h2 {
      margin-top: 10px;
    }
  </style>
</head>
<body>

<div class="auth-card">
  <div class="auth-header">
    <h2>eHEALTH</h2>
    <p>Tangub City Health System</p>
  </div>

  <?php if ($error): ?>
    <script>window.onload = () => showModal("<?= $error ?>");</script>
  <?php endif; ?>

  <form method="POST">
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" required>
    </div>

    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>

    <button class="btn-primary" style="width:100%">Login</button>
  </form>

  <p style="margin-top:15px;text-align:center">
    Patient? <a href="register.php">Register here</a>
  </p>
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
