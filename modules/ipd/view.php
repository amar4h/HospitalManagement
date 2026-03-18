<?php
/**
 * IPD Admission Details
 */
requireAuth();

$storage = getStorage();
$id = (int)($_GET['id'] ?? 0);
$admission = $storage->getById('ipd_admissions', $id);

if (!$admission) {
    setFlashMessage('error', 'Admission not found');
    redirect('index.php?page=ipd');
}

$patient = $storage->getById('patients', $admission['patient_id']);
$doctor = $storage->getById('doctors', $admission['doctor_id']);
$bed = $storage->getById('beds', $admission['bed_id']);
$days = ceil((strtotime($admission['discharge_date'] ?? 'now') - strtotime($admission['admission_date'])) / 86400);
?>

<div class="page-header">
    <h1><i class="bi bi-hospital me-2"></i>Admission Details</h1>
    <div class="quick-actions">
        <?php if ($admission['status'] === 'admitted'): ?>
        <a href="index.php?page=ipd-discharge&id=<?= $id ?>" class="btn btn-success">
            <i class="bi bi-box-arrow-right me-2"></i>Discharge
        </a>
        <?php endif; ?>
        <a href="index.php?page=invoice-add&ipd_id=<?= $id ?>&patient_id=<?= $admission['patient_id'] ?>" class="btn btn-info">
            <i class="bi bi-receipt me-2"></i>Generate Bill
        </a>
        <a href="index.php?page=ipd" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <!-- Patient Card -->
        <div class="patient-info-card mb-4">
            <div class="d-flex align-items-center mb-3">
                <div class="avatar bg-white text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                    <?= strtoupper(substr($patient['name'], 0, 1)) ?>
                </div>
                <div>
                    <div class="patient-name"><?= htmlspecialchars($patient['name']) ?></div>
                    <div class="patient-id"><?= $patient['patient_id'] ?></div>
                </div>
            </div>
            <div class="row text-center">
                <div class="col-4">
                    <div class="fw-bold"><?= calculateAge($patient['dob']) ?></div>
                    <small>Age</small>
                </div>
                <div class="col-4">
                    <div class="fw-bold"><?= $patient['gender'] ?></div>
                    <small>Gender</small>
                </div>
                <div class="col-4">
                    <div class="fw-bold"><?= $patient['blood_group'] ?? '-' ?></div>
                    <small>Blood</small>
                </div>
            </div>
        </div>

        <!-- Admission Info -->
        <div class="card">
            <div class="card-header">Admission Info</div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>Admission #:</strong><br>
                    IPD-<?= str_pad($id, 6, '0', STR_PAD_LEFT) ?>
                </p>
                <p class="mb-2">
                    <strong>Status:</strong><br>
                    <?= getStatusBadge($admission['status']) ?>
                </p>
                <p class="mb-2">
                    <strong>Admitted:</strong><br>
                    <?= formatDateTime($admission['admission_date'] . ' ' . ($admission['admission_time'] ?? '')) ?>
                </p>
                <?php if ($admission['status'] === 'discharged'): ?>
                <p class="mb-2">
                    <strong>Discharged:</strong><br>
                    <?= formatDate($admission['discharge_date']) ?>
                </p>
                <?php endif; ?>
                <p class="mb-2">
                    <strong>Duration:</strong><br>
                    <span class="badge bg-info"><?= $days ?> days</span>
                </p>
            </div>
        </div>

        <!-- Bed Info -->
        <div class="card">
            <div class="card-header">Bed Information</div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>Bed Number:</strong><br>
                    <span class="badge bg-primary fs-6"><?= $bed['bed_number'] ?></span>
                </p>
                <p class="mb-0">
                    <strong>Ward:</strong><br>
                    <?= htmlspecialchars($bed['ward']) ?>
                </p>
            </div>
        </div>

        <!-- Doctor Info -->
        <div class="card">
            <div class="card-header">Attending Doctor</div>
            <div class="card-body">
                <p class="mb-1"><strong><?= htmlspecialchars($doctor['name']) ?></strong></p>
                <p class="mb-1"><?= $doctor['specialization'] ?></p>
                <small class="text-muted"><?= $doctor['phone'] ?></small>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Clinical Details -->
        <div class="card">
            <div class="card-header">Clinical Details</div>
            <div class="card-body">
                <?php if (!empty($admission['reason'])): ?>
                <div class="mb-3">
                    <label class="text-muted small">Reason for Admission</label>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($admission['reason'])) ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($admission['diagnosis'])): ?>
                <div class="mb-3">
                    <label class="text-muted small">Diagnosis</label>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($admission['diagnosis'])) ?></p>
                </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="text-muted small">Condition on Admission</label>
                    <p class="mb-0"><?= $admission['condition_on_admission'] ?? 'Not specified' ?></p>
                </div>

                <?php if (!empty($admission['notes'])): ?>
                <div class="mb-0">
                    <label class="text-muted small">Notes</label>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($admission['notes'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($admission['status'] === 'discharged' && !empty($admission['discharge_summary'])): ?>
        <!-- Discharge Summary -->
        <div class="card">
            <div class="card-header">Discharge Summary</div>
            <div class="card-body">
                <?= nl2br(htmlspecialchars($admission['discharge_summary'])) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <?php if ($admission['status'] === 'admitted'): ?>
        <div class="card">
            <div class="card-header">Quick Actions</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <a href="index.php?page=lab-order-add&patient_id=<?= $admission['patient_id'] ?>" class="btn btn-outline-info w-100">
                            <i class="bi bi-droplet me-2"></i>Order Lab Test
                        </a>
                    </div>
                    <div class="col-md-4 mb-2">
                        <a href="index.php?page=dispense&patient_id=<?= $admission['patient_id'] ?>" class="btn btn-outline-warning w-100">
                            <i class="bi bi-capsule me-2"></i>Dispense Medicine
                        </a>
                    </div>
                    <div class="col-md-4 mb-2">
                        <a href="index.php?page=surgery-add&patient_id=<?= $admission['patient_id'] ?>&ipd_id=<?= $id ?>" class="btn btn-outline-danger w-100">
                            <i class="bi bi-heart-pulse me-2"></i>Schedule Surgery
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
