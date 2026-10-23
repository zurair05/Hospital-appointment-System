<?php
declare(strict_types=1);
require_once __DIR__ . '/config/app.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $gender = (string) ($_POST['gender'] ?? '');
    $dob = trim((string) ($_POST['date_of_birth'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === '') $errors[] = 'Name, valid email, and phone are required.';
    if (!in_array($gender, ['Male', 'Female', 'Other'], true)) $errors[] = 'Please select a gender.';
    if (strlen($password) < 6 || $password !== $confirm) $errors[] = 'Passwords must match and contain at least 6 characters.';

    if (!$errors) {
        try {
            $statement = getDatabaseConnection()->prepare(
                'INSERT INTO patients (name, email, phone, gender, date_of_birth, address, password_hash)
                 VALUES (:name, :email, :phone, :gender, :dob, :address, :password_hash)'
            );
            $statement->execute([
                'name' => $name, 'email' => $email, 'phone' => $phone, 'gender' => $gender,
                'dob' => $dob !== '' ? $dob : null, 'address' => trim((string) ($_POST['address'] ?? '')),
                'password_hash' => hashPassword($password),
            ]);
            redirect('login.php?registered=1');
        } catch (PDOException $exception) {
            $errors[] = $exception->getCode() === '23000' ? 'That email is already registered.' : 'Registration failed. Check your database setup.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Register</title><link rel="stylesheet" href="css/styles.css"></head>
<body>
<header class="header"><nav class="navbar"><div class="logo">🏥 HMS</div><div class="nav-links"><a href="index.html">Home</a><a href="login.php">Login</a></div></nav></header>
<div class="form-container"><h2>Patient Registration</h2>
<?php foreach ($errors as $error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endforeach; ?>
<form method="post">
<div class="form-group"><label>Full Name</label><input name="name" required value="<?= e($_POST['name'] ?? '') ?>"></div>
<div class="form-group"><label>Email</label><input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></div>
<div class="form-group"><label>Phone</label><input name="phone" required value="<?= e($_POST['phone'] ?? '') ?>"></div>
<div class="form-group"><label>Gender</label><select name="gender" required><option value="">Select gender</option><option>Male</option><option>Female</option><option>Other</option></select></div>
<div class="form-group"><label>Date of Birth</label><input type="date" name="date_of_birth"></div>
<div class="form-group"><label>Address</label><textarea name="address" rows="3"></textarea></div>
<div class="form-group"><label>Password</label><input type="password" name="password" required autocomplete="new-password"></div>
<div class="form-group"><label>Confirm Password</label><input type="password" name="confirm_password" required autocomplete="new-password"></div>
<button class="btn btn-primary" style="width:100%">Register</button></form>
<p style="text-align:center;margin-top:15px">Already registered? <a href="login.php">Login here</a></p></div>
<footer class="footer"><p>&copy; 2026 Hospital Appointment System.</p></footer></body></html>
