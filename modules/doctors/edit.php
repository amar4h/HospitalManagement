<?php
/**
 * Edit Doctor
 */
requireAuth();
requireRole(['admin']);

$storage = getStorage();
$id = (int)($_GET['id'] ?? 0);
$doctor = $storage->getById('doctors', $id);

if (!$doctor) {
    setFlashMessage('error', 'Doctor not found');
    redirect('index.php?page=doctors');
}

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
        $storage->update('doctors', $id, $data);
        logActivity('doctor_update', 'Updated doctor: ' . $data['name']);
        setFlashMessage('success', 'Doctor updated successfully');
        redirect('index.php?page=doctor-view&id=' . $id);
    }
}
?>

<div class="page-header">
    <h1><i class="bi bi-pencil me-2"></i>Edit Doctor</h1>
    <div>
        <a href="index.php?page=doctor-view&id=<?= $id ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Doctor
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
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($doctor['name']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Specialization <span class="text-danger">*</span></label>
                            <input type="text" name="specialization" class="form-control" value="<?= htmlspecialchars($doctor['specialization']) ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department <span class="text-danger">*</span></label>
                            <select name="department_id" class="form-select" required>
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>" <?= $doctor['department_id'] == $dept['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Qualification</label>
                            <input type="text" name="qualification" class="form-control" value="<?= htmlspecialchars($doctor['qualification'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($doctor['phone']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($doctor['email'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($doctor['address'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bio / About</label>
                        <textarea name="bio" class="form-control" rows="3"><?= htmlspecialchars($doctor['bio'] ?? '') ?></textarea>
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
                        <input type="number" name="experience" class="form-control" min="0" value="<?= $doctor['experience'] ?? 0 ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Consultation Fee</label>
                        <div class="input-group">
                            <span class="input-group-text"><?= CURRENCY_SYMBOL ?></span>
                            <input type="number" name="consultation_fee" class="form-control" min="0" step="0.01" value="<?= $doctor['consultation_fee'] ?? 0 ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= $doctor['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $doctor['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-check-lg me-2"></i>Update Doctor
                    </button>
                    <a href="index.php?page=doctor-view&id=<?= $id ?>" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x me-2"></i>Cancel
                    </a>
                </div>
            </div>

            <!-- Info -->
            <div class="card">
                <div class="card-body small text-muted">
                    <p class="mb-1"><strong>Added:</strong> <?= formatDateTime($doctor['created_at']) ?></p>
                    <?php if (!empty($doctor['updated_at'])): ?>
                    <p class="mb-0"><strong>Last Updated:</strong> <?= formatDateTime($doctor['updated_at']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</form>
