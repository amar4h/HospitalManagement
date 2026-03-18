<?php
/**
 * Discharge Patient
 */
requireAuth();
requireRole(['admin', 'doctor', 'receptionist']);

$storage = getStorage();
$id = (int)($_GET['id'] ?? 0);
$admission = $storage->getById('ipd_admissions', $id);

if (!$admission || $admission['status'] !== 'admitted') {
    setFlashMessage('error', 'Invalid admission or already discharged');
    redirect('index.php?page=ipd');
}

$patient = $storage->getById('patients', $admission['patient_id']);
$doctor = $storage->getById('doctors', $admission['doctor_id']);
$bed = $storage->getById('beds', $admission['bed_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'status' => 'discharged',
        'discharge_date' => sanitize($_POST['discharge_date'] ?? date('Y-m-d')),
        'discharge_time' => sanitize($_POST['discharge_time'] ?? date('H:i')),
        'discharge_type' => sanitize($_POST['discharge_type'] ?? 'Normal'),
        'discharge_summary' => sanitize($_POST['discharge_summary'] ?? ''),
        'condition_on_discharge' => sanitize($_POST['condition_on_discharge'] ?? ''),
        'follow_up_date' => sanitize($_POST['follow_up_date'] ?? ''),
        'follow_up_instructions' => sanitize($_POST['follow_up_instructions'] ?? '')
    ];

    $storage->update('ipd_admissions', $id, $data);

    // Free the bed
    $storage->update('beds', $admission['bed_id'], [
        'status' => 'available',
        'patient_id' => null,
        'admission_id' => null
    ]);

    logActivity('ipd_discharge', 'Discharged patient: ' . $patient['name']);
    setFlashMessage('success', 'Patient discharged successfully');
    redirect('index.php?page=ipd-admission&id=' . $id);
}

$days = ceil((strtotime('now') - strtotime($admission['admission_date'])) / 86400);
?>

<div class="page-header">
    <h1><i class="bi bi-box-arrow-right me-2"></i>Discharge Patient</h1>
    <div>
        <a href="index.php?page=ipd-admission&id=<?= $id ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<!-- Patient Summary -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-1"><?= htmlspecialchars($patient['name']) ?></h5>
                <p class="mb-0 text-muted"><?= $patient['patient_id'] ?> | <?= calculateAge($patient['dob']) ?> yrs | <?= $patient['gender'] ?></p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-1">
                    <strong>Bed:</strong> <?= $bed['bed_number'] ?> (<?= $bed['ward'] ?>)
                </p>
                <p class="mb-0">
                    <strong>Duration:</strong> <?= $days ?> days (Admitted: <?= formatDate($admission['admission_date']) ?>)
                </p>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="" class="needs-validation" novalidate>
    <?= csrfField() ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-file-medical me-2"></i>Discharge Summary
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Discharge Date <span class="text-danger">*</span></label>
                            <input type="date" name="discharge_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Discharge Time</label>
                            <input type="time" name="discharge_time" class="form-control" value="<?= date('H:i') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Discharge Type</label>
                            <select name="discharge_type" class="form-select">
                                <option value="Normal">Normal Discharge</option>
                                <option value="LAMA">Left Against Medical Advice (LAMA)</option>
                                <option value="Transferred">Transferred to Another Hospital</option>
                                <option value="Absconded">Absconded</option>
                                <option value="Expired">Expired</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Condition at Discharge</label>
                            <select name="condition_on_discharge" class="form-select">
                                <option value="Improved">Improved</option>
                                <option value="Cured">Cured</option>
                                <option value="Unchanged">Unchanged</option>
                                <option value="Deteriorated">Deteriorated</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Discharge Summary</label>
                        <textarea name="discharge_summary" class="form-control" rows="5" placeholder="Include: Principal diagnosis, treatment given, procedures performed, condition at discharge..."></textarea>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="bi bi-calendar-event me-2"></i>Follow-up Instructions
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Follow-up Date</label>
                        <input type="date" name="follow_up_date" class="form-control" min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Instructions</label>
                        <textarea name="follow_up_instructions" class="form-control" rows="3" placeholder="Diet, medications, activities, warning signs to watch for..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Admission Summary -->
            <div class="card">
                <div class="card-header">Admission Summary</div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Attending Doctor:</strong><br>
                        <?= htmlspecialchars($doctor['name']) ?>
                    </p>
                    <?php if (!empty($admission['diagnosis'])): ?>
                    <p class="mb-2">
                        <strong>Diagnosis:</strong><br>
                        <?= htmlspecialchars($admission['diagnosis']) ?>
                    </p>
                    <?php endif; ?>
                    <p class="mb-0">
                        <strong>Condition on Admission:</strong><br>
                        <?= $admission['condition_on_admission'] ?? 'Not specified' ?>
                    </p>
                </div>
            </div>

            <!-- Submit -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-success w-100 mb-2">
                        <i class="bi bi-check-lg me-2"></i>Discharge Patient
                    </button>
                    <a href="index.php?page=ipd-admission&id=<?= $id ?>" class="btn btn-outline-secondary w-100">Cancel</a>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                After discharge, the bed will be marked as available.
            </div>
        </div>
    </div>
</form>
