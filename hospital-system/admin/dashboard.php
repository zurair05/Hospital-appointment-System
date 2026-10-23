<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/admin.php';
requireAdmin();
$db = getDatabaseConnection();
$patients = (int) $db->query('SELECT COUNT(*) FROM patients')->fetchColumn();
$doctors = (int) $db->query('SELECT COUNT(*) FROM doctors')->fetchColumn();
$appointments = (int) $db->query('SELECT COUNT(*) FROM appointments')->fetchColumn();
$pending = (int) $db->query("SELECT COUNT(*) FROM appointments WHERE status = 'Pending'")->fetchColumn();
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Dashboard</title><link rel="stylesheet" href="../css/styles.css"></head>
<body><header class="header"><nav class="navbar"><div class="logo">🏥 HMS Admin</div><div class="nav-links"><a href="dashboard.php" class="active">Dashboard</a><a href="logout.php">Logout</a></div></nav></header>
<div class="layout"><aside class="sidebar"><h3>Admin Panel</h3><ul><li><a class="active" href="dashboard.php">📊 Dashboard</a></li><li><a href="patients.php">🧑 Patients</a></li><li><a href="doctors.php">👨‍⚕️ Doctors</a></li><li><a href="appointments.php">📅 Appointments</a></li></ul></aside><main class="main-content"><h2>Admin Dashboard</h2><?= sessionStatusHtml() ?><div class="stats"><div class="stat-card"><h3><?= $patients ?></h3><p>Total Patients</p></div><div class="stat-card"><h3><?= $doctors ?></h3><p>Total Doctors</p></div><div class="stat-card"><h3><?= $appointments ?></h3><p>Total Appointments</p></div><div class="stat-card"><h3><?= $pending ?></h3><p>Pending Appointments</p></div></div></main></div><footer class="footer"><p>&copy; 2026 Hospital Appointment System.</p></footer></body></html>
