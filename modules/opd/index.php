<?php
/**
 * OPD Visits List
 */
requireAuth();
requireRole(['admin', 'doctor', 'nurse', 'receptionist']);

$storage = getStorage();

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $storage->delete('opd_visits', (int)$_GET['id']);
    setFlashMessage('success', 'OPD visit deleted');
    redirect('index.php?page=opd');
}

// Get OPD visits
$opdVisits = array_reverse($storage->getAll('opd_visits'));

// Filter by date
$filterDate = $_GET['date'] ?? '';
if (!empty($filterDate)) {
    $opdVisits = array_filter($opdVisits, function($visit) use ($filterDate) {
        return $visit['date'] === $filterDate;
    });
}

// Filter by doctor for doctor role
if (getCurrentUserRole() === 'doctor') {
    $doctorId = $_SESSION['user']['doctor_id'] ?? 0;
    $opdVisits = array_filter($opdVisits, function($visit) use ($doctorId) {
        return $visit['doctor_id'] == $doctorId;
    });
}
?>

<div class="page-header">
    <h1><i class="bi bi-clipboard2-pulse me-2"></i>OPD Management</h1>
    <div class="quick-actions">
        <a href="index.php?page=opd-add" class="btn btn-primary">
            <i class="bi bi-clipboard2-plus me-2"></i>New OPD Visit
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row align-items-end">
            <input type="hidden" name="page" value="opd">
            <div class="col-md-4">
                <label class="form-label">Filter by Date</label>
                <input type="date" name="date" class="form-control" value="<?= $filterDate ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-filter me-2"></i>Filter
                </button>
                <a href="index.php?page=opd" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span>OPD Visits (<?= count($opdVisits) ?>)</span>
    </div>
    <div class="card-body">
        <?php if (empty($opdVisits)): ?>
        <div class="empty-state">
            <i class="bi bi-clipboard2-x"></i>
            <h5>No OPD Visits</h5>
            <p class="text-muted">No OPD visits found</p>
            <a href="index.php?page=opd-add" class="btn btn-primary">
                <i class="bi bi-clipboard2-plus me-2"></i>New OPD Visit
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Diagnosis</th>
                        <th class="hide-mobile">Vitals</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($opdVisits as $visit): ?>
                    <?php
                    $patient = $storage->getById('patients', $visit['patient_id']);
                    $doctor = $storage->getById('doctors', $visit['doctor_id']);
                    ?>
                    <tr>
                        <td><?= formatDate($visit['date']) ?></td>
                        <td>
                            <a href="index.php?page=patient-view&id=<?= $visit['patient_id'] ?>">
                                <?= htmlspecialchars($patient['name'] ?? 'N/A') ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($doctor['name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars(substr($visit['diagnosis'] ?? '-', 0, 30)) ?>...</td>
                        <td class="hide-mobile">
                            <?php if (!empty($visit['bp'])): ?>
                            <small>BP: <?= $visit['bp'] ?>, T: <?= $visit['temperature'] ?? '-' ?>°F</small>
                            <?php else: ?>
                            <small class="text-muted">Not recorded</small>
                            <?php endif; ?>
                        </td>
                        <td class="action-btns">
                            <a href="index.php?page=opd-visit&id=<?= $visit['id'] ?>" class="btn btn-sm btn-info" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="index.php?page=invoice-add&opd_id=<?= $visit['id'] ?>&patient_id=<?= $visit['patient_id'] ?>" class="btn btn-sm btn-success" title="Bill">
                                <i class="bi bi-receipt"></i>
                            </a>
                            <a href="index.php?page=opd&action=delete&id=<?= $visit['id'] ?>" class="btn btn-sm btn-danger btn-delete" title="Delete">
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
