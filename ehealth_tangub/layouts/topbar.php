<?php
// layouts/topbar.php
if (!isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit();
}

$role = ucfirst($_SESSION['role']);
$full_name = $_SESSION['full_name'];
?>

<header class="topbar">
    <div class="topbar-left">
        <strong><?= $role ?> Dashboard</strong>
    </div>

    <div class="topbar-right">

        <!-- NOTIFICATION BELL -->
        <div class="notif">
            🔔
            <div class="notif-dropdown">
                <p><strong>Notifications</strong></p>
                <hr>
                <p>No new notifications</p>
            </div>
        </div>

        <!-- USER NAME -->
        <span class="user-name"><?= htmlspecialchars($full_name) ?></span>

    </div>
</header>

<style>
/* ================================
   TOPBAR STYLES (REUSABLE)
================================ */
.topbar {
    background: #fff;
    padding: 15px 20px;
    border-radius: 12px;
    box-shadow: var(--shadow);
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

/* NOTIFICATIONS */
.notif {
    position: relative;
    cursor: pointer;
    font-size: 18px;
}

.notif-dropdown {
    display: none;
    position: absolute;
    right: 0;
    top: 28px;
    background: #fff;
    width: 260px;
    box-shadow: var(--shadow);
    border-radius: 10px;
    padding: 15px;
    font-size: 13px;
    z-index: 1000;
}

.notif:hover .notif-dropdown {
    display: block;
}

.user-name {
    font-size: 14px;
    font-weight: 500;
}
</style>
