<?php
/**
 * View OPD Visit Details
 */
requireAuth();

$storage = getStorage();
$id = (int)($_GET['id'] ?? 0);
$visit = $storage->getById('opd_visits', $id);

if (!$visit) {
    setFlashMessage('error', 'OPD visit not found');
    redirect('index.php?page=opd');
}

$patient = $storage->getById('patients', $visit['patient_id']);
$doctor = $storage->getById('doctors', $visit['doctor_id']);
$prescriptions = array_filter($storage->getAll('prescriptions'), function($p) use ($id) {
    return $p['opd_visit_id'] == $id;
});
?>

<div class="page-header">
    <h1><i class="bi bi-clipboard2-pulse me-2"></i>OPD Visit Details</h1>
    <div class="quick-actions">
        <button onclick="printElement('printArea')" class="btn btn-info">
            <i class="bi bi-printer me-2"></i>Print
        </button>
        <a href="index.php?page=invoice-add&opd_id=<?= $id ?>&patient_id=<?= $visit['patient_id'] ?>" class="btn btn-success">
            <i class="bi bi-receipt me-2"></i>Generate Bill
        </a>
        <a href="index.php?page=opd" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div id="printArea">
    <div class="row">
        <div class="col-lg-8">
            <!-- Prescription -->
            <div class="card prescription-print">
                <div class="prescription-header">
                    <div class="row">
                        <div class="col-8">
                            <h4 class="text-primary mb-1"><?= HOSPITAL_NAME ?></h4>
                            <small class="text-muted"><?= HOSPITAL_ADDRESS ?></small><br>
                            <small class="text-muted">Phone: <?= HOSPITAL_PHONE ?></small>
                        </div>
                        <div class="col-4 text-end">
                            <p class="mb-1"><strong>Date:</strong> <?= formatDate($visit['date']) ?></p>
                            <p class="mb-0"><strong>Visit #:</strong> OPD-<?= str_pad($id, 6, '0', STR_PAD_LEFT) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Patient Info -->
                <div class="row mb-4">
                    <div class="col-6">
                        <p class="mb-1"><strong>Patient:</strong> <?= htmlspecialchars($patient['name']) ?></p>
                        <p class="mb-1"><strong>Patient ID:</strong> <?= $patient['patient_id'] ?></p>
                        <p class="mb-0"><strong>Age/Gender:</strong> <?= calculateAge($patient['dob']) ?> years / <?= $patient['gender'] ?></p>
                    </div>
                    <div class="col-6 text-end">
                        <p class="mb-1"><strong>Doctor:</strong> <?= htmlspecialchars($doctor['name']) ?></p>
                        <p class="mb-0"><strong>Specialization:</strong> <?= $doctor['specialization'] ?></p>
                    </div>
                </div>

                <!-- Vitals -->
                <?php if (!empty($visit['bp']) || !empty($visit['pulse'])): ?>
                <div class="mb-4 p-3 bg-light rounded">
                    <h6 class="mb-2">Vital Signs</h6>
                    <div class="row">
                        <div class="col-4 col-md-2">
                            <small class="text-muted">BP</small><br>
                            <strong><?= $visit['bp'] ?: '-' ?></strong>
                        </div>
                        <div class="col-4 col-md-2">
                            <small class="text-muted">Pulse</small><br>
                            <strong><?= $visit['pulse'] ?: '-' ?> bpm</strong>
                        </div>
                        <div class="col-4 col-md-2">
                            <small class="text-muted">Temp</small><br>
                            <strong><?= $visit['temperature'] ?: '-' ?>°F</strong>
                        </div>
                        <div class="col-4 col-md-2">
                            <small class="text-muted">Weight</small><br>
                            <strong><?= $visit['weight'] ?: '-' ?> kg</strong>
                        </div>
                        <div class="col-4 col-md-2">
                            <small class="text-muted">Height</small><br>
                            <strong><?= $visit['height'] ?: '-' ?> cm</strong>
                        </div>
                        <div class="col-4 col-md-2">
                            <small class="text-muted">SpO2</small><br>
                            <strong><?= $visit['spo2'] ?: '-' ?>%</strong>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Clinical Notes -->
                <?php if (!empty($visit['symptoms'])): ?>
                <div class="mb-3">
                    <h6>Chief Complaints</h6>
                    <p><?= nl2br(htmlspecialchars($visit['symptoms'])) ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($visit['diagnosis'])): ?>
                <div class="mb-3">
                    <h6>Diagnosis</h6>
                    <p><?= nl2br(htmlspecialchars($visit['diagnosis'])) ?></p>
                </div>
                <?php endif; ?>

                <!-- Prescription -->
                <?php if (!empty($prescriptions)): ?>
                <div class="mb-4">
                    <h6 class="rx-symbol mb-3">℞</h6>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Medicine</th>
                                <th>Dosage</th>
                                <th>Frequency</th>
                                <th>Duration</th>
                                <th>Instructions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($prescriptions as $rx): ?>
                            <?php $medicine = $storage->getById('medicines', $rx['medicine_id']); ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><strong><?= htmlspecialchars($medicine['name'] ?? 'N/A') ?></strong></td>
                                <td><?= htmlspecialchars($rx['dosage']) ?></td>
                                <td><?= htmlspecialchars($rx['frequency']) ?></td>
                                <td><?= htmlspecialchars($rx['duration']) ?></td>
                                <td><?= htmlspecialchars($rx['instructions']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <?php if (!empty($visit['treatment'])): ?>
                <div class="mb-3">
                    <h6>Treatment / Advice</h6>
                    <p><?= nl2br(htmlspecialchars($visit['treatment'])) ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($visit['follow_up_date'])): ?>
                <div class="alert alert-info">
                    <i class="bi bi-calendar-event me-2"></i>
                    <strong>Follow-up Date:</strong> <?= formatDate($visit['follow_up_date']) ?>
                </div>
                <?php endif; ?>

                <!-- Footer -->
                <div class="mt-5 pt-4 border-top">
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">This is a computer generated prescription</small>
                        </div>
                        <div class="col-6 text-end">
                            <p class="mb-0"><strong><?= htmlspecialchars($doctor['name']) ?></strong></p>
                            <small class="text-muted"><?= $doctor['qualification'] ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 no-print">
            <!-- Patient Card -->
            <div class="card">
                <div class="card-header">Patient Information</div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 60px; height: 60px; font-size: 1.5rem;">
                            <?= strtoupper(substr($patient['name'], 0, 1)) ?>
                        </div>
                        <h5 class="mt-2 mb-0"><?= htmlspecialchars($patient['name']) ?></h5>
                        <small class="text-muted"><?= $patient['patient_id'] ?></small>
                    </div>

                    <hr>

                    <p class="mb-2"><i class="bi bi-telephone me-2"></i><?= $patient['phone'] ?></p>
                    <?php if (!empty($patient['allergies'])): ?>
                    <div class="alert alert-danger py-2">
                        <strong><i class="bi bi-exclamation-triangle me-1"></i>Allergies:</strong><br>
                        <?= htmlspecialchars($patient['allergies']) ?>
                    </div>
                    <?php endif; ?>

                    <a href="index.php?page=patient-view&id=<?= $patient['id'] ?>" class="btn btn-outline-primary btn-sm w-100">
                        View Full Profile
                    </a>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-header">Actions</div>
                <div class="card-body">
                    <a href="index.php?page=lab-order-add&patient_id=<?= $patient['id'] ?>" class="btn btn-outline-info w-100 mb-2">
                        <i class="bi bi-droplet me-2"></i>Order Lab Test
                    </a>
                    <a href="index.php?page=appointment-add&patient_id=<?= $patient['id'] ?>&doctor_id=<?= $doctor['id'] ?>" class="btn btn-outline-success w-100 mb-2">
                        <i class="bi bi-calendar-plus me-2"></i>Schedule Follow-up
                    </a>
                    <a href="index.php?page=dispense&patient_id=<?= $patient['id'] ?>&opd_id=<?= $id ?>" class="btn btn-outline-warning w-100">
                        <i class="bi bi-capsule me-2"></i>Dispense Medicine
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
