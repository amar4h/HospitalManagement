<?php
/**
 * Billing - Invoices List
 */
requireAuth();
requireRole(['admin', 'accountant', 'receptionist']);

$storage = getStorage();

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $storage->delete('invoices', (int)$_GET['id']);
    setFlashMessage('success', 'Invoice deleted');
    redirect('index.php?page=billing');
}

$invoices = array_reverse($storage->getAll('invoices'));

// Filter by status
$filterStatus = $_GET['status'] ?? '';
if (!empty($filterStatus)) {
    $invoices = array_filter($invoices, function($inv) use ($filterStatus) {
        return $inv['payment_status'] === $filterStatus;
    });
}

// Calculate totals
$totalRevenue = array_sum(array_column($invoices, 'total_amount'));
$paidAmount = array_sum(array_column(array_filter($invoices, fn($i) => $i['payment_status'] === 'paid'), 'total_amount'));
$pendingAmount = $totalRevenue - $paidAmount;
?>

<div class="page-header">
    <h1><i class="bi bi-receipt me-2"></i>Billing</h1>
    <div class="quick-actions">
        <a href="index.php?page=invoice-add" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>New Invoice
        </a>
    </div>
</div>

<!-- Stats -->
<div class="row mb-4">
    <div class="col-md-4 col-6 mb-3">
        <div class="stat-card bg-primary">
            <i class="bi bi-currency-dollar stat-icon"></i>
            <div class="stat-value"><?= formatCurrency($totalRevenue) ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>
    <div class="col-md-4 col-6 mb-3">
        <div class="stat-card bg-success">
            <i class="bi bi-check-circle stat-icon"></i>
            <div class="stat-value"><?= formatCurrency($paidAmount) ?></div>
            <div class="stat-label">Collected</div>
        </div>
    </div>
    <div class="col-md-4 col-6 mb-3">
        <div class="stat-card bg-warning">
            <i class="bi bi-clock stat-icon"></i>
            <div class="stat-value"><?= formatCurrency($pendingAmount) ?></div>
            <div class="stat-label">Pending</div>
        </div>
    </div>
</div>

<!-- Status Tabs -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?= $filterStatus === '' ? 'active' : '' ?>" href="index.php?page=billing">All</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $filterStatus === 'unpaid' ? 'active' : '' ?>" href="index.php?page=billing&status=unpaid">Unpaid</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $filterStatus === 'partial' ? 'active' : '' ?>" href="index.php?page=billing&status=partial">Partial</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $filterStatus === 'paid' ? 'active' : '' ?>" href="index.php?page=billing&status=paid">Paid</a>
    </li>
</ul>

<div class="card">
    <div class="card-header">
        Invoices (<?= count($invoices) ?>)
    </div>
    <div class="card-body">
        <?php if (empty($invoices)): ?>
        <div class="empty-state">
            <i class="bi bi-receipt"></i>
            <h5>No Invoices</h5>
            <a href="index.php?page=invoice-add" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>Create Invoice
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Patient</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Paid</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $inv): ?>
                    <?php $patient = $storage->getById('patients', $inv['patient_id']); ?>
                    <tr>
                        <td><strong><?= $inv['invoice_number'] ?></strong></td>
                        <td>
                            <a href="index.php?page=patient-view&id=<?= $inv['patient_id'] ?>">
                                <?= htmlspecialchars($patient['name'] ?? 'N/A') ?>
                            </a>
                        </td>
                        <td><?= formatDate($inv['created_at']) ?></td>
                        <td><strong><?= formatCurrency($inv['total_amount']) ?></strong></td>
                        <td><?= formatCurrency($inv['paid_amount'] ?? 0) ?></td>
                        <td><?= getStatusBadge($inv['payment_status']) ?></td>
                        <td class="action-btns">
                            <a href="index.php?page=invoice-view&id=<?= $inv['id'] ?>" class="btn btn-sm btn-info" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <?php if ($inv['payment_status'] !== 'paid'): ?>
                            <a href="index.php?page=payment&id=<?= $inv['id'] ?>" class="btn btn-sm btn-success" title="Payment">
                                <i class="bi bi-cash"></i>
                            </a>
                            <?php endif; ?>
                            <a href="index.php?page=billing&action=delete&id=<?= $inv['id'] ?>" class="btn btn-sm btn-danger btn-delete">
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
