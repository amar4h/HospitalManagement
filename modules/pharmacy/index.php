<?php
/**
 * Pharmacy - Medicine Inventory
 */
requireAuth();
requireRole(['admin', 'pharmacist', 'doctor']);

$storage = getStorage();

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $storage->delete('medicines', (int)$_GET['id']);
    setFlashMessage('success', 'Medicine deleted');
    redirect('index.php?page=pharmacy');
}

$medicines = $storage->getAll('medicines');

// Filter by category
$filterCategory = $_GET['category'] ?? '';
if (!empty($filterCategory)) {
    $medicines = array_filter($medicines, function($med) use ($filterCategory) {
        return $med['category'] === $filterCategory;
    });
}

// Get categories
$categories = array_unique(array_column($medicines, 'category'));
sort($categories);

// Low stock alert
$lowStockMedicines = array_filter($medicines, function($med) {
    return $med['stock'] <= $med['reorder_level'];
});
?>

<div class="page-header">
    <h1><i class="bi bi-capsule me-2"></i>Pharmacy</h1>
    <div class="quick-actions">
        <a href="index.php?page=medicine-add" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Add Medicine
        </a>
        <a href="index.php?page=dispense" class="btn btn-success">
            <i class="bi bi-bag-plus me-2"></i>Dispense
        </a>
    </div>
</div>

<?php if (!empty($lowStockMedicines)): ?>
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong>Low Stock Alert:</strong> <?= count($lowStockMedicines) ?> medicine(s) are running low on stock.
</div>
<?php endif; ?>

<!-- Stats -->
<div class="row mb-4">
    <div class="col-md-3 col-6 mb-3">
        <div class="stat-card bg-primary">
            <i class="bi bi-capsule stat-icon"></i>
            <div class="stat-value"><?= count($medicines) ?></div>
            <div class="stat-label">Total Medicines</div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="stat-card bg-success">
            <i class="bi bi-check-circle stat-icon"></i>
            <div class="stat-value"><?= count(array_filter($medicines, fn($m) => $m['stock'] > $m['reorder_level'])) ?></div>
            <div class="stat-label">In Stock</div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="stat-card bg-warning">
            <i class="bi bi-exclamation-triangle stat-icon"></i>
            <div class="stat-value"><?= count($lowStockMedicines) ?></div>
            <div class="stat-label">Low Stock</div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="stat-card bg-info">
            <i class="bi bi-tags stat-icon"></i>
            <div class="stat-value"><?= count($categories) ?></div>
            <div class="stat-label">Categories</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <span>Medicine Inventory</span>
            </div>
            <div class="col-md-6">
                <select class="form-select" onchange="location.href='index.php?page=pharmacy&category='+this.value">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= $filterCategory === $cat ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Medicine Name</th>
                        <th>Category</th>
                        <th class="hide-mobile">Manufacturer</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($medicines as $med): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($med['name']) ?></strong>
                            <small class="text-muted d-block"><?= $med['unit'] ?></small>
                        </td>
                        <td><?= htmlspecialchars($med['category']) ?></td>
                        <td class="hide-mobile"><?= htmlspecialchars($med['manufacturer']) ?></td>
                        <td><?= formatCurrency($med['price']) ?></td>
                        <td>
                            <?php if ($med['stock'] <= $med['reorder_level']): ?>
                            <span class="badge bg-danger"><?= $med['stock'] ?></span>
                            <?php else: ?>
                            <span class="badge bg-success"><?= $med['stock'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= getStatusBadge($med['status']) ?></td>
                        <td class="action-btns">
                            <a href="index.php?page=medicine-edit&id=<?= $med['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="index.php?page=pharmacy&action=delete&id=<?= $med['id'] ?>" class="btn btn-sm btn-danger btn-delete" title="Delete">
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
