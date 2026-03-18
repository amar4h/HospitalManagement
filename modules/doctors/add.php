<?php
/**
 * Add New Doctor
 */
requireAuth();
requireRole(['admin']);

$storage = getStorage();
$departments = $storage->getAll('departments');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => sanitize($_POST['name'] ?? ''),
        'specialization' => sanitize($_POST['specialization'] ?? ''),
        'department_id' => (int)($_POST['department_id'] ?? 0),
        'qualification' => sanitize($_POST['qualification'] ?? ''),
        'experience' => (int)($_POST['experience'] ?? 0),
        'phone' => sanitize($_POST['phone'] ?? ''),
        'email' => sanitize($_POST['email'] ?? ''),
        'address' => sanitize($_POST['address'] ?? ''),
        'consultation_fee' => (float)($_POST['consultation_fee'] ?? 0),
        'bio' => sanitize($_POST['bio'] ?? ''),
        'status' => sanitize($_POST['status'] ?? 'active')
    ];

    if (empty($data['name']) || empty($data['specialization']) || empty($data['phone'])) {
        setFlashMessage('error', 'Please fill in all required fields');
    } else {
        $doctorId = $storage->insert('doctors', $data);

        // Create user account if requested
        if (!empty($_POST['create_account'])) {
            $username = strtolower(str_replace(' ', '.', $data['name']));
            $userData = [
                'username' => $username,
                'password' => password_hash('doctor123', PASSWORD_DEFAULT),
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'role' => 'doctor',
                'doctor_id' => $doctorId,
                'status' => 'active'
            ];
            $userId = $storage->insert('users', $userData);
            $storage->update('doctors', $doctorId, ['user_id' => $userId]);
        }

        logActivity('doctor_add', 'Added new doctor: ' . $data['name']);
        setFlashMessage('success', 'Doctor added successfully');
        redirect('index.php?page=doctor-view&id=' . $doctorId);
    }
}
?>

<div class="page-header">
    <h1><i class="bi bi-person-plus me-2"></i>Add New Doctor</h1>
    <div>
        <a href="index.php?page=doctors" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Doctors
        </a>
    </div>
</div>

<form method="POST" action="" class="needs-validation" novalidate>
    <?= csrfField() ?>

    <div class="row">
        <div class="col-md-8">
            <!-- Personal Information -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-person me-2"></i>Personal Information
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Dr. John Smith" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Specialization <span class="text-danger">*</span></label>
                            <input type="text" name="specialization" class="form-control" placeholder="Cardiologist" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department <span class="text-danger">*</span></label>
                            <select name="department_id" class="form-select" required>
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Qualification</label>
                            <input type="text" name="qualification" class="form-control" placeholder="MBBS, MD">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bio / About</label>
                        <textarea name="bio" class="form-control" rows="3" placeholder="Brief description about the doctor..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Professional Details -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-briefcase me-2"></i>Professional Details
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Experience (Years)</label>
                        <input type="number" name="experience" class="form-control" min="0" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Consultation Fee</label>
                        <div class="input-group">
                            <span class="input-group-text"><?= CURRENCY_SYMBOL ?></span>
                            <input type="number" name="consultation_fee" class="form-control" min="0" step="0.01" value="100">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- User Account -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-person-gear me-2"></i>System Access
                </div>
                <div class="card-body">
                    <div class="form-check">
                        <input type="checkbox" name="create_account" value="1" class="form-check-input" id="createAccount">
                        <label class="form-check-label" for="createAccount">
                            Create login account for this doctor
                        </label>
                    </div>
                    <small class="text-muted d-block mt-2">
                        If checked, a user account will be created with default password: <code>doctor123</code>
                    </small>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-check-lg me-2"></i>Add Doctor
                    </button>
                    <a href="index.php?page=doctors" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x me-2"></i>Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>
