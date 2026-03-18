<?php
/**
 * Surgery Management
 */
requireAuth();
requireRole(['admin', 'doctor', 'nurse']);

$storage = getStorage();

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $storage->delete('surgeries', (int)$_GET['id']);
    setFlashMessage('success', 'Surgery record deleted');
    redirect('index.php?page=surgery');
}

$surgeries = array_reverse($storage->getAll('surgeries'));
?>

<div class="page-header">
    <h1><i class="bi bi-heart-pulse me-2"></i>Surgery Management</h1>
    <div class="quick-actions">
        <a href="index.php?page=surgery-add" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Schedule Surgery
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        Surgeries (<?= count($surgeries) ?>)
    </div>
    <div class="card-body">
        <?php if (empty($surgeries)): ?>
        <div class="empty-state">
            <i class="bi bi-heart-pulse"></i>
            <h5>No Surgeries Scheduled</h5>
            <a href="index.php?page=surgery-add" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>Schedule Surgery
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>Surgery</th>
                        <th>Surgeon</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($surgeries as $surgery): ?>
                    <?php
                    $patient = $storage->getById('patients', $surgery['patient_id']);
                    $doctor = $storage->getById('doctors', $surgery['doctor_id']);
                    ?>
                    <tr>
                        <td><?= formatDate($surgery['date']) ?> <?= $surgery['time'] ?? '' ?></td>
                        <td>
                            <a href="index.php?page=patient-view&id=<?= $surgery['patient_id'] ?>">
                                <?= htmlspecialchars($patient['name'] ?? 'N/A') ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($surgery['surgery_name']) ?></td>
                        <td><?= htmlspecialchars($doctor['name'] ?? 'N/A') ?></td>
                        <td><?= getStatusBadge($surgery['status']) ?></td>
                        <td class="action-btns">
                            <a href="index.php?page=surgery-edit&id=<?= $surgery['id'] ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="index.php?page=surgery&action=delete&id=<?= $surgery['id'] ?>" class="btn btn-sm btn-danger btn-delete">
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
