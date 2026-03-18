<?php
/**
 * User Management
 */
requireAuth();
requireRole(['admin']);

$storage = getStorage();

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action === 'delete') {
        $result = deleteUser($id);
        setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
    } elseif ($action === 'activate') {
        $storage->update('users', $id, ['status' => 'active']);
        setFlashMessage('success', 'User activated');
    } elseif ($action === 'deactivate') {
        $storage->update('users', $id, ['status' => 'inactive']);
        setFlashMessage('success', 'User deactivated');
    }
    redirect('index.php?page=users');
}

$users = $storage->getAll('users');
$roles = $storage->getAll('roles');
?>

<div class="page-header">
    <h1><i class="bi bi-person-gear me-2"></i>User Management</h1>
    <div class="quick-actions">
        <a href="index.php?page=user-add" class="btn btn-primary">
            <i class="bi bi-person-plus me-2"></i>Add User
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        All Users (<?= count($users) ?>)
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th class="hide-mobile">Email</th>
                        <th class="hide-mobile">Last Login</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                </div>
                                <?= htmlspecialchars($user['name']) ?>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><span class="badge bg-secondary"><?= getRoleDisplayName($user['role']) ?></span></td>
                        <td class="hide-mobile"><?= htmlspecialchars($user['email']) ?></td>
                        <td class="hide-mobile"><?= !empty($user['last_login']) ? formatDateTime($user['last_login']) : 'Never' ?></td>
                        <td><?= getStatusBadge($user['status']) ?></td>
                        <td class="action-btns">
                            <a href="index.php?page=user-edit&id=<?= $user['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php if ($user['status'] === 'active'): ?>
                            <a href="index.php?page=users&action=deactivate&id=<?= $user['id'] ?>" class="btn btn-sm btn-secondary" title="Deactivate">
                                <i class="bi bi-pause"></i>
                            </a>
                            <?php else: ?>
                            <a href="index.php?page=users&action=activate&id=<?= $user['id'] ?>" class="btn btn-sm btn-success" title="Activate">
                                <i class="bi bi-play"></i>
                            </a>
                            <?php endif; ?>
                            <?php if ($user['id'] != getCurrentUserId()): ?>
                            <a href="index.php?page=users&action=delete&id=<?= $user['id'] ?>" class="btn btn-sm btn-danger btn-delete" title="Delete">
                                <i class="bi bi-trash"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Roles Reference -->
<div class="card mt-4">
    <div class="card-header">
        <i class="bi bi-shield-check me-2"></i>User Roles & Permissions
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Description</th>
                        <th>Access</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="badge bg-danger">Administrator</span></td>
                        <td>Full system access</td>
                        <td>All modules, settings, user management</td>
                    </tr>
                    <tr>
                        <td><span class="badge bg-primary">Doctor</span></td>
                        <td>Medical staff</td>
                        <td>Patients, Appointments, OPD, IPD, Lab results, Pharmacy view</td>
                    </tr>
                    <tr>
                        <td><span class="badge bg-info">Nurse</span></td>
                        <td>Patient care</td>
                        <td>Patients (view), Vitals, IPD, Appointments</td>
                    </tr>
                    <tr>
                        <td><span class="badge bg-success">Receptionist</span></td>
                        <td>Front desk</td>
                        <td>Patients, Appointments, Doctors, Billing</td>
                    </tr>
                    <tr>
                        <td><span class="badge bg-warning">Pharmacist</span></td>
                        <td>Pharmacy management</td>
                        <td>Pharmacy, Prescriptions, Medicine dispensing</td>
                    </tr>
                    <tr>
                        <td><span class="badge bg-secondary">Lab Technician</span></td>
                        <td>Laboratory operations</td>
                        <td>Lab orders, Result entry, Reports</td>
                    </tr>
                    <tr>
                        <td><span class="badge bg-dark">Accountant</span></td>
                        <td>Financial management</td>
                        <td>Billing, Payments, Financial reports</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
