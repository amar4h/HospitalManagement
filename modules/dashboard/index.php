<?php
/**
 * Dashboard
 */
requireAuth();

$storage = getStorage();
$stats = getDashboardStats();
$role = getCurrentUserRole();
$today = date('Y-m-d');

// Get recent appointments
$appointments = array_filter($storage->getAll('appointments'), function($apt) use ($today) {
    return $apt['date'] === $today && $apt['status'] !== 'cancelled';
});
$appointments = array_slice($appointments, 0, 5);

// Get recent patients
$patients = array_slice(array_reverse($storage->getAll('patients')), 0, 5);

// Get recent OPD visits
$opdVisits = array_filter($storage->getAll('opd_visits'), function($visit) use ($today) {
    return $visit['date'] === $today;
});

// Get IPD admissions
$ipdAdmissions = array_filter($storage->getAll('ipd_admissions'), function($adm) {
    return $adm['status'] === 'admitted';
});

// Get pending lab orders
$pendingLabs = array_filter($storage->getAll('lab_orders'), function($order) {
    return $order['status'] === 'pending' || $order['status'] === 'sample_collected';
});
?>

<div class="page-header">
    <h1><i class="bi bi-speedometer2 me-2"></i>Dashboard</h1>
    <div class="text-muted">
        <i class="bi bi-calendar me-1"></i><?= date('l, F j, Y') ?>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card bg-primary">
            <i class="bi bi-people stat-icon"></i>
            <div class="stat-value"><?= $stats['total_patients'] ?></div>
            <div class="stat-label">Total Patients</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card bg-success">
            <i class="bi bi-calendar-check stat-icon"></i>
            <div class="stat-value"><?= $stats['today_appointments'] ?></div>
            <div class="stat-label">Today's Appointments</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card bg-info">
            <i class="bi bi-hospital stat-icon"></i>
            <div class="stat-value"><?= $stats['ipd_patients'] ?></div>
            <div class="stat-label">IPD Patients</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card bg-warning">
            <i class="bi bi-bed stat-icon"></i>
            <div class="stat-value"><?= $stats['available_beds'] ?></div>
            <div class="stat-label">Available Beds</div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card bg-secondary">
            <i class="bi bi-person-badge stat-icon"></i>
            <div class="stat-value"><?= $stats['total_doctors'] ?></div>
            <div class="stat-label">Active Doctors</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card bg-primary">
            <i class="bi bi-clipboard2-pulse stat-icon"></i>
            <div class="stat-value"><?= count($opdVisits) ?></div>
            <div class="stat-label">Today's OPD Visits</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card bg-danger">
            <i class="bi bi-droplet stat-icon"></i>
            <div class="stat-value"><?= count($pendingLabs) ?></div>
            <div class="stat-label">Pending Lab Tests</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card bg-success">
            <i class="bi bi-capsule stat-icon"></i>
            <div class="stat-value"><?= $stats['low_stock_medicines'] ?></div>
            <div class="stat-label">Low Stock Medicines</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<?php if (in_array($role, ['admin', 'receptionist', 'doctor'])): ?>
<div class="card mb-4">
    <div class="card-header">Quick Actions</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 col-sm-6 mb-2">
                <a href="index.php?page=patient-add" class="btn btn-outline-primary w-100">
                    <i class="bi bi-person-plus me-2"></i>New Patient
                </a>
            </div>
            <div class="col-md-3 col-sm-6 mb-2">
                <a href="index.php?page=appointment-add" class="btn btn-outline-success w-100">
                    <i class="bi bi-calendar-plus me-2"></i>New Appointment
                </a>
            </div>
            <div class="col-md-3 col-sm-6 mb-2">
                <a href="index.php?page=opd-add" class="btn btn-outline-info w-100">
                    <i class="bi bi-clipboard2-plus me-2"></i>New OPD Visit
                </a>
            </div>
            <div class="col-md-3 col-sm-6 mb-2">
                <a href="index.php?page=ipd-add" class="btn btn-outline-warning w-100">
                    <i class="bi bi-hospital me-2"></i>New Admission
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <!-- Today's Appointments -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-check me-2"></i>Today's Appointments</span>
                <a href="index.php?page=appointments" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($appointments)): ?>
                <div class="empty-state py-4">
                    <i class="bi bi-calendar-x"></i>
                    <p class="text-muted mb-0">No appointments scheduled for today</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $apt): ?>
                            <?php
                            $patient = $storage->getById('patients', $apt['patient_id']);
                            $doctor = $storage->getById('doctors', $apt['doctor_id']);
                            ?>
                            <tr>
                                <td><?= date('h:i A', strtotime($apt['time'])) ?></td>
                                <td><?= htmlspecialchars($patient['name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($doctor['name'] ?? 'N/A') ?></td>
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

    <!-- Recent Patients -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people me-2"></i>Recent Patients</span>
                <a href="index.php?page=patients" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($patients)): ?>
                <div class="empty-state py-4">
                    <i class="bi bi-people"></i>
                    <p class="text-muted mb-0">No patients registered yet</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Patient ID</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patients as $patient): ?>
                            <tr>
                                <td><a href="index.php?page=patient-view&id=<?= $patient['id'] ?>"><?= $patient['patient_id'] ?></a></td>
                                <td><?= htmlspecialchars($patient['name']) ?></td>
                                <td><?= htmlspecialchars($patient['phone']) ?></td>
                                <td><?= formatDate($patient['created_at']) ?></td>
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

<div class="row">
    <!-- IPD Patients -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-hospital me-2"></i>Current IPD Patients</span>
                <a href="index.php?page=ipd" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($ipdAdmissions)): ?>
                <div class="empty-state py-4">
                    <i class="bi bi-hospital"></i>
                    <p class="text-muted mb-0">No patients currently admitted</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Bed</th>
                                <th>Admitted</th>
                                <th>Days</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($ipdAdmissions, 0, 5) as $adm): ?>
                            <?php
                            $patient = $storage->getById('patients', $adm['patient_id']);
                            $bed = $storage->getById('beds', $adm['bed_id']);
                            $days = (strtotime('now') - strtotime($adm['admission_date'])) / 86400;
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($patient['name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($bed['bed_number'] ?? 'N/A') ?></td>
                                <td><?= formatDate($adm['admission_date']) ?></td>
                                <td><?= ceil($days) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Pending Lab Tests -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-droplet me-2"></i>Pending Lab Tests</span>
                <a href="index.php?page=laboratory" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($pendingLabs)): ?>
                <div class="empty-state py-4">
                    <i class="bi bi-droplet"></i>
                    <p class="text-muted mb-0">No pending lab tests</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Test</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($pendingLabs, 0, 5) as $lab): ?>
                            <?php
                            $patient = $storage->getById('patients', $lab['patient_id']);
                            $test = $storage->getById('lab_tests', $lab['test_id']);
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($patient['name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($test['name'] ?? 'N/A') ?></td>
                                <td><?= getStatusBadge($lab['status']) ?></td>
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

<!-- Bed Availability Overview -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-grid me-2"></i>Bed Availability Overview
    </div>
    <div class="card-body">
        <?php
        $beds = $storage->getAll('beds');
        $wards = [];
        foreach ($beds as $bed) {
            if (!isset($wards[$bed['ward']])) {
                $wards[$bed['ward']] = ['total' => 0, 'available' => 0, 'occupied' => 0];
            }
            $wards[$bed['ward']]['total']++;
            if ($bed['status'] === 'available') {
                $wards[$bed['ward']]['available']++;
            } else {
                $wards[$bed['ward']]['occupied']++;
            }
        }
        ?>
        <div class="row">
            <?php foreach ($wards as $wardName => $wardStats): ?>
            <div class="col-md-4 col-sm-6 mb-3">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title"><?= htmlspecialchars($wardName) ?></h6>
                        <div class="d-flex justify-content-between">
                            <span class="text-success"><i class="bi bi-check-circle me-1"></i><?= $wardStats['available'] ?> Available</span>
                            <span class="text-danger"><i class="bi bi-x-circle me-1"></i><?= $wardStats['occupied'] ?> Occupied</span>
                        </div>
                        <div class="progress mt-2" style="height: 8px;">
                            <?php $percentage = ($wardStats['occupied'] / $wardStats['total']) * 100; ?>
                            <div class="progress-bar bg-danger" style="width: <?= $percentage ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
