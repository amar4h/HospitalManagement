<?php
/**
 * View Doctor Details
 */
requireAuth();

$storage = getStorage();
$id = (int)($_GET['id'] ?? 0);
$doctor = $storage->getById('doctors', $id);

if (!$doctor) {
    setFlashMessage('error', 'Doctor not found');
    redirect('index.php?page=doctors');
}

$department = $storage->getById('departments', $doctor['department_id']);

// Get doctor's appointments
$appointments = array_filter($storage->getAll('appointments'), function($apt) use ($id) {
    return $apt['doctor_id'] == $id;
});
$todayAppointments = array_filter($appointments, function($apt) {
    return $apt['date'] === date('Y-m-d') && $apt['status'] !== 'cancelled';
});

// Get doctor's OPD visits
$opdVisits = array_filter($storage->getAll('opd_visits'), function($visit) use ($id) {
    return $visit['doctor_id'] == $id;
});

// Statistics
$totalPatients = count(array_unique(array_column($opdVisits, 'patient_id')));
?>

<div class="page-header">
    <h1><i class="bi bi-person-badge me-2"></i>Doctor Profile</h1>
    <div class="quick-actions">
        <a href="index.php?page=appointment-add&doctor_id=<?= $id ?>" class="btn btn-success">
            <i class="bi bi-calendar-plus me-2"></i>Schedule Appointment
        </a>
        <a href="index.php?page=doctor-edit&id=<?= $id ?>" class="btn btn-warning">
            <i class="bi bi-pencil me-2"></i>Edit
        </a>
        <a href="index.php?page=doctors" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div class="row">
    <!-- Doctor Info Card -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 100px; height: 100px; font-size: 2.5rem;">
                    <?= strtoupper(substr($doctor['name'], 0, 1)) ?>
                </div>
                <h4><?= htmlspecialchars($doctor['name']) ?></h4>
                <p class="text-primary mb-2"><?= htmlspecialchars($doctor['specialization']) ?></p>
                <p class="text-muted mb-3">
                    <i class="bi bi-building me-1"></i><?= htmlspecialchars($department['name'] ?? 'N/A') ?>
                </p>
                <?= getStatusBadge($doctor['status']) ?>

                <hr>

                <div class="row text-center">
                    <div class="col-4">
                        <div class="fw-bold text-primary fs-4"><?= $doctor['experience'] ?></div>
                        <small class="text-muted">Years Exp</small>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold text-success fs-4"><?= $totalPatients ?></div>
                        <small class="text-muted">Patients</small>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold text-info fs-4"><?= count($todayAppointments) ?></div>
                        <small class="text-muted">Today</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="card">
            <div class="card-header">Contact Information</div>
            <div class="card-body">
                <p class="mb-2">
                    <i class="bi bi-telephone me-2 text-muted"></i>
                    <?= htmlspecialchars($doctor['phone']) ?>
                </p>
                <?php if (!empty($doctor['email'])): ?>
                <p class="mb-2">
                    <i class="bi bi-envelope me-2 text-muted"></i>
                    <?= htmlspecialchars($doctor['email']) ?>
                </p>
                <?php endif; ?>
                <?php if (!empty($doctor['address'])): ?>
                <p class="mb-0">
                    <i class="bi bi-geo-alt me-2 text-muted"></i>
                    <?= htmlspecialchars($doctor['address']) ?>
                </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Professional Details -->
        <div class="card">
            <div class="card-header">Professional Details</div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>Qualification:</strong><br>
                    <?= htmlspecialchars($doctor['qualification'] ?? 'N/A') ?>
                </p>
                <p class="mb-2">
                    <strong>Consultation Fee:</strong><br>
                    <?= formatCurrency($doctor['consultation_fee']) ?>
                </p>
                <?php if (!empty($doctor['bio'])): ?>
                <p class="mb-0">
                    <strong>About:</strong><br>
                    <?= nl2br(htmlspecialchars($doctor['bio'])) ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Appointments and History -->
    <div class="col-md-8">
        <!-- Today's Appointments -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-check me-2"></i>Today's Appointments</span>
                <span class="badge bg-primary"><?= count($todayAppointments) ?></span>
            </div>
            <div class="card-body">
                <?php if (empty($todayAppointments)): ?>
                <p class="text-muted mb-0">No appointments scheduled for today</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Patient</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($todayAppointments as $apt): ?>
                            <?php $patient = $storage->getById('patients', $apt['patient_id']); ?>
                            <tr>
                                <td><?= date('h:i A', strtotime($apt['time'])) ?></td>
                                <td>
                                    <a href="index.php?page=patient-view&id=<?= $apt['patient_id'] ?>">
                                        <?= htmlspecialchars($patient['name'] ?? 'N/A') ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($patient['phone'] ?? '-') ?></td>
                                <td><?= getStatusBadge($apt['status']) ?></td>
                                <td>
                                    <a href="index.php?page=opd-add&appointment_id=<?= $apt['id'] ?>&patient_id=<?= $apt['patient_id'] ?>&doctor_id=<?= $id ?>" class="btn btn-sm btn-success">
                                        <i class="bi bi-clipboard2-plus"></i> Start Visit
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

        <!-- Recent OPD Visits -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clipboard2-pulse me-2"></i>Recent OPD Visits</span>
                <span class="badge bg-info"><?= count($opdVisits) ?> total</span>
            </div>
            <div class="card-body">
                <?php if (empty($opdVisits)): ?>
                <p class="text-muted mb-0">No OPD visits recorded</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Patient</th>
                                <th>Diagnosis</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice(array_reverse($opdVisits), 0, 10) as $visit): ?>
                            <?php $patient = $storage->getById('patients', $visit['patient_id']); ?>
                            <tr>
                                <td><?= formatDate($visit['date']) ?></td>
                                <td>
                                    <a href="index.php?page=patient-view&id=<?= $visit['patient_id'] ?>">
                                        <?= htmlspecialchars($patient['name'] ?? 'N/A') ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars(substr($visit['diagnosis'] ?? '-', 0, 40)) ?>...</td>
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

        <!-- Upcoming Appointments -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-calendar-week me-2"></i>Upcoming Appointments
            </div>
            <div class="card-body">
                <?php
                $upcomingAppointments = array_filter($appointments, function($apt) {
                    return $apt['date'] > date('Y-m-d') && $apt['status'] !== 'cancelled';
                });
                usort($upcomingAppointments, function($a, $b) {
                    return strcmp($a['date'] . $a['time'], $b['date'] . $b['time']);
                });
                $upcomingAppointments = array_slice($upcomingAppointments, 0, 5);
                ?>

                <?php if (empty($upcomingAppointments)): ?>
                <p class="text-muted mb-0">No upcoming appointments</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Patient</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcomingAppointments as $apt): ?>
                            <?php $patient = $storage->getById('patients', $apt['patient_id']); ?>
                            <tr>
                                <td><?= formatDate($apt['date']) ?></td>
                                <td><?= date('h:i A', strtotime($apt['time'])) ?></td>
                                <td><?= htmlspecialchars($patient['name'] ?? 'N/A') ?></td>
                                <td><?= getStatusBadge($apt['status']) ?></td>
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
