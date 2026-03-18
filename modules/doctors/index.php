<?php
/**
 * Doctor List
 */
requireAuth();
requireRole(['admin', 'receptionist']);

$storage = getStorage();

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $storage->delete('doctors', $id);
    logActivity('doctor_delete', 'Deleted doctor ID: ' . $id);
    setFlashMessage('success', 'Doctor deleted successfully');
    redirect('index.php?page=doctors');
}

// Get all doctors
$doctors = $storage->getAll('doctors');
$departments = $storage->getAll('departments');

// Filter by department
$filterDept = $_GET['department'] ?? '';
if (!empty($filterDept)) {
    $doctors = array_filter($doctors, function($doc) use ($filterDept) {
        return $doc['department_id'] == $filterDept;
    });
}

// Search
$search = $_GET['search'] ?? '';
if (!empty($search)) {
    $doctors = $storage->search('doctors', ['name', 'specialization', 'phone'], $search);
}
?>

<div class="page-header">
    <h1><i class="bi bi-person-badge me-2"></i>Doctor Management</h1>
    <div class="quick-actions">
        <a href="index.php?page=doctor-add" class="btn btn-primary">
            <i class="bi bi-person-plus me-2"></i>Add New Doctor
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-4">
                <span>All Doctors (<?= count($doctors) ?>)</span>
            </div>
            <div class="col-md-4">
                <select class="form-select" onchange="location.href='index.php?page=doctors&department='+this.value">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $dept): ?>
                    <option value="<?= $dept['id'] ?>" <?= $filterDept == $dept['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dept['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <form method="GET" class="d-flex">
                    <input type="hidden" name="page" value="doctors">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search doctors..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($doctors)): ?>
        <div class="empty-state">
            <i class="bi bi-person-badge"></i>
            <h5>No Doctors Found</h5>
            <p class="text-muted">Start by adding a new doctor</p>
            <a href="index.php?page=doctor-add" class="btn btn-primary">
                <i class="bi bi-person-plus me-2"></i>Add Doctor
            </a>
        </div>
        <?php else: ?>
        <div class="row">
            <?php foreach ($doctors as $doctor): ?>
            <?php $dept = $storage->getById('departments', $doctor['department_id']); ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                            <?= strtoupper(substr($doctor['name'], 0, 1)) ?>
                        </div>
                        <h5 class="card-title mb-1"><?= htmlspecialchars($doctor['name']) ?></h5>
                        <p class="text-primary mb-2"><?= htmlspecialchars($doctor['specialization']) ?></p>
                        <p class="text-muted small mb-2">
                            <i class="bi bi-building me-1"></i><?= htmlspecialchars($dept['name'] ?? 'N/A') ?>
                        </p>
                        <p class="text-muted small mb-3">
                            <i class="bi bi-mortarboard me-1"></i><?= htmlspecialchars($doctor['qualification']) ?>
                        </p>

                        <div class="d-flex justify-content-center mb-3">
                            <span class="badge bg-success me-2">
                                <i class="bi bi-clock me-1"></i><?= $doctor['experience'] ?> yrs exp
                            </span>
                            <span class="badge bg-info">
                                <?= formatCurrency($doctor['consultation_fee']) ?>
                            </span>
                        </div>

                        <?= getStatusBadge($doctor['status']) ?>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="btn-group w-100">
                            <a href="index.php?page=doctor-view&id=<?= $doctor['id'] ?>" class="btn btn-outline-info btn-sm">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <a href="index.php?page=doctor-edit&id=<?= $doctor['id'] ?>" class="btn btn-outline-warning btn-sm">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <a href="index.php?page=doctors&action=delete&id=<?= $doctor['id'] ?>" class="btn btn-outline-danger btn-sm btn-delete">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
