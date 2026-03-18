<?php
/**
 * Edit User
 */
requireAuth();
requireRole(['admin']);

$storage = getStorage();
$id = (int)($_GET['id'] ?? 0);
$user = $storage->getById('users', $id);

if (!$user) {
    setFlashMessage('error', 'User not found');
    redirect('index.php?page=users');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => sanitize($_POST['name'] ?? ''),
        'email' => sanitize($_POST['email'] ?? ''),
        'phone' => sanitize($_POST['phone'] ?? ''),
        'role' => sanitize($_POST['role'] ?? 'receptionist'),
        'status' => sanitize($_POST['status'] ?? 'active')
    ];

    // Only update password if provided
    if (!empty($_POST['password'])) {
        if (strlen($_POST['password']) < 6) {
            setFlashMessage('error', 'Password must be at least 6 characters');
        } else {
            $data['password'] = $_POST['password'];
        }
    }

    if (empty($data['name']) || empty($data['email'])) {
        setFlashMessage('error', 'Name and email are required');
    } else {
        $result = updateUser($id, $data);
        setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
        if ($result['success']) {
            redirect('index.php?page=users');
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
    <h1><i class="bi bi-pencil me-2"></i>Edit User</h1>
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
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                        <small class="text-muted">Username cannot be changed</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <div class="input-group">
                            <input type="password" name="password" class="form-control" minlength="6">
                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="[name='password']">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <small class="text-muted">Leave blank to keep current password</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select">
                                <?php foreach ($roles as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $user['role'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i>Update User
                        </button>
                        <a href="index.php?page=users" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body small text-muted">
                <p class="mb-1"><strong>Created:</strong> <?= formatDateTime($user['created_at']) ?></p>
                <?php if (!empty($user['last_login'])): ?>
                <p class="mb-0"><strong>Last Login:</strong> <?= formatDateTime($user['last_login']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
