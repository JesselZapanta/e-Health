<?php
session_start();

if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: admin/dashboard.php");
            break;
        case 'doctor':
            header("Location: doctor/dashboard.php");
            break;
        case 'staff':
            header("Location: staff/dashboard.php");
            break;
        case 'patient':
            header("Location: patient/dashboard.php");
            break;
    }
    exit();
}
header("Location: auth/login.php");
