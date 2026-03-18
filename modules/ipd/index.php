<?php
/**
 * IPD Admissions List
 */
requireAuth();
requireRole(['admin', 'doctor', 'nurse', 'receptionist']);

$storage = getStorage();

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'delete') {
        $admission = $storage->getById('ipd_admissions', $id);
        if ($admission && $admission['bed_id']) {
            $storage->update('beds', $admission['bed_id'], ['status' => 'available', 'patient_id' => null]);
        }
        $storage->delete('ipd_admissions', $id);
        setFlashMessage('success', 'Admission deleted');
        redirect('index.php?page=ipd');
    }
}

// Get admissions
$admissions = array_reverse($storage->getAll('ipd_admissions'));

// Filter by status
$filterStatus = $_GET['status'] ?? 'admitted';
if (!empty($filterStatus)) {
    $admissions = array_filter($admissions, function($adm) use ($filterStatus) {
        return $adm['status'] === $filterStatus;
    });
}
?>

<div class="page-header">
    <h1><i class="bi bi-hospital me-2"></i>IPD Management</h1>
    <div class="quick-actions">
        <a href="index.php?page=ipd-add" class="btn btn-primary">
            <i class="bi bi-person-plus me-2"></i>New Admission
        </a>
    </div>
</div>

<!-- Status Tabs -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?= $filterStatus === 'admitted' ? 'active' : '' ?>" href="index.php?page=ipd&status=admitted">
            <i class="bi bi-hospital me-1"></i>Admitted
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $filterStatus === 'discharged' ? 'active' : '' ?>" href="index.php?page=ipd&status=discharged">
            <i class="bi bi-check-circle me-1"></i>Discharged
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $filterStatus === '' ? 'active' : '' ?>" href="index.php?page=ipd&status=">
            <i class="bi bi-list me-1"></i>All
        </a>
    </li>
</ul>

<div class="card">
    <div class="card-header">
        IPD Admissions (<?= count($admissions) ?>)
    </div>
    <div class="card-body">
        <?php if (empty($admissions)): ?>
        <div class="empty-state">
            <i class="bi bi-hospital"></i>
            <h5>No Admissions Found</h5>
            <a href="index.php?page=ipd-add" class="btn btn-primary">
                <i class="bi bi-person-plus me-2"></i>New Admission
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Admission #</th>
                        <th>Patient</th>
                        <th>Bed</th>
                        <th>Doctor</th>
                        <th>Admitted</th>
                        <th>Days</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admissions as $adm): ?>
                    <?php
                    $patient = $storage->getById('patients', $adm['patient_id']);
                    $doctor = $storage->getById('doctors', $adm['doctor_id']);
                    $bed = $storage->getById('beds', $adm['bed_id']);
                    $days = ceil((strtotime($adm['discharge_date'] ?? 'now') - strtotime($adm['admission_date'])) / 86400);
                    ?>
                    <tr>
                        <td><strong>IPD-<?= str_pad($adm['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                        <td>
                            <a href="index.php?page=patient-view&id=<?= $adm['patient_id'] ?>">
                                <?= htmlspecialchars($patient['name'] ?? 'N/A') ?>
                            </a>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?= $bed['bed_number'] ?? 'N/A' ?></span>
                            <small class="text-muted d-block"><?= $bed['ward'] ?? '' ?></small>
                        </td>
                        <td><?= htmlspecialchars($doctor['name'] ?? 'N/A') ?></td>
                        <td><?= formatDate($adm['admission_date']) ?></td>
                        <td><span class="badge bg-info"><?= $days ?> days</span></td>
                        <td><?= getStatusBadge($adm['status']) ?></td>
                        <td class="action-btns">
                            <a href="index.php?page=ipd-admission&id=<?= $adm['id'] ?>" class="btn btn-sm btn-info" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <?php if ($adm['status'] === 'admitted'): ?>
                            <a href="index.php?page=ipd-discharge&id=<?= $adm['id'] ?>" class="btn btn-sm btn-success" title="Discharge">
                                <i class="bi bi-box-arrow-right"></i>
                            </a>
                            <?php endif; ?>
                            <a href="index.php?page=ipd&action=delete&id=<?= $adm['id'] ?>" class="btn btn-sm btn-danger btn-delete" title="Delete">
                                <i class="bi bi-trash"></i>
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

<!-- Bed Overview -->
<div class="card mt-4">
    <div class="card-header">
        <i class="bi bi-grid me-2"></i>Bed Availability
    </div>
    <div class="card-body">
        <?php
        $beds = $storage->getAll('beds');
        $wards = [];
        foreach ($beds as $bed) {
            $wards[$bed['ward']][] = $bed;
        }
        ?>
        <?php foreach ($wards as $wardName => $wardBeds): ?>
        <h6 class="mb-3"><?= htmlspecialchars($wardName) ?></h6>
        <div class="bed-grid mb-4">
            <?php foreach ($wardBeds as $bed): ?>
            <div class="bed-item <?= $bed['status'] ?>">
                <div class="bed-number"><?= $bed['bed_number'] ?></div>
                <div class="bed-status"><?= ucfirst($bed['status']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
