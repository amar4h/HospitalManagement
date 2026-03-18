<?php
/**
 * Edit Surgery
 */
requireAuth();
requireRole(['admin', 'doctor']);

$storage = getStorage();
$id = (int)($_GET['id'] ?? 0);
$surgery = $storage->getById('surgeries', $id);

if (!$surgery) {
    setFlashMessage('error', 'Surgery not found');
    redirect('index.php?page=surgery');
}

$patients = $storage->getAll('patients');
$doctors = $storage->getAll('doctors', ['status' => 'active']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'patient_id' => (int)($_POST['patient_id'] ?? 0),
        'doctor_id' => (int)($_POST['doctor_id'] ?? 0),
        'surgery_name' => sanitize($_POST['surgery_name'] ?? ''),
        'date' => sanitize($_POST['date'] ?? ''),
        'time' => sanitize($_POST['time'] ?? ''),
        'operation_theatre' => sanitize($_POST['operation_theatre'] ?? ''),
        'anesthesia_type' => sanitize($_POST['anesthesia_type'] ?? ''),
        'pre_op_notes' => sanitize($_POST['pre_op_notes'] ?? ''),
        'post_op_notes' => sanitize($_POST['post_op_notes'] ?? ''),
        'status' => sanitize($_POST['status'] ?? 'scheduled')
    ];

    $storage->update('surgeries', $id, $data);
    logActivity('surgery_update', 'Updated surgery ID: ' . $id);
    setFlashMessage('success', 'Surgery updated successfully');
    redirect('index.php?page=surgery');
}
?>

<div class="page-header">
    <h1><i class="bi bi-pencil me-2"></i>Edit Surgery</h1>
    <div>
        <a href="index.php?page=surgery" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <form method="POST" action="" class="needs-validation" novalidate>
            <?= csrfField() ?>

            <div class="card">
                <div class="card-header">Patient & Surgeon</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Patient</label>
                            <select name="patient_id" class="form-select" required>
                                <?php foreach ($patients as $patient): ?>
                                <option value="<?= $patient['id'] ?>" <?= $surgery['patient_id'] == $patient['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($patient['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Surgeon</label>
                            <select name="doctor_id" class="form-select" required>
                                <?php foreach ($doctors as $doctor): ?>
                                <option value="<?= $doctor['id'] ?>" <?= $surgery['doctor_id'] == $doctor['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($doctor['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Surgery Details</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Surgery Name</label>
                        <input type="text" name="surgery_name" class="form-control" value="<?= htmlspecialchars($surgery['surgery_name']) ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="date" class="form-control" value="<?= $surgery['date'] ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Time</label>
                            <input type="time" name="time" class="form-control" value="<?= $surgery['time'] ?? '' ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="scheduled" <?= $surgery['status'] === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                <option value="in_progress" <?= $surgery['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="completed" <?= $surgery['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="cancelled" <?= $surgery['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Operation Theatre</label>
                            <select name="operation_theatre" class="form-select">
                                <option value="">Select OT</option>
                                <option value="OT-1" <?= ($surgery['operation_theatre'] ?? '') === 'OT-1' ? 'selected' : '' ?>>OT-1</option>
                                <option value="OT-2" <?= ($surgery['operation_theatre'] ?? '') === 'OT-2' ? 'selected' : '' ?>>OT-2</option>
                                <option value="OT-3" <?= ($surgery['operation_theatre'] ?? '') === 'OT-3' ? 'selected' : '' ?>>OT-3</option>
                                <option value="OT-4" <?= ($surgery['operation_theatre'] ?? '') === 'OT-4' ? 'selected' : '' ?>>OT-4</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Anesthesia</label>
                            <select name="anesthesia_type" class="form-select">
                                <option value="">Select</option>
                                <option value="General" <?= ($surgery['anesthesia_type'] ?? '') === 'General' ? 'selected' : '' ?>>General</option>
                                <option value="Local" <?= ($surgery['anesthesia_type'] ?? '') === 'Local' ? 'selected' : '' ?>>Local</option>
                                <option value="Spinal" <?= ($surgery['anesthesia_type'] ?? '') === 'Spinal' ? 'selected' : '' ?>>Spinal</option>
                                <option value="Epidural" <?= ($surgery['anesthesia_type'] ?? '') === 'Epidural' ? 'selected' : '' ?>>Epidural</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pre-operative Notes</label>
                        <textarea name="pre_op_notes" class="form-control" rows="2"><?= htmlspecialchars($surgery['pre_op_notes'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Post-operative Notes</label>
                        <textarea name="post_op_notes" class="form-control" rows="2"><?= htmlspecialchars($surgery['post_op_notes'] ?? '') ?></textarea>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php?page=surgery" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i>Update Surgery
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
