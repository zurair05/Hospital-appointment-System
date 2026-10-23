<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/admin.php';

if (!empty($_SESSION['admin_id'])) {
    redirect('dashboard.php');
}

$error = '';
$notice = sessionNoticeMessage(isset($_GET['session']) ? (string) $_GET['session'] : null);

if (isset($_GET['logged_out'])) {
    $notice = 'Administrator session closed securely.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $statement = getDatabaseConnection()->prepare('SELECT id, email, password_hash FROM admins WHERE email = :email');
    $statement->execute(['email' => $email]);
    $admin = $statement->fetch();
    $storedHash = $admin !== false && is_string($admin['password_hash'] ?? null) ? (string) $admin['password_hash'] : null;

    if ($admin !== false && verifyPassword($password, $storedHash)) {
        refreshPasswordHash((int) $admin['id'], $password, (string) $storedHash, ADMINS_TABLE);
        beginAuthenticatedSession('admin_id', (int) $admin['id'], ['admin_email' => (string) $admin['email']]);
        redirect('dashboard.php');
    }

    $error = 'Invalid administrator email or password.';
}
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Login</title><link rel="stylesheet" href="../css/styles.css"></head>
<body><header class="header"><nav class="navbar"><div class="logo">🏥 HMS Admin</div><div class="nav-links"><a href="../index.html">Patient Website</a></div></nav></header>
<div class="form-container"><h2>Administrator Login</h2><?php if ($notice): ?><div class="alert alert-info"><?= e($notice) ?></div><?php endif; ?><?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?><form method="post"><div class="form-group"><label>Email</label><input type="email" name="email" required autocomplete="email" autofocus></div><div class="form-group"><label>Password</label><input type="password" name="password" required autocomplete="current-password"></div><button class="btn btn-primary" style="width:100%">Login to Admin Panel</button></form><p style="text-align:center;margin-top:15px"><a href="../index.html">Return to patient website</a></p></div>
<footer class="footer"><p>&copy; 2026 Hospital Appointment System.</p></footer></body></html>
