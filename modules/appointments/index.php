<?php
/**
 * Appointments List
 */
requireAuth();
requireRole(['admin', 'doctor', 'nurse', 'receptionist']);

$storage = getStorage();

// Handle status update
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action === 'confirm') {
        $storage->update('appointments', $id, ['status' => 'confirmed']);
        setFlashMessage('success', 'Appointment confirmed');
    } elseif ($action === 'cancel') {
        $storage->update('appointments', $id, ['status' => 'cancelled']);
        setFlashMessage('success', 'Appointment cancelled');
    } elseif ($action === 'complete') {
        $storage->update('appointments', $id, ['status' => 'completed']);
        setFlashMessage('success', 'Appointment marked as completed');
    } elseif ($action === 'delete') {
        $storage->delete('appointments', $id);
        setFlashMessage('success', 'Appointment deleted');
    }
    redirect('index.php?page=appointments');
}

// Get appointments
$appointments = $storage->getAll('appointments');

// Filter by date
$filterDate = $_GET['date'] ?? date('Y-m-d');
$appointments = array_filter($appointments, function($apt) use ($filterDate) {
    return $apt['date'] === $filterDate;
});

// Sort by time
usort($appointments, function($a, $b) {
    return strcmp($a['time'], $b['time']);
});

// Filter by status
$filterStatus = $_GET['status'] ?? '';
if (!empty($filterStatus)) {
    $appointments = array_filter($appointments, function($apt) use ($filterStatus) {
        return $apt['status'] === $filterStatus;
    });
}

// Filter by doctor (for doctor role)
if (getCurrentUserRole() === 'doctor') {
    $doctorId = $_SESSION['user']['doctor_id'] ?? 0;
    $appointments = array_filter($appointments, function($apt) use ($doctorId) {
        return $apt['doctor_id'] == $doctorId;
    });
}
?>

<div class="page-header">
    <h1><i class="bi bi-calendar-check me-2"></i>Appointments</h1>
    <div class="quick-actions">
        <a href="index.php?page=appointment-add" class="btn btn-primary">
            <i class="bi bi-calendar-plus me-2"></i>New Appointment
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row align-items-end">
            <input type="hidden" name="page" value="appointments">
            <div class="col-md-3">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control" value="<?= $filterDate ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="confirmed" <?= $filterStatus === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                    <option value="completed" <?= $filterStatus === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= $filterStatus === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-filter me-2"></i>Filter
                </button>
                <a href="index.php?page=appointments" class="btn btn-outline-secondary">Reset</a>
            </div>
            <div class="col-md-3 text-end">
                <div class="btn-group">
                    <a href="index.php?page=appointments&date=<?= date('Y-m-d', strtotime($filterDate . ' -1 day')) ?>" class="btn btn-outline-primary">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                    <a href="index.php?page=appointments&date=<?= date('Y-m-d') ?>" class="btn btn-outline-primary">Today</a>
                    <a href="index.php?page=appointments&date=<?= date('Y-m-d', strtotime($filterDate . ' +1 day')) ?>" class="btn btn-outline-primary">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span><i class="bi bi-calendar me-2"></i><?= formatDate($filterDate) ?> - <?= count($appointments) ?> Appointments</span>
    </div>
    <div class="card-body">
        <?php if (empty($appointments)): ?>
        <div class="empty-state">
            <i class="bi bi-calendar-x"></i>
            <h5>No Appointments</h5>
            <p class="text-muted">No appointments found for this date</p>
            <a href="index.php?page=appointment-add" class="btn btn-primary">
                <i class="bi bi-calendar-plus me-2"></i>Schedule Appointment
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $apt): ?>
                    <?php
                    $patient = $storage->getById('patients', $apt['patient_id']);
                    $doctor = $storage->getById('doctors', $apt['doctor_id']);
                    $dept = $doctor ? $storage->getById('departments', $doctor['department_id']) : null;
                    ?>
                    <tr>
                        <td>
                            <strong><?= date('h:i A', strtotime($apt['time'])) ?></strong>
                        </td>
                        <td>
                            <a href="index.php?page=patient-view&id=<?= $apt['patient_id'] ?>">
                                <strong><?= htmlspecialchars($patient['name'] ?? 'N/A') ?></strong>
                            </a>
                            <br><small class="text-muted"><?= $patient['patient_id'] ?? '' ?></small>
                        </td>
                        <td>
                            <a href="index.php?page=doctor-view&id=<?= $apt['doctor_id'] ?>">
                                <?= htmlspecialchars($doctor['name'] ?? 'N/A') ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($dept['name'] ?? 'N/A') ?></td>
                        <td><?= getStatusBadge($apt['status']) ?></td>
                        <td class="action-btns">
                            <?php if ($apt['status'] === 'pending'): ?>
                            <a href="index.php?page=appointments&action=confirm&id=<?= $apt['id'] ?>" class="btn btn-sm btn-success" title="Confirm">
                                <i class="bi bi-check-lg"></i>
                            </a>
                            <?php endif; ?>

                            <?php if ($apt['status'] === 'confirmed'): ?>
                            <a href="index.php?page=opd-add&appointment_id=<?= $apt['id'] ?>&patient_id=<?= $apt['patient_id'] ?>&doctor_id=<?= $apt['doctor_id'] ?>" class="btn btn-sm btn-primary" title="Start OPD">
                                <i class="bi bi-clipboard2-plus"></i>
                            </a>
                            <a href="index.php?page=appointments&action=complete&id=<?= $apt['id'] ?>" class="btn btn-sm btn-success" title="Complete">
                                <i class="bi bi-check-circle"></i>
                            </a>
                            <?php endif; ?>

                            <?php if ($apt['status'] !== 'cancelled' && $apt['status'] !== 'completed'): ?>
                            <a href="index.php?page=appointment-edit&id=<?= $apt['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="index.php?page=appointments&action=cancel&id=<?= $apt['id'] ?>" class="btn btn-sm btn-danger" title="Cancel">
                                <i class="bi bi-x-lg"></i>
                            </a>
                            <?php endif; ?>

                            <a href="index.php?page=appointments&action=delete&id=<?= $apt['id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" title="Delete">
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
