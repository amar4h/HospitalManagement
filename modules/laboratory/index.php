<?php
/**
 * Laboratory - Lab Orders
 */
requireAuth();
requireRole(['admin', 'lab_technician', 'doctor']);

$storage = getStorage();

// Handle status update
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action === 'collect') {
        $storage->update('lab_orders', $id, ['status' => 'sample_collected', 'collection_date' => date('Y-m-d H:i:s')]);
        setFlashMessage('success', 'Sample marked as collected');
    } elseif ($action === 'delete') {
        $storage->delete('lab_orders', $id);
        setFlashMessage('success', 'Lab order deleted');
    }
    redirect('index.php?page=laboratory');
}

$labOrders = array_reverse($storage->getAll('lab_orders'));

// Filter by status
$filterStatus = $_GET['status'] ?? '';
if (!empty($filterStatus)) {
    $labOrders = array_filter($labOrders, function($order) use ($filterStatus) {
        return $order['status'] === $filterStatus;
    });
}
?>

<div class="page-header">
    <h1><i class="bi bi-droplet me-2"></i>Laboratory</h1>
    <div class="quick-actions">
        <a href="index.php?page=lab-order-add" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>New Lab Order
        </a>
    </div>
</div>

<!-- Status Tabs -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?= $filterStatus === '' ? 'active' : '' ?>" href="index.php?page=laboratory">All</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $filterStatus === 'pending' ? 'active' : '' ?>" href="index.php?page=laboratory&status=pending">Pending</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $filterStatus === 'sample_collected' ? 'active' : '' ?>" href="index.php?page=laboratory&status=sample_collected">Sample Collected</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $filterStatus === 'result_available' ? 'active' : '' ?>" href="index.php?page=laboratory&status=result_available">Results Ready</a>
    </li>
</ul>

<div class="card">
    <div class="card-header">
        Lab Orders (<?= count($labOrders) ?>)
    </div>
    <div class="card-body">
        <?php if (empty($labOrders)): ?>
        <div class="empty-state">
            <i class="bi bi-droplet"></i>
            <h5>No Lab Orders</h5>
            <a href="index.php?page=lab-order-add" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>New Lab Order
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Patient</th>
                        <th>Test</th>
                        <th class="hide-mobile">Ordered By</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($labOrders as $order): ?>
                    <?php
                    $patient = $storage->getById('patients', $order['patient_id']);
                    $test = $storage->getById('lab_tests', $order['test_id']);
                    $doctor = $storage->getById('doctors', $order['doctor_id']);
                    ?>
                    <tr>
                        <td><strong>LAB-<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                        <td>
                            <a href="index.php?page=patient-view&id=<?= $order['patient_id'] ?>">
                                <?= htmlspecialchars($patient['name'] ?? 'N/A') ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($test['name'] ?? 'N/A') ?></td>
                        <td class="hide-mobile"><?= htmlspecialchars($doctor['name'] ?? 'N/A') ?></td>
                        <td><?= formatDate($order['created_at']) ?></td>
                        <td><?= getStatusBadge($order['status']) ?></td>
                        <td class="action-btns">
                            <?php if ($order['status'] === 'pending'): ?>
                            <a href="index.php?page=laboratory&action=collect&id=<?= $order['id'] ?>" class="btn btn-sm btn-info" title="Mark Sample Collected">
                                <i class="bi bi-check"></i>
                            </a>
                            <?php endif; ?>

                            <?php if ($order['status'] === 'sample_collected'): ?>
                            <a href="index.php?page=lab-result&id=<?= $order['id'] ?>" class="btn btn-sm btn-success" title="Enter Result">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php endif; ?>

                            <?php if ($order['status'] === 'result_available'): ?>
                            <a href="index.php?page=lab-result&id=<?= $order['id'] ?>" class="btn btn-sm btn-info" title="View Result">
                                <i class="bi bi-eye"></i>
                            </a>
                            <?php endif; ?>

                            <a href="index.php?page=laboratory&action=delete&id=<?= $order['id'] ?>" class="btn btn-sm btn-danger btn-delete">
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
