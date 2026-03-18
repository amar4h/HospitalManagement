<?php
/**
 * Dispense Medicine
 */
requireAuth();
requireRole(['admin', 'pharmacist']);

$storage = getStorage();
$patients = $storage->getAll('patients');
$medicines = $storage->getAll('medicines', ['status' => 'active']);

$selectedPatient = $_GET['patient_id'] ?? '';
$opdId = $_GET['opd_id'] ?? '';

// Get prescriptions if OPD visit linked
$prescriptions = [];
if ($opdId) {
    $prescriptions = array_filter($storage->getAll('prescriptions'), function($p) use ($opdId) {
        return $p['opd_visit_id'] == $opdId;
    });
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patientId = (int)($_POST['patient_id'] ?? 0);
    $medicineIds = $_POST['medicines'] ?? [];
    $quantities = $_POST['quantities'] ?? [];

    if (empty($patientId) || empty($medicineIds)) {
        setFlashMessage('error', 'Patient and at least one medicine are required');
    } else {
        $totalAmount = 0;
        $dispensed = [];

        foreach ($medicineIds as $index => $medId) {
            if (!empty($medId) && !empty($quantities[$index])) {
                $medicine = $storage->getById('medicines', $medId);
                $qty = (int)$quantities[$index];

                if ($medicine && $qty > 0 && $medicine['stock'] >= $qty) {
                    // Update stock
                    $storage->update('medicines', $medId, [
                        'stock' => $medicine['stock'] - $qty
                    ]);

                    // Record dispense
                    $storage->insert('medicine_dispenses', [
                        'patient_id' => $patientId,
                        'medicine_id' => $medId,
                        'quantity' => $qty,
                        'price' => $medicine['price'],
                        'total' => $medicine['price'] * $qty,
                        'dispensed_by' => getCurrentUserId(),
                        'opd_id' => $opdId ?: null
                    ]);

                    $totalAmount += $medicine['price'] * $qty;
                    $dispensed[] = $medicine['name'];
                }
            }
        }

        if (!empty($dispensed)) {
            $patient = $storage->getById('patients', $patientId);
            logActivity('medicine_dispense', 'Dispensed medicines to ' . $patient['name']);
            setFlashMessage('success', 'Medicines dispensed successfully. Total: ' . formatCurrency($totalAmount));
            redirect('index.php?page=pharmacy');
        } else {
            setFlashMessage('error', 'No medicines could be dispensed. Check stock availability.');
        }
    }
}
?>

<div class="page-header">
    <h1><i class="bi bi-bag-plus me-2"></i>Dispense Medicine</h1>
    <div>
        <a href="index.php?page=pharmacy" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<form method="POST" action="" class="needs-validation" novalidate>
    <?= csrfField() ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-person me-2"></i>Patient
                </div>
                <div class="card-body">
                    <select name="patient_id" class="form-select" required>
                        <option value="">Select Patient</option>
                        <?php foreach ($patients as $patient): ?>
                        <option value="<?= $patient['id'] ?>" <?= $selectedPatient == $patient['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($patient['name']) ?> (<?= $patient['patient_id'] ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-capsule me-2"></i>Medicines</span>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addMedicineRow()">
                        <i class="bi bi-plus me-1"></i>Add
                    </button>
                </div>
                <div class="card-body">
                    <div id="medicineRows">
                        <?php if (!empty($prescriptions)): ?>
                            <?php foreach ($prescriptions as $rx): ?>
                            <?php $med = $storage->getById('medicines', $rx['medicine_id']); ?>
                            <div class="medicine-row row mb-3">
                                <div class="col-md-6 mb-2">
                                    <select name="medicines[]" class="form-select">
                                        <option value="">Select Medicine</option>
                                        <?php foreach ($medicines as $m): ?>
                                        <option value="<?= $m['id'] ?>" data-stock="<?= $m['stock'] ?>" data-price="<?= $m['price'] ?>"
                                            <?= $m['id'] == $rx['medicine_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($m['name']) ?> (Stock: <?= $m['stock'] ?>) - <?= formatCurrency($m['price']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <input type="number" name="quantities[]" class="form-control" placeholder="Quantity" min="1" value="1">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <button type="button" class="btn btn-outline-danger" onclick="removeMedicineRow(this)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <div class="medicine-row row mb-3">
                            <div class="col-md-6 mb-2">
                                <select name="medicines[]" class="form-select">
                                    <option value="">Select Medicine</option>
                                    <?php foreach ($medicines as $med): ?>
                                    <option value="<?= $med['id'] ?>" data-stock="<?= $med['stock'] ?>" data-price="<?= $med['price'] ?>">
                                        <?= htmlspecialchars($med['name']) ?> (Stock: <?= $med['stock'] ?>) - <?= formatCurrency($med['price']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <input type="number" name="quantities[]" class="form-control" placeholder="Quantity" min="1" value="1">
                            </div>
                            <div class="col-md-2 mb-2">
                                <button type="button" class="btn btn-outline-danger" onclick="removeMedicineRow(this)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-success w-100 mb-2">
                        <i class="bi bi-check-lg me-2"></i>Dispense Medicines
                    </button>
                    <a href="index.php?page=pharmacy" class="btn btn-outline-secondary w-100">Cancel</a>
                </div>
            </div>

            <?php if (!empty($prescriptions)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                Prescription medicines have been pre-filled.
            </div>
            <?php endif; ?>
        </div>
    </div>
</form>

<script>
function addMedicineRow() {
    const template = document.querySelector('.medicine-row').cloneNode(true);
    template.querySelector('select').value = '';
    template.querySelector('input').value = '1';
    document.getElementById('medicineRows').appendChild(template);
}

function removeMedicineRow(btn) {
    const rows = document.querySelectorAll('.medicine-row');
    if (rows.length > 1) {
        btn.closest('.medicine-row').remove();
    }
}
</script>
