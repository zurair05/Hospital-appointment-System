<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/admin.php';
requireAdmin();
$db = getDatabaseConnection();
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($action === 'delete' && $id) {
        try {
            $statement = $db->prepare('DELETE FROM doctors WHERE id=:id');
            $statement->execute(['id' => $id]);
            $message = 'Doctor deleted successfully.';
        } catch (PDOException $exception) {
            $message = 'Doctor cannot be deleted while appointments refer to this doctor.';
        }
    } elseif ($action === 'save') {
        $data = ['name' => trim((string) ($_POST['name'] ?? '')), 'specialty' => trim((string) ($_POST['specialty'] ?? '')), 'phone' => trim((string) ($_POST['phone'] ?? '')), 'email' => trim((string) ($_POST['email'] ?? ''))];
        if ($id) {
            $data['id'] = $id;
            $statement = $db->prepare('UPDATE doctors SET name=:name,specialty=:specialty,phone=:phone,email=:email WHERE id=:id');
            $statement->execute($data);
        } else {
            $statement = $db->prepare('INSERT INTO doctors (name,specialty,phone,email) VALUES (:name,:specialty,:phone,:email)');
            $statement->execute($data);
        }
        $message = 'Doctor saved successfully.';
    }
}
$doctors = $db->query('SELECT id,name,specialty,phone,email FROM doctors ORDER BY name')->fetchAll();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Doctors</title><link rel="stylesheet" href="../css/styles.css"></head><body><header class="header"><nav class="navbar"><div class="logo">🏥 HMS Admin</div><div class="nav-links"><a href="dashboard.php">Dashboard</a><a href="logout.php">Logout</a></div></nav></header><div class="layout"><aside class="sidebar"><h3>Admin Panel</h3><ul><li><a href="dashboard.php">📊 Dashboard</a></li><li><a href="patients.php">🧑 Patients</a></li><li><a class="active" href="doctors.php">👨‍⚕️ Doctors</a></li><li><a href="appointments.php">📅 Appointments</a></li></ul></aside><main class="main-content"><div class="table-container"><h2>Manage Doctors</h2><?= sessionStatusHtml() ?><?php if ($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?><div class="form-container" style="max-width:none;margin:0 0 20px"><h2>Add Doctor</h2><form method="post"><input type="hidden" name="action" value="save"><div class="form-row"><div class="form-group"><label>Name</label><input name="name" required></div><div class="form-group"><label>Specialty</label><input name="specialty" required></div></div><div class="form-row"><div class="form-group"><label>Phone</label><input name="phone" required></div><div class="form-group"><label>Email</label><input type="email" name="email" required></div></div><button class="btn btn-primary">Add Doctor</button></form></div><table><thead><tr><th>Name</th><th>Specialty</th><th>Phone</th><th>Email</th><th>Action</th></tr></thead><tbody><?php foreach ($doctors as $doctor): ?><tr><td><?= e($doctor['name']) ?></td><td><?= e($doctor['specialty']) ?></td><td><?= e($doctor['phone']) ?></td><td><?= e($doctor['email']) ?></td><td><form method="post" onsubmit="return confirm('Delete this doctor?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($doctor['id']) ?>"><button class="btn btn-danger btn-sm">Delete</button></form></td></tr><?php endforeach; ?></tbody></table></div></main></div></body></html>
