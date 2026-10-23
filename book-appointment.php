<?php
declare(strict_types=1);
require_once __DIR__ . '/config/app.php';
requireLogin();

$db = getDatabaseConnection();
$errors = [];
$message = '';
$doctors = $db->query('SELECT id, name, specialty FROM doctors ORDER BY name')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctorId = filter_input(INPUT_POST, 'doctor_id', FILTER_VALIDATE_INT);
    $date = (string) ($_POST['appointment_date'] ?? '');
    $time = (string) ($_POST['appointment_time'] ?? '');
    $reason = trim((string) ($_POST['reason'] ?? ''));
    if (!$doctorId || $date === '' || $time === '' || $reason === '') $errors[] = 'Doctor, date, time, and reason are required.';
    if ($date < date('Y-m-d')) $errors[] = 'Appointment date cannot be in the past.';
    if (!$errors) {
        try {
            $statement = $db->prepare(
                'INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason)
                 VALUES (:patient_id, :doctor_id, :appointment_date, :appointment_time, :reason)'
            );
            $statement->execute(['patient_id' => currentPatientId(), 'doctor_id' => $doctorId, 'appointment_date' => $date, 'appointment_time' => $time, 'reason' => $reason]);
            $message = 'Appointment requested successfully.';
        } catch (PDOException $exception) {
            $errors[] = $exception->getCode() === '23000' ? 'That doctor is already booked for this time.' : 'Could not book appointment.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Book Appointment</title><link rel="stylesheet" href="css/styles.css"></head>
<body><header class="header"><nav class="navbar"><div class="logo">🏥 HMS</div><div class="nav-links"><a href="index.html">Home</a><a href="appointments.php">My Appointments</a><a href="logout.php">Logout</a></div></nav></header>
<div class="layout"><aside class="sidebar"><h3>Patient Panel</h3><ul><li><a href="book-appointment.php" class="active">📅 Book Appointment</a></li><li><a href="appointments.php">📋 My Appointments</a></li></ul></aside><main class="main-content"><div class="form-container" style="max-width:700px"><h2>Book an Appointment</h2><?= sessionStatusHtml() ?><?php foreach ($errors as $error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endforeach; ?><?php if ($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
<form method="post"><div class="form-group"><label>Doctor</label><select name="doctor_id" required><option value="">Choose a doctor</option><?php foreach ($doctors as $doctor): ?><option value="<?= e($doctor['id']) ?>"><?= e($doctor['name'] . ' - ' . $doctor['specialty']) ?></option><?php endforeach; ?></select></div><div class="form-row"><div class="form-group"><label>Date</label><input type="date" name="appointment_date" min="<?= date('Y-m-d') ?>" required></div><div class="form-group"><label>Time</label><input type="time" name="appointment_time" required></div></div><div class="form-group"><label>Reason for Visit</label><textarea name="reason" rows="4" required></textarea></div><button class="btn btn-primary" style="width:100%">Book Appointment</button></form></div></main></div><footer class="footer"><p>&copy; 2026 Hospital Appointment System.</p></footer></body></html>
