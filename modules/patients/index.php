<?php
/**
 * Patient List
 */
requireAuth();
requireRole(['admin', 'doctor', 'nurse', 'receptionist']);

$storage = getStorage();

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $storage->delete('patients', $id);
    logActivity('patient_delete', 'Deleted patient ID: ' . $id);
    setFlashMessage('success', 'Patient deleted successfully');
    redirect('index.php?page=patients');
}

// Get all patients
$patients = array_reverse($storage->getAll('patients'));

// Search filter
$search = $_GET['search'] ?? '';
if (!empty($search)) {
    $patients = $storage->search('patients', ['name', 'patient_id', 'phone', 'email'], $search);
}
?>

<div class="page-header">
    <h1><i class="bi bi-people me-2"></i>Patient Management</h1>
    <div class="quick-actions">
        <a href="index.php?page=patient-add" class="btn btn-primary">
            <i class="bi bi-person-plus me-2"></i>Add New Patient
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <span>All Patients (<?= count($patients) ?>)</span>
            </div>
            <div class="col-md-6">
                <form method="GET" class="d-flex">
                    <input type="hidden" name="page" value="patients">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search patients..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-search"></i>
                        </button>
                        <?php if (!empty($search)): ?>
                        <a href="index.php?page=patients" class="btn btn-outline-secondary">
                            <i class="bi bi-x"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($patients)): ?>
        <div class="empty-state">
            <i class="bi bi-people"></i>
            <h5>No Patients Found</h5>
            <p class="text-muted">Start by adding a new patient</p>
            <a href="index.php?page=patient-add" class="btn btn-primary">
                <i class="bi bi-person-plus me-2"></i>Add Patient
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Patient ID</th>
                        <th>Name</th>
                        <th>Age/Gender</th>
                        <th>Phone</th>
                        <th>Blood Group</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($patients as $patient): ?>
                    <tr>
                        <td>
                            <a href="index.php?page=patient-view&id=<?= $patient['id'] ?>" class="fw-bold">
                                <?= htmlspecialchars($patient['patient_id']) ?>
                            </a>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                    <?= strtoupper(substr($patient['name'], 0, 1)) ?>
                                </div>
                                <?= htmlspecialchars($patient['name']) ?>
                            </div>
                        </td>
                        <td>
                            <?= calculateAge($patient['dob']) ?> yrs / <?= $patient['gender'] ?>
                        </td>
                        <td><?= htmlspecialchars($patient['phone']) ?></td>
                        <td>
                            <span class="badge bg-danger"><?= $patient['blood_group'] ?? '-' ?></span>
                        </td>
                        <td><?= formatDate($patient['created_at']) ?></td>
                        <td class="action-btns">
                            <a href="index.php?page=patient-view&id=<?= $patient['id'] ?>" class="btn btn-sm btn-info" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="index.php?page=patient-edit&id=<?= $patient['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="index.php?page=patients&action=delete&id=<?= $patient['id'] ?>" class="btn btn-sm btn-danger btn-delete" title="Delete">
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
