<?php
/**
 * System Settings
 */
requireAuth();
requireRole(['admin']);

$storage = getStorage();
$departments = $storage->getAll('departments');

// Handle department actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'delete_dept' && isset($_GET['id'])) {
        $storage->delete('departments', (int)$_GET['id']);
        setFlashMessage('success', 'Department deleted');
        redirect('index.php?page=settings');
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? '';

    if ($formType === 'department') {
        $deptData = [
            'name' => sanitize($_POST['dept_name'] ?? ''),
            'description' => sanitize($_POST['dept_description'] ?? ''),
            'status' => 'active'
        ];

        if (!empty($deptData['name'])) {
            $storage->insert('departments', $deptData);
            logActivity('department_add', 'Added department: ' . $deptData['name']);
            setFlashMessage('success', 'Department added successfully');
        }
        redirect('index.php?page=settings');
    }
}
?>

<div class="page-header">
    <h1><i class="bi bi-gear me-2"></i>Settings</h1>
</div>

<div class="row">
    <!-- Hospital Info -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-hospital me-2"></i>Hospital Information
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Hospital Name</label>
                    <input type="text" class="form-control" value="<?= HOSPITAL_NAME ?>" readonly>
                    <small class="text-muted">Edit in config/config.php</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" class="form-control" value="<?= HOSPITAL_ADDRESS ?>" readonly>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" value="<?= HOSPITAL_PHONE ?>" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="text" class="form-control" value="<?= HOSPITAL_EMAIL ?>" readonly>
                    </div>
                </div>

                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    To modify hospital settings, edit the <code>config/config.php</code> file.
                </div>
            </div>
        </div>
    </div>

    <!-- System Info -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i>System Information
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Application</strong></td>
                        <td><?= APP_NAME ?></td>
                    </tr>
                    <tr>
                        <td><strong>Version</strong></td>
                        <td><?= APP_VERSION ?></td>
                    </tr>
                    <tr>
                        <td><strong>PHP Version</strong></td>
                        <td><?= PHP_VERSION ?></td>
                    </tr>
                    <tr>
                        <td><strong>Storage Type</strong></td>
                        <td>Local JSON Files</td>
                    </tr>
                    <tr>
                        <td><strong>Data Directory</strong></td>
                        <td><code>data/</code></td>
                    </tr>
                    <tr>
                        <td><strong>Timezone</strong></td>
                        <td><?= date_default_timezone_get() ?></td>
                    </tr>
                    <tr>
                        <td><strong>Currency</strong></td>
                        <td><?= CURRENCY_SYMBOL ?> (<?= CURRENCY_CODE ?>)</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Departments -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-building me-2"></i>Departments</span>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addDeptModal">
            <i class="bi bi-plus me-1"></i>Add Department
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Department Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departments as $dept): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($dept['name']) ?></strong></td>
                        <td><?= htmlspecialchars($dept['description'] ?? '') ?></td>
                        <td><?= getStatusBadge($dept['status'] ?? 'active') ?></td>
                        <td>
                            <a href="index.php?page=settings&action=delete_dept&id=<?= $dept['id'] ?>" class="btn btn-sm btn-danger btn-delete">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Data Statistics -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-database me-2"></i>Data Statistics
    </div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-2 col-4 mb-3">
                <h4 class="text-primary"><?= $storage->count('patients') ?></h4>
                <small class="text-muted">Patients</small>
            </div>
            <div class="col-md-2 col-4 mb-3">
                <h4 class="text-success"><?= $storage->count('doctors') ?></h4>
                <small class="text-muted">Doctors</small>
            </div>
            <div class="col-md-2 col-4 mb-3">
                <h4 class="text-info"><?= $storage->count('appointments') ?></h4>
                <small class="text-muted">Appointments</small>
            </div>
            <div class="col-md-2 col-4 mb-3">
                <h4 class="text-warning"><?= $storage->count('opd_visits') ?></h4>
                <small class="text-muted">OPD Visits</small>
            </div>
            <div class="col-md-2 col-4 mb-3">
                <h4 class="text-danger"><?= $storage->count('ipd_admissions') ?></h4>
                <small class="text-muted">IPD Admissions</small>
            </div>
            <div class="col-md-2 col-4 mb-3">
                <h4 class="text-secondary"><?= $storage->count('invoices') ?></h4>
                <small class="text-muted">Invoices</small>
            </div>
        </div>
    </div>
</div>

<!-- Add Department Modal -->
<div class="modal fade" id="addDeptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <?= csrfField() ?>
                <input type="hidden" name="form_type" value="department">

                <div class="modal-header">
                    <h5 class="modal-title">Add Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Department Name <span class="text-danger">*</span></label>
                        <input type="text" name="dept_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="dept_description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Department</button>
                </div>
            </form>
        </div>
    </div>
</div>
