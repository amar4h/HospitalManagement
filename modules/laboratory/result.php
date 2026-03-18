<?php
/**
 * Lab Result Entry/View
 */
requireAuth();
requireRole(['admin', 'lab_technician', 'doctor']);

$storage = getStorage();
$id = (int)($_GET['id'] ?? 0);
$order = $storage->getById('lab_orders', $id);

if (!$order) {
    setFlashMessage('error', 'Lab order not found');
    redirect('index.php?page=laboratory');
}

$patient = $storage->getById('patients', $order['patient_id']);
$test = $storage->getById('lab_tests', $order['test_id']);
$doctor = $storage->getById('doctors', $order['doctor_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'result' => sanitize($_POST['result'] ?? ''),
        'remarks' => sanitize($_POST['remarks'] ?? ''),
        'status' => 'result_available',
        'result_date' => date('Y-m-d H:i:s'),
        'result_by' => getCurrentUserId()
    ];

    $storage->update('lab_orders', $id, $data);
    logActivity('lab_result', 'Entered lab result for order #' . $id);
    setFlashMessage('success', 'Lab result saved successfully');
    redirect('index.php?page=lab-result&id=' . $id);
}
?>

<div class="page-header">
    <h1><i class="bi bi-file-medical me-2"></i>Lab Result</h1>
    <div class="quick-actions">
        <?php if ($order['status'] === 'result_available'): ?>
        <button onclick="printElement('printArea')" class="btn btn-info">
            <i class="bi bi-printer me-2"></i>Print
        </button>
        <?php endif; ?>
        <a href="index.php?page=laboratory" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div id="printArea">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <!-- Report Header -->
                <div class="card-header bg-primary text-white">
                    <div class="row">
                        <div class="col-8">
                            <h5 class="mb-0"><?= HOSPITAL_NAME ?></h5>
                            <small><?= HOSPITAL_ADDRESS ?></small>
                        </div>
                        <div class="col-4 text-end">
                            <strong>LAB REPORT</strong><br>
                            <small>#LAB-<?= str_pad($id, 6, '0', STR_PAD_LEFT) ?></small>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Patient Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Patient:</strong> <?= htmlspecialchars($patient['name']) ?></p>
                            <p class="mb-1"><strong>Patient ID:</strong> <?= $patient['patient_id'] ?></p>
                            <p class="mb-0"><strong>Age/Gender:</strong> <?= calculateAge($patient['dob']) ?> yrs / <?= $patient['gender'] ?></p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="mb-1"><strong>Order Date:</strong> <?= formatDate($order['created_at']) ?></p>
                            <?php if (!empty($order['result_date'])): ?>
                            <p class="mb-1"><strong>Report Date:</strong> <?= formatDate($order['result_date']) ?></p>
                            <?php endif; ?>
                            <p class="mb-0"><strong>Referred By:</strong> <?= htmlspecialchars($doctor['name'] ?? 'N/A') ?></p>
                        </div>
                    </div>

                    <hr>

                    <!-- Test Info -->
                    <div class="mb-4">
                        <h5 class="text-primary"><?= htmlspecialchars($test['name']) ?></h5>
                        <small class="text-muted">Category: <?= $test['category'] ?></small>
                    </div>

                    <?php if ($order['status'] === 'result_available'): ?>
                    <!-- Display Result -->
                    <div class="mb-4">
                        <h6>Result</h6>
                        <div class="p-3 bg-light rounded">
                            <?= nl2br(htmlspecialchars($order['result'])) ?>
                        </div>
                    </div>

                    <?php if (!empty($test['normal_range'])): ?>
                    <div class="mb-4">
                        <h6>Normal Range</h6>
                        <p class="mb-0"><?= htmlspecialchars($test['normal_range']) ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($order['remarks'])): ?>
                    <div class="mb-4">
                        <h6>Remarks</h6>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($order['remarks'])) ?></p>
                    </div>
                    <?php endif; ?>

                    <div class="mt-5 pt-4 border-top no-print">
                        <small class="text-muted">This is a computer generated report</small>
                    </div>

                    <?php else: ?>
                    <!-- Enter Result Form -->
                    <form method="POST" action="">
                        <?= csrfField() ?>

                        <div class="mb-3">
                            <label class="form-label">Result <span class="text-danger">*</span></label>
                            <textarea name="result" class="form-control" rows="5" required placeholder="Enter test result..."></textarea>
                        </div>

                        <?php if (!empty($test['normal_range'])): ?>
                        <div class="alert alert-info">
                            <strong>Normal Range:</strong> <?= htmlspecialchars($test['normal_range']) ?>
                        </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="2" placeholder="Any additional remarks..."></textarea>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="index.php?page=laboratory" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg me-2"></i>Save Result
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4 no-print">
            <!-- Status -->
            <div class="card">
                <div class="card-header">Status</div>
                <div class="card-body text-center">
                    <?= getStatusBadge($order['status']) ?>
                    <p class="mt-2 mb-0 small text-muted">
                        <?php if ($order['status'] === 'pending'): ?>
                        Sample not yet collected
                        <?php elseif ($order['status'] === 'sample_collected'): ?>
                        Awaiting result entry
                        <?php else: ?>
                        Result available
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <!-- Test Details -->
            <div class="card">
                <div class="card-header">Test Details</div>
                <div class="card-body">
                    <p class="mb-2"><strong>Test:</strong> <?= htmlspecialchars($test['name']) ?></p>
                    <p class="mb-2"><strong>Category:</strong> <?= $test['category'] ?></p>
                    <p class="mb-0"><strong>Price:</strong> <?= formatCurrency($test['price']) ?></p>
                </div>
            </div>

            <!-- Patient -->
            <div class="card">
                <div class="card-header">Patient</div>
                <div class="card-body">
                    <p class="mb-1"><strong><?= htmlspecialchars($patient['name']) ?></strong></p>
                    <p class="mb-1"><?= $patient['patient_id'] ?></p>
                    <p class="mb-0"><i class="bi bi-telephone me-2"></i><?= $patient['phone'] ?></p>
                    <a href="index.php?page=patient-view&id=<?= $patient['id'] ?>" class="btn btn-sm btn-outline-primary mt-2">
                        View Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
