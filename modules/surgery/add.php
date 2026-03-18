<?php
/**
 * Schedule Surgery
 */
requireAuth();
requireRole(['admin', 'doctor']);

$storage = getStorage();
$patients = $storage->getAll('patients');
$doctors = $storage->getAll('doctors', ['status' => 'active']);

$selectedPatient = $_GET['patient_id'] ?? '';
$ipdId = $_GET['ipd_id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'patient_id' => (int)($_POST['patient_id'] ?? 0),
        'doctor_id' => (int)($_POST['doctor_id'] ?? 0),
        'ipd_id' => $ipdId ?: null,
        'surgery_name' => sanitize($_POST['surgery_name'] ?? ''),
        'date' => sanitize($_POST['date'] ?? ''),
        'time' => sanitize($_POST['time'] ?? ''),
        'operation_theatre' => sanitize($_POST['operation_theatre'] ?? ''),
        'anesthesia_type' => sanitize($_POST['anesthesia_type'] ?? ''),
        'pre_op_notes' => sanitize($_POST['pre_op_notes'] ?? ''),
        'status' => 'scheduled'
    ];

    if (empty($data['patient_id']) || empty($data['doctor_id']) || empty($data['surgery_name']) || empty($data['date'])) {
        setFlashMessage('error', 'Please fill in all required fields');
    } else {
        $id = $storage->insert('surgeries', $data);
        $patient = $storage->getById('patients', $data['patient_id']);
        logActivity('surgery_schedule', 'Scheduled surgery for ' . $patient['name']);
        setFlashMessage('success', 'Surgery scheduled successfully');
        redirect('index.php?page=surgery');
    }
}
?>

<div class="page-header">
    <h1><i class="bi bi-plus-lg me-2"></i>Schedule Surgery</h1>
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
                <div class="card-header">
                    <i class="bi bi-person me-2"></i>Patient & Surgeon
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
                            <label class="form-label">Surgeon <span class="text-danger">*</span></label>
                            <select name="doctor_id" class="form-select" required>
                                <option value="">Select Surgeon</option>
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

            <div class="card">
                <div class="card-header">
                    <i class="bi bi-heart-pulse me-2"></i>Surgery Details
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Surgery / Procedure Name <span class="text-danger">*</span></label>
                        <input type="text" name="surgery_name" class="form-control" placeholder="e.g., Appendectomy, Knee Replacement" required>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Time</label>
                            <input type="time" name="time" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Operation Theatre</label>
                            <select name="operation_theatre" class="form-select">
                                <option value="">Select OT</option>
                                <option value="OT-1">OT-1 (Main)</option>
                                <option value="OT-2">OT-2</option>
                                <option value="OT-3">OT-3 (Minor)</option>
                                <option value="OT-4">OT-4 (Emergency)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Anesthesia Type</label>
                        <select name="anesthesia_type" class="form-select">
                            <option value="">Select Type</option>
                            <option value="General">General Anesthesia</option>
                            <option value="Local">Local Anesthesia</option>
                            <option value="Spinal">Spinal Anesthesia</option>
                            <option value="Epidural">Epidural Anesthesia</option>
                            <option value="Regional">Regional Block</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pre-operative Notes</label>
                        <textarea name="pre_op_notes" class="form-control" rows="3" placeholder="Pre-operative assessment, special instructions..."></textarea>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php?page=surgery" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i>Schedule Surgery
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
