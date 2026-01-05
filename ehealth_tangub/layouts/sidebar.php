<?php
// layouts/sidebar.php
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit();
}

$role = $_SESSION['role'];
$current = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar">
    <div class="sidebar-header">
        <h2>eHEALTH</h2>
        <p class="subtitle">Tangub City</p>
    </div>

    <nav class="menu">

        <!-- ================= ADMIN MENU (REVISED ONLY) ================= -->
        <?php if ($role === 'admin'): ?>

            <a href="/ehealth_tangub/admin/dashboard.php" class="<?= $current=='dashboard.php'?'active':'' ?>">
                📊 <span>Dashboard</span>
            </a>

            <a href="/ehealth_tangub/admin/users.php" class="<?= $current=='users.php'?'active':'' ?>">
                👥 <span>User Management</span>
            </a>

            <a href="/ehealth_tangub/admin/patients.php" class="<?= $current=='patients.php'?'active':'' ?>">
                🧑‍⚕️ <span>Patient Records</span>
            </a>

            <a href="/ehealth_tangub/admin/consultations.php" class="<?= $current=='consultations.php'?'active':'' ?>">
                📋 <span>Consultation Records</span>
            </a>

            <a href="/ehealth_tangub/admin/prenatal.php" class="<?= $current=='prenatal.php'?'active':'' ?>">
                🤰 <span>Prenatal Records</span>
            </a>

            <a href="/ehealth_tangub/admin/inventory.php" class="<?= $current=='inventory.php'?'active':'' ?>">
                💊 <span>Inventory</span>
            </a>

            <a href="/ehealth_tangub/admin/reports.php" class="<?= $current=='reports.php'?'active':'' ?>">
                📊 <span>Reports</span>
            </a>

        <?php endif; ?>

        <!-- ================= DOCTOR MENU (UNCHANGED) ================= -->
        <?php if ($role === 'doctor'): ?>

            <a href="dashboard.php" class="<?= $current=='dashboard.php'?'active':'' ?>">
                📊 <span>Dashboard</span>
            </a>

            <a href="appointments.php">
                📅 <span>Appointments</span>
            </a>

            <a href="consultations.php">
                🩺 <span>Consultation</span>
            </a>

            <a href="prenatal.php">
                🤰 <span>Prenatal History</span>
            </a>

            <a href="patients.php">
                👥 <span>Patient History</span>
            </a>

            <a href="reports.php">
                📄 <span>Reports</span>
            </a>

        <?php endif; ?>

        <!-- ================= HEALTH STAFF MENU (UNCHANGED) ================= -->
        <?php if ($role === 'staff'): ?>

            <a href="dashboard.php" class="<?= $current=='dashboard.php'?'active':'' ?>">
                📊 <span>Dashboard</span>
            </a>

            <a href="appointments.php">
                📅 <span>Appointment Management</span>
            </a>

            <a href="availability.php">
                🗓️ <span>Doctor Availability</span>
            </a>

            <a href="verify_qr.php">
                🔍 <span>QR Verification</span>
            </a>

            <a href="inventory.php">
                💊 <span>Inventory</span>
            </a>

            <a href="patients.php">
                👥 <span>Patient Records</span>
            </a>

            <!-- ✅ NEW: Staff Consultation Records -->
    <a href="consultations.php" class="<?= $current=='consultations.php'?'active':'' ?>">
        📋 <span>Consultation Records</span>
    </a>

    <!-- ✅ NEW: Staff Prenatal Records -->
    <a href="prenatal.php" class="<?= $current=='prenatal.php'?'active':'' ?>">
        🤰 <span>Prenatal Records</span>
    </a>


            <a href="reports.php">
                📄 <span>Reports</span>
            </a>

        <?php endif; ?>

        <!-- ================= PATIENT MENU (UNCHANGED) ================= -->
        <?php if ($role === 'patient'): ?>

            <a href="dashboard.php" class="<?= $current=='dashboard.php'?'active':'' ?>">
                📊 <span>Dashboard</span>
            </a>

            <a href="appointments.php">
                📅 <span>Appointments</span>
            </a>

            <a href="consultations.php">
                🩺 <span>Consultation Records</span>
            </a>

            <a href="prenatal.php">
                🤰 <span>Prenatal Tracker</span>
            </a>

            <a href="profile.php">
                ⚙️ <span>Profile / Settings</span>
            </a>

        <?php endif; ?>

        <hr style="margin:15px 0;border-color:rgba(255,255,255,0.2);">

        <a href="../auth/logout.php">
            🚪 <span>Logout</span>
        </a>

    </nav>
</aside>

<style>
.sidebar {
    width: 250px;
    background: var(--primary);
    color: #fff;
    min-height: 100vh;
    padding: 20px;
}

.sidebar-header {
    text-align: center;
    margin-bottom: 30px;
}

.sidebar-header h2 {
    margin-bottom: 5px;
}

.sidebar-header .subtitle {
    font-size: 12px;
    opacity: 0.8;
}

.menu a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px;
    color: #fff;
    text-decoration: none;
    border-radius: 8px;
    margin-bottom: 6px;
    font-size: 14px;
}

.menu a span {
    white-space: nowrap;
}

.menu a:hover,
.menu a.active {
    background: var(--primary-dark);
}
</style>
