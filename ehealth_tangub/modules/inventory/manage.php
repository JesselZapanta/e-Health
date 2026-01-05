<?php
require_once "../../config/database.php";

if (!isset($_SESSION)) {
    session_start();
}

if (!in_array($_SESSION['role'], ['admin','staff'])) {
    header("Location: /ehealth_tangub/auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory Management</title>
    <link rel="stylesheet" href="/ehealth_tangub/assets/css/ui.css">
</head>
<body>

<div style="display:flex">
    <?php require_once "../../layouts/sidebar.php"; ?>

    <main style="flex:1;padding:25px">
        <?php require_once "../../layouts/topbar.php"; ?>

        <h3>Inventory Management</h3>

        <!-- YOUR EXISTING CRUD LOGIC CONTINUES HERE -->

    </main>
</div>

</body>
</html>
