<?php
declare(strict_types=1);
require_once __DIR__ . '/config/app.php';

if (!empty($_SESSION['patient_id'])) {
    redirect('book-appointment.php');
}

$error = '';
$notice = sessionNoticeMessage(isset($_GET['session']) ? (string) $_GET['session'] : null);

if (isset($_GET['logged_out'])) {
    $notice = 'You have been logged out securely.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $statement = getDatabaseConnection()->prepare('SELECT id, name, email, password_hash FROM patients WHERE email = :email');
    $statement->execute(['email' => $email]);
    $patient = $statement->fetch();
    $storedHash = $patient !== false && is_string($patient['password_hash'] ?? null) ? (string) $patient['password_hash'] : null;

    if ($patient !== false && verifyPassword($password, $storedHash)) {
        refreshPasswordHash((int) $patient['id'], $password, (string) $storedHash, PATIENTS_TABLE);
        beginAuthenticatedSession('patient_id', (int) $patient['id'], [
            'patient_name' => (string) $patient['name'],
            'patient_email' => (string) $patient['email'],
        ]);
        redirect('book-appointment.php');
    }

    $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Login</title><link rel="stylesheet" href="css/styles.css"></head>
<body><header class="header"><nav class="navbar"><div class="logo">🏥 HMS</div><div class="nav-links"><a href="index.html">Home</a><a href="register.php">Register</a></div></nav></header>
<div class="form-container"><h2>Patient Login</h2><?php if (isset($_GET['registered'])): ?><div class="alert alert-success">Registration complete. You can now log in.</div><?php endif; ?><?php if ($notice): ?><div class="alert alert-info"><?= e($notice) ?></div><?php endif; ?><?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
<form method="post"><div class="form-group"><label>Email</label><input type="email" name="email" required autocomplete="email" autofocus></div><div class="form-group"><label>Password</label><input type="password" name="password" required autocomplete="current-password"></div><button class="btn btn-primary" style="width:100%">Login</button></form><p style="text-align:center;margin-top:15px">New patient? <a href="register.php">Register here</a></p></div>
<footer class="footer"><p>&copy; 2026 Hospital Appointment System.</p></footer></body></html>
