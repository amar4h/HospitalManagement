<?php
/**
 * New Lab Order
 */
requireAuth();
requireRole(['admin', 'doctor', 'lab_technician']);

$storage = getStorage();
$patients = $storage->getAll('patients');
$doctors = $storage->getAll('doctors', ['status' => 'active']);
$labTests = $storage->getAll('lab_tests', ['status' => 'active']);

$selectedPatient = $_GET['patient_id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patientId = (int)($_POST['patient_id'] ?? 0);
    $doctorId = (int)($_POST['doctor_id'] ?? 0);
    $testIds = $_POST['tests'] ?? [];

    if (empty($patientId) || empty($testIds)) {
        setFlashMessage('error', 'Patient and at least one test are required');
    } else {
        foreach ($testIds as $testId) {
            if (!empty($testId)) {
                $storage->insert('lab_orders', [
                    'patient_id' => $patientId,
                    'doctor_id' => $doctorId,
                    'test_id' => (int)$testId,
                    'status' => 'pending',
                    'priority' => sanitize($_POST['priority'] ?? 'normal'),
                    'notes' => sanitize($_POST['notes'] ?? '')
                ]);
            }
        }

        $patient = $storage->getById('patients', $patientId);
        logActivity('lab_order', 'Created lab order for ' . $patient['name']);
        setFlashMessage('success', 'Lab order created successfully');
        redirect('index.php?page=laboratory');
    }
}
?>

<div class="page-header">
    <h1><i class="bi bi-plus-lg me-2"></i>New Lab Order</h1>
    <div>
        <a href="index.php?page=laboratory" class="btn btn-outline-secondary">
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
                            <label class="form-label">Referring Doctor</label>
                            <select name="doctor_id" class="form-select">
                                <option value="">Select Doctor</option>
                                <?php foreach ($doctors as $doctor): ?>
                                <option value="<?= $doctor['id'] ?>"><?= htmlspecialchars($doctor['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="bi bi-droplet me-2"></i>Select Tests <span class="text-danger">*</span>
                </div>
                <div class="card-body">
                    <?php
                    $testsByCategory = [];
                    foreach ($labTests as $test) {
                        $testsByCategory[$test['category']][] = $test;
                    }
                    ?>
                    <?php foreach ($testsByCategory as $category => $tests): ?>
                    <h6 class="mb-2"><?= htmlspecialchars($category) ?></h6>
                    <div class="row mb-3">
                        <?php foreach ($tests as $test): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="form-check">
                                <input type="checkbox" name="tests[]" value="<?= $test['id'] ?>" class="form-check-input" id="test<?= $test['id'] ?>">
                                <label class="form-check-label" for="test<?= $test['id'] ?>">
                                    <?= htmlspecialchars($test['name']) ?>
                                    <small class="text-muted">(<?= formatCurrency($test['price']) ?>)</small>
                                </label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="normal">Normal</option>
                                <option value="urgent">Urgent</option>
                                <option value="stat">STAT (Emergency)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Notes</label>
                            <input type="text" name="notes" class="form-control" placeholder="Any special instructions...">
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php?page=laboratory" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i>Create Order
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
