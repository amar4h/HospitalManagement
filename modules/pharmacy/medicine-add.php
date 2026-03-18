<?php
/**
 * Add Medicine
 */
requireAuth();
requireRole(['admin', 'pharmacist']);

$storage = getStorage();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => sanitize($_POST['name'] ?? ''),
        'category' => sanitize($_POST['category'] ?? ''),
        'manufacturer' => sanitize($_POST['manufacturer'] ?? ''),
        'unit' => sanitize($_POST['unit'] ?? 'Tablet'),
        'price' => (float)($_POST['price'] ?? 0),
        'stock' => (int)($_POST['stock'] ?? 0),
        'reorder_level' => (int)($_POST['reorder_level'] ?? 10),
        'description' => sanitize($_POST['description'] ?? ''),
        'status' => 'active'
    ];

    if (empty($data['name']) || empty($data['category'])) {
        setFlashMessage('error', 'Name and Category are required');
    } else {
        $storage->insert('medicines', $data);
        logActivity('medicine_add', 'Added medicine: ' . $data['name']);
        setFlashMessage('success', 'Medicine added successfully');
        redirect('index.php?page=pharmacy');
    }
}

$categories = ['Analgesic', 'Antibiotic', 'Antacid', 'Antidiabetic', 'Antihypertensive', 'Antihistamine', 'NSAID', 'Vitamin', 'Lipid Lowering', 'Other'];
$units = ['Tablet', 'Capsule', 'Syrup', 'Injection', 'Drops', 'Cream', 'Ointment', 'Inhaler', 'Sachet'];
?>

<div class="page-header">
    <h1><i class="bi bi-plus-lg me-2"></i>Add Medicine</h1>
    <div>
        <a href="index.php?page=pharmacy" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="" class="needs-validation" novalidate>
                    <?= csrfField() ?>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Medicine Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g., Paracetamol 500mg" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Unit</label>
                            <select name="unit" class="form-select">
                                <?php foreach ($units as $unit): ?>
                                <option value="<?= $unit ?>"><?= $unit ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat ?>"><?= $cat ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Manufacturer</label>
                            <input type="text" name="manufacturer" class="form-control" placeholder="Company name">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text"><?= CURRENCY_SYMBOL ?></span>
                                <input type="number" name="price" class="form-control" min="0" step="0.01" value="0">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Initial Stock</label>
                            <input type="number" name="stock" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Reorder Level</label>
                            <input type="number" name="reorder_level" class="form-control" min="0" value="10">
                            <small class="text-muted">Alert when stock falls below this</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Additional information..."></textarea>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php?page=pharmacy" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i>Add Medicine
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
