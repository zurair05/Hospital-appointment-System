<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/admin.php';
requireAdmin();
$db = getDatabaseConnection();
$message = '';
$errors = [];
$editing = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($action === 'delete' && $id) {
        $statement = $db->prepare('DELETE FROM patients WHERE id = :id');
        $statement->execute(['id' => $id]);
        $message = 'Patient deleted successfully.';
    } elseif ($action === 'save') {
        $data = ['name' => trim((string) ($_POST['name'] ?? '')), 'email' => trim((string) ($_POST['email'] ?? '')), 'phone' => trim((string) ($_POST['phone'] ?? '')), 'gender' => (string) ($_POST['gender'] ?? '')];
        if (!$data['name'] || !filter_var($data['email'], FILTER_VALIDATE_EMAIL) || !$data['phone'] || !in_array($data['gender'], ['Male', 'Female', 'Other'], true)) $errors[] = 'Enter a valid name, email, phone and gender.';
        if (!$errors) {
            try {
                if ($id) {
                    $data['id'] = $id;
                    $statement = $db->prepare('UPDATE patients SET name=:name,email=:email,phone=:phone,gender=:gender WHERE id=:id');
                    $statement->execute($data);
                    $message = 'Patient updated successfully.';
                } else {
                    $data['password_hash'] = hashPassword(bin2hex(random_bytes(16)));
                    $statement = $db->prepare('INSERT INTO patients (name,email,phone,gender,password_hash) VALUES (:name,:email,:phone,:gender,:password_hash)');
                    $statement->execute($data);
                    $message = 'Patient added successfully.';
                }
            } catch (PDOException $exception) {
                $errors[] = $exception->getCode() === '23000' ? 'That email is already registered.' : 'Patient operation failed.';
            }
        }
    }
}
if (isset($_GET['edit'])) {
    $statement = $db->prepare('SELECT id,name,email,phone,gender FROM patients WHERE id=:id');
    $statement->execute(['id' => (int) $_GET['edit']]);
    $editing = $statement->fetch() ?: null;
}
$patients = $db->query('SELECT id,name,email,phone,gender FROM patients ORDER BY id DESC')->fetchAll();
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Patients</title><link rel="stylesheet" href="../css/styles.css"></head>
<body><header class="header"><nav class="navbar"><div class="logo">🏥 HMS Admin</div><div class="nav-links"><a href="dashboard.php">Dashboard</a><a href="logout.php">Logout</a></div></nav></header><div class="layout"><aside class="sidebar"><h3>Admin Panel</h3><ul><li><a href="dashboard.php">📊 Dashboard</a></li><li><a class="active" href="patients.php">🧑 Patients</a></li><li><a href="doctors.php">👨‍⚕️ Doctors</a></li><li><a href="appointments.php">📅 Appointments</a></li></ul></aside><main class="main-content"><div class="table-container"><h2>Manage Patients</h2><?= sessionStatusHtml() ?><?php if ($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?><?php foreach ($errors as $error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endforeach; ?><div class="form-container" style="max-width:none;margin:0 0 20px"><h2><?= $editing ? 'Edit Patient' : 'Add Patient' ?></h2><form method="post"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e($editing['id'] ?? '') ?>"><div class="form-row"><div class="form-group"><label>Name</label><input name="name" required value="<?= e($editing['name'] ?? '') ?>"></div><div class="form-group"><label>Email</label><input type="email" name="email" required value="<?= e($editing['email'] ?? '') ?>"></div></div><div class="form-row"><div class="form-group"><label>Phone</label><input name="phone" required value="<?= e($editing['phone'] ?? '') ?>"></div><div class="form-group"><label>Gender</label><select name="gender" required><option value="">Select</option><?php foreach (['Male','Female','Other'] as $gender): ?><option <?= ($editing['gender'] ?? '') === $gender ? 'selected' : '' ?>><?= $gender ?></option><?php endforeach; ?></select></div></div><button class="btn btn-primary"><?= $editing ? 'Update' : 'Add' ?></button><?php if ($editing): ?> <a class="btn btn-secondary" href="patients.php">Cancel</a><?php endif; ?></form></div><table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Gender</th><th>Actions</th></tr></thead><tbody><?php foreach ($patients as $patient): ?><tr><td><?= e($patient['id']) ?></td><td><?= e($patient['name']) ?></td><td><?= e($patient['email']) ?></td><td><?= e($patient['phone']) ?></td><td><?= e($patient['gender']) ?></td><td><a class="btn btn-secondary btn-sm" href="?edit=<?= e($patient['id']) ?>">Edit</a><form method="post" style="display:inline" onsubmit="return confirm('Delete this patient?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($patient['id']) ?>"><button class="btn btn-danger btn-sm">Delete</button></form></td></tr><?php endforeach; ?></tbody></table></div></main></div></body></html>
