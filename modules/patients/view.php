<?php
/**
 * View Patient Details
 */
requireAuth();
requireRole(['admin', 'doctor', 'nurse', 'receptionist']);

$storage = getStorage();
$id = (int)($_GET['id'] ?? 0);
$patient = $storage->getById('patients', $id);

if (!$patient) {
    setFlashMessage('error', 'Patient not found');
    redirect('index.php?page=patients');
}

// Get patient's appointments
$appointments = array_filter($storage->getAll('appointments'), function($apt) use ($id) {
    return $apt['patient_id'] == $id;
});
$appointments = array_reverse($appointments);

// Get patient's OPD visits
$opdVisits = array_filter($storage->getAll('opd_visits'), function($visit) use ($id) {
    return $visit['patient_id'] == $id;
});
$opdVisits = array_reverse($opdVisits);

// Get patient's IPD admissions
$ipdAdmissions = array_filter($storage->getAll('ipd_admissions'), function($adm) use ($id) {
    return $adm['patient_id'] == $id;
});
$ipdAdmissions = array_reverse($ipdAdmissions);

// Get patient's lab orders
$labOrders = array_filter($storage->getAll('lab_orders'), function($order) use ($id) {
    return $order['patient_id'] == $id;
});
$labOrders = array_reverse($labOrders);

// Get patient's invoices
$invoices = array_filter($storage->getAll('invoices'), function($inv) use ($id) {
    return $inv['patient_id'] == $id;
});
$invoices = array_reverse($invoices);
?>

<div class="page-header">
    <h1><i class="bi bi-person me-2"></i>Patient Details</h1>
    <div class="quick-actions">
        <a href="index.php?page=appointment-add&patient_id=<?= $id ?>" class="btn btn-success">
            <i class="bi bi-calendar-plus me-2"></i>New Appointment
        </a>
        <a href="index.php?page=opd-add&patient_id=<?= $id ?>" class="btn btn-info">
            <i class="bi bi-clipboard2-plus me-2"></i>New OPD Visit
        </a>
        <a href="index.php?page=patient-edit&id=<?= $id ?>" class="btn btn-warning">
            <i class="bi bi-pencil me-2"></i>Edit
        </a>
        <a href="index.php?page=patients" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div class="row">
    <!-- Patient Info Card -->
    <div class="col-md-4">
        <div class="patient-info-card mb-4">
            <div class="d-flex align-items-center mb-3">
                <div class="avatar bg-white text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 70px; height: 70px; font-size: 1.8rem;">
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

        <!-- Contact Information -->
        <div class="card">
            <div class="card-header">Contact Information</div>
            <div class="card-body">
                <p class="mb-2">
                    <i class="bi bi-telephone me-2 text-muted"></i>
                    <?= htmlspecialchars($patient['phone']) ?>
                </p>
                <?php if (!empty($patient['email'])): ?>
                <p class="mb-2">
                    <i class="bi bi-envelope me-2 text-muted"></i>
                    <?= htmlspecialchars($patient['email']) ?>
                </p>
                <?php endif; ?>
                <?php if (!empty($patient['address'])): ?>
                <p class="mb-2">
                    <i class="bi bi-geo-alt me-2 text-muted"></i>
                    <?= htmlspecialchars($patient['address']) ?>
                    <?php if (!empty($patient['city'])): ?>
                    <br><?= htmlspecialchars($patient['city']) ?>, <?= htmlspecialchars($patient['state'] ?? '') ?> <?= htmlspecialchars($patient['zip_code'] ?? '') ?>
                    <?php endif; ?>
                </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Emergency Contact -->
        <?php if (!empty($patient['emergency_contact_name'])): ?>
        <div class="card">
            <div class="card-header">Emergency Contact</div>
            <div class="card-body">
                <p class="mb-1"><strong><?= htmlspecialchars($patient['emergency_contact_name']) ?></strong></p>
                <p class="mb-1"><?= htmlspecialchars($patient['emergency_contact_phone']) ?></p>
                <small class="text-muted"><?= htmlspecialchars($patient['emergency_contact_relation']) ?></small>
            </div>
        </div>
        <?php endif; ?>

        <!-- Medical Info -->
        <div class="card">
            <div class="card-header">Medical Information</div>
            <div class="card-body">
                <?php if (!empty($patient['allergies'])): ?>
                <div class="mb-3">
                    <label class="text-muted small">Allergies</label>
                    <p class="mb-0 text-danger"><?= nl2br(htmlspecialchars($patient['allergies'])) ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($patient['chronic_conditions'])): ?>
                <div class="mb-3">
                    <label class="text-muted small">Chronic Conditions</label>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($patient['chronic_conditions'])) ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($patient['current_medications'])): ?>
                <div class="mb-0">
                    <label class="text-muted small">Current Medications</label>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($patient['current_medications'])) ?></p>
                </div>
                <?php endif; ?>

                <?php if (empty($patient['allergies']) && empty($patient['chronic_conditions']) && empty($patient['current_medications'])): ?>
                <p class="text-muted mb-0">No medical information recorded</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Insurance -->
        <?php if (!empty($patient['insurance_provider'])): ?>
        <div class="card">
            <div class="card-header">Insurance</div>
            <div class="card-body">
                <p class="mb-1"><strong><?= htmlspecialchars($patient['insurance_provider']) ?></strong></p>
                <p class="mb-0">Policy: <?= htmlspecialchars($patient['insurance_id']) ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Patient History -->
    <div class="col-md-8">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#appointments">
                    <i class="bi bi-calendar-check me-1"></i>Appointments (<?= count($appointments) ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#opd">
                    <i class="bi bi-clipboard2-pulse me-1"></i>OPD Visits (<?= count($opdVisits) ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#ipd">
                    <i class="bi bi-hospital me-1"></i>IPD (<?= count($ipdAdmissions) ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#lab">
                    <i class="bi bi-droplet me-1"></i>Lab Tests (<?= count($labOrders) ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#billing">
                    <i class="bi bi-receipt me-1"></i>Billing (<?= count($invoices) ?>)
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Appointments Tab -->
            <div class="tab-pane fade show active" id="appointments">
                <div class="card card-body border-top-0 rounded-top-0">
                    <?php if (empty($appointments)): ?>
                    <p class="text-muted mb-0">No appointments found</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Doctor</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($appointments, 0, 10) as $apt): ?>
                                <?php $doctor = $storage->getById('doctors', $apt['doctor_id']); ?>
                                <tr>
                                    <td><?= formatDate($apt['date']) ?></td>
                                    <td><?= date('h:i A', strtotime($apt['time'])) ?></td>
                                    <td><?= htmlspecialchars($doctor['name'] ?? 'N/A') ?></td>
                                    <td><?= getStatusBadge($apt['status']) ?></td>
                                    <td>
                                        <a href="index.php?page=opd-add&appointment_id=<?= $apt['id'] ?>&patient_id=<?= $id ?>" class="btn btn-sm btn-info">
                                            <i class="bi bi-clipboard2-plus"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- OPD Tab -->
            <div class="tab-pane fade" id="opd">
                <div class="card card-body border-top-0 rounded-top-0">
                    <?php if (empty($opdVisits)): ?>
                    <p class="text-muted mb-0">No OPD visits found</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Doctor</th>
                                    <th>Diagnosis</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($opdVisits, 0, 10) as $visit): ?>
                                <?php $doctor = $storage->getById('doctors', $visit['doctor_id']); ?>
                                <tr>
                                    <td><?= formatDate($visit['date']) ?></td>
                                    <td><?= htmlspecialchars($doctor['name'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars(substr($visit['diagnosis'] ?? '', 0, 50)) ?>...</td>
                                    <td>
                                        <a href="index.php?page=opd-visit&id=<?= $visit['id'] ?>" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- IPD Tab -->
            <div class="tab-pane fade" id="ipd">
                <div class="card card-body border-top-0 rounded-top-0">
                    <?php if (empty($ipdAdmissions)): ?>
                    <p class="text-muted mb-0">No IPD admissions found</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Admission Date</th>
                                    <th>Bed</th>
                                    <th>Doctor</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ipdAdmissions as $adm): ?>
                                <?php
                                $doctor = $storage->getById('doctors', $adm['doctor_id']);
                                $bed = $storage->getById('beds', $adm['bed_id']);
                                ?>
                                <tr>
                                    <td><?= formatDate($adm['admission_date']) ?></td>
                                    <td><?= htmlspecialchars($bed['bed_number'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($doctor['name'] ?? 'N/A') ?></td>
                                    <td><?= getStatusBadge($adm['status']) ?></td>
                                    <td>
                                        <a href="index.php?page=ipd-admission&id=<?= $adm['id'] ?>" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Lab Tab -->
            <div class="tab-pane fade" id="lab">
                <div class="card card-body border-top-0 rounded-top-0">
                    <?php if (empty($labOrders)): ?>
                    <p class="text-muted mb-0">No lab tests found</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Test</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($labOrders, 0, 10) as $order): ?>
                                <?php $test = $storage->getById('lab_tests', $order['test_id']); ?>
                                <tr>
                                    <td><?= formatDate($order['created_at']) ?></td>
                                    <td><?= htmlspecialchars($test['name'] ?? 'N/A') ?></td>
                                    <td><?= getStatusBadge($order['status']) ?></td>
                                    <td>
                                        <a href="index.php?page=lab-result&id=<?= $order['id'] ?>" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Billing Tab -->
            <div class="tab-pane fade" id="billing">
                <div class="card card-body border-top-0 rounded-top-0">
                    <?php if (empty($invoices)): ?>
                    <p class="text-muted mb-0">No invoices found</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($invoices, 0, 10) as $inv): ?>
                                <tr>
                                    <td><?= $inv['invoice_number'] ?></td>
                                    <td><?= formatDate($inv['created_at']) ?></td>
                                    <td><?= formatCurrency($inv['total_amount']) ?></td>
                                    <td><?= getStatusBadge($inv['payment_status']) ?></td>
                                    <td>
                                        <a href="index.php?page=invoice-view&id=<?= $inv['id'] ?>" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
