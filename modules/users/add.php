<?php
/**
 * Add User
 */
requireAuth();
requireRole(['admin']);

$storage = getStorage();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username' => sanitize($_POST['username'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'name' => sanitize($_POST['name'] ?? ''),
        'email' => sanitize($_POST['email'] ?? ''),
        'phone' => sanitize($_POST['phone'] ?? ''),
        'role' => sanitize($_POST['role'] ?? 'receptionist'),
        'status' => 'active'
    ];

    if (empty($data['username']) || empty($data['password']) || empty($data['name']) || empty($data['email'])) {
        setFlashMessage('error', 'Please fill in all required fields');
    } elseif (strlen($data['password']) < 6) {
        setFlashMessage('error', 'Password must be at least 6 characters');
    } else {
        $result = createUser($data);
        if ($result['success']) {
            setFlashMessage('success', $result['message']);
            redirect('index.php?page=users');
        } else {
            setFlashMessage('error', $result['message']);
        }
    }
}

$roles = [
    'admin' => 'Administrator',
    'doctor' => 'Doctor',
    'nurse' => 'Nurse',
    'receptionist' => 'Receptionist',
    'pharmacist' => 'Pharmacist',
    'lab_technician' => 'Lab Technician',
    'accountant' => 'Accountant'
];
?>

<div class="page-header">
    <h1><i class="bi bi-person-plus me-2"></i>Add User</h1>
    <div>
        <a href="index.php?page=users" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="" class="needs-validation" novalidate>
                    <?= csrfField() ?>

                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" required pattern="[a-zA-Z0-9_]+">
                        <small class="text-muted">Letters, numbers, and underscores only</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password" class="form-control" minlength="6" required>
                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="[name='password']">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <small class="text-muted">Minimum 6 characters</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <?php foreach ($roles as $value => $label): ?>
                            <option value="<?= $value ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i>Create User
                        </button>
                        <a href="index.php?page=users" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
