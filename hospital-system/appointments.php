<?php
declare(strict_types=1);
require_once __DIR__ . '/config/app.php';
requireLogin();

$db = getDatabaseConnection();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $statement = $db->prepare("UPDATE appointments SET status = 'Cancelled' WHERE id = :id AND patient_id = :patient_id AND status = 'Pending'");
    $statement->execute(['id' => (int) $_POST['cancel_id'], 'patient_id' => currentPatientId()]);
}
$statement = $db->prepare(
    'SELECT a.id, a.appointment_date, a.appointment_time, a.reason, a.status, d.name AS doctor_name, d.specialty
     FROM appointments a JOIN doctors d ON d.id = a.doctor_id
     WHERE a.patient_id = :patient_id ORDER BY a.appointment_date DESC, a.appointment_time DESC'
);
$statement->execute(['patient_id' => currentPatientId()]);
$appointments = $statement->fetchAll();
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>My Appointments</title><link rel="stylesheet" href="css/styles.css"></head>
<body><header class="header"><nav class="navbar"><div class="logo">🏥 HMS</div><div class="nav-links"><a href="index.html">Home</a><a href="book-appointment.php">Book Appointment</a><a href="logout.php">Logout</a></div></nav></header><div class="layout"><aside class="sidebar"><h3>Patient Panel</h3><ul><li><a href="book-appointment.php">📅 Book Appointment</a></li><li><a href="appointments.php" class="active">📋 My Appointments</a></li></ul></aside><main class="main-content"><div class="table-container"><?= sessionStatusHtml() ?><div class="table-header"><h2>My Appointments</h2><a href="book-appointment.php" class="btn btn-primary">New Appointment</a></div><table><thead><tr><th>Doctor</th><th>Specialty</th><th>Date</th><th>Time</th><th>Reason</th><th>Status</th><th>Action</th></tr></thead><tbody><?php if (!$appointments): ?><tr><td colspan="7">No appointments found.</td></tr><?php endif; ?><?php foreach ($appointments as $appointment): ?><tr><td><?= e($appointment['doctor_name']) ?></td><td><?= e($appointment['specialty']) ?></td><td><?= e($appointment['appointment_date']) ?></td><td><?= e($appointment['appointment_time']) ?></td><td><?= e($appointment['reason']) ?></td><td><?= e($appointment['status']) ?></td><td><?php if ($appointment['status'] === 'Pending'): ?><form method="post"><input type="hidden" name="cancel_id" value="<?= e($appointment['id']) ?>"><button class="btn btn-danger btn-sm">Cancel</button></form><?php else: ?>-<?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></main></div><footer class="footer"><p>&copy; 2026 Hospital Appointment System.</p></footer></body></html>
