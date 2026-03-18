<?php
/**
 * New IPD Admission
 */
requireAuth();
requireRole(['admin', 'receptionist', 'doctor']);

$storage = getStorage();
$patients = $storage->getAll('patients');
$doctors = $storage->getAll('doctors', ['status' => 'active']);
$beds = array_filter($storage->getAll('beds'), function($bed) {
    return $bed['status'] === 'available';
});

$selectedPatient = $_GET['patient_id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'patient_id' => (int)($_POST['patient_id'] ?? 0),
        'doctor_id' => (int)($_POST['doctor_id'] ?? 0),
        'bed_id' => (int)($_POST['bed_id'] ?? 0),
        'admission_date' => sanitize($_POST['admission_date'] ?? date('Y-m-d')),
        'admission_time' => sanitize($_POST['admission_time'] ?? date('H:i')),
        'reason' => sanitize($_POST['reason'] ?? ''),
        'diagnosis' => sanitize($_POST['diagnosis'] ?? ''),
        'condition_on_admission' => sanitize($_POST['condition_on_admission'] ?? ''),
        'notes' => sanitize($_POST['notes'] ?? ''),
        'status' => 'admitted'
    ];

    if (empty($data['patient_id']) || empty($data['doctor_id']) || empty($data['bed_id'])) {
        setFlashMessage('error', 'Patient, Doctor, and Bed are required');
    } else {
        $id = $storage->insert('ipd_admissions', $data);

        // Update bed status
        $storage->update('beds', $data['bed_id'], [
            'status' => 'occupied',
            'patient_id' => $data['patient_id'],
            'admission_id' => $id
        ]);

        $patient = $storage->getById('patients', $data['patient_id']);
        logActivity('ipd_admission', 'Admitted patient: ' . $patient['name']);
        setFlashMessage('success', 'Patient admitted successfully');
        redirect('index.php?page=ipd-admission&id=' . $id);
    }
}
?>

<div class="page-header">
    <h1><i class="bi bi-person-plus me-2"></i>New Admission</h1>
    <div>
        <a href="index.php?page=ipd" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<form method="POST" action="" class="needs-validation" novalidate>
    <?= csrfField() ?>

    <div class="row">
        <div class="col-lg-8">
            <!-- Patient & Doctor -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-person me-2"></i>Patient & Doctor
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Patient <span class="text-danger">*</span></label>
                            <select name="patient_id" class="form-select" required>
                                <option value="">Select Patient</option>
                                <?php foreach ($patients as $patient): ?>
                                <option value="<?= $patient['id'] ?>" <?= $selectedPatient == $patient['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($patient['name']) ?> (<?= $patient['patient_id'] ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Attending Doctor <span class="text-danger">*</span></label>
                            <select name="doctor_id" class="form-select" required>
                                <option value="">Select Doctor</option>
                                <?php foreach ($doctors as $doctor): ?>
                                <option value="<?= $doctor['id'] ?>">
                                    <?= htmlspecialchars($doctor['name']) ?> - <?= $doctor['specialization'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admission Details -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-hospital me-2"></i>Admission Details
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Admission Date <span class="text-danger">*</span></label>
                            <input type="date" name="admission_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Admission Time</label>
                            <input type="time" name="admission_time" class="form-control" value="<?= date('H:i') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason for Admission</label>
                        <textarea name="reason" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Diagnosis</label>
                        <textarea name="diagnosis" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Condition on Admission</label>
                        <select name="condition_on_admission" class="form-select">
                            <option value="Stable">Stable</option>
                            <option value="Serious">Serious</option>
                            <option value="Critical">Critical</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Bed Selection -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-grid me-2"></i>Bed Selection <span class="text-danger">*</span>
                </div>
                <div class="card-body">
                    <?php if (empty($beds)): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>No beds available
                    </div>
                    <?php else: ?>
                    <select name="bed_id" class="form-select" required>
                        <option value="">Select Bed</option>
                        <?php
                        $groupedBeds = [];
                        foreach ($beds as $bed) {
                            $groupedBeds[$bed['ward']][] = $bed;
                        }
                        foreach ($groupedBeds as $ward => $wardBeds):
                        ?>
                        <optgroup label="<?= htmlspecialchars($ward) ?>">
                            <?php foreach ($wardBeds as $bed): ?>
                            <option value="<?= $bed['id'] ?>"><?= $bed['bed_number'] ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-info-circle me-1"></i><?= count($beds) ?> beds available
                    </small>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Submit -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2" <?= empty($beds) ? 'disabled' : '' ?>>
                        <i class="bi bi-check-lg me-2"></i>Admit Patient
                    </button>
                    <a href="index.php?page=ipd" class="btn btn-outline-secondary w-100">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>
