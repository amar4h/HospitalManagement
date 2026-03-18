<?php
/**
 * Create Invoice
 */
requireAuth();
requireRole(['admin', 'accountant', 'receptionist']);

$storage = getStorage();
$patients = $storage->getAll('patients');

$selectedPatient = $_GET['patient_id'] ?? '';
$opdId = $_GET['opd_id'] ?? '';
$ipdId = $_GET['ipd_id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patientId = (int)($_POST['patient_id'] ?? 0);
    $items = $_POST['items'] ?? [];
    $quantities = $_POST['quantities'] ?? [];
    $prices = $_POST['prices'] ?? [];

    if (empty($patientId)) {
        setFlashMessage('error', 'Patient is required');
    } else {
        // Calculate totals
        $subtotal = 0;
        $invoiceItems = [];

        foreach ($items as $index => $item) {
            if (!empty($item)) {
                $qty = (float)($quantities[$index] ?? 1);
                $price = (float)($prices[$index] ?? 0);
                $total = $qty * $price;
                $subtotal += $total;

                $invoiceItems[] = [
                    'description' => sanitize($item),
                    'quantity' => $qty,
                    'price' => $price,
                    'total' => $total
                ];
            }
        }

        $discount = (float)($_POST['discount'] ?? 0);
        $tax = (float)($_POST['tax'] ?? 0);

        $discountAmount = ($subtotal * $discount) / 100;
        $taxableAmount = $subtotal - $discountAmount;
        $taxAmount = ($taxableAmount * $tax) / 100;
        $totalAmount = $taxableAmount + $taxAmount;

        $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad($storage->count('invoices') + 1, 4, '0', STR_PAD_LEFT);

        $invoiceId = $storage->insert('invoices', [
            'invoice_number' => $invoiceNumber,
            'patient_id' => $patientId,
            'opd_id' => $opdId ?: null,
            'ipd_id' => $ipdId ?: null,
            'items' => json_encode($invoiceItems),
            'subtotal' => $subtotal,
            'discount_percent' => $discount,
            'discount_amount' => $discountAmount,
            'tax_percent' => $tax,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'paid_amount' => 0,
            'payment_status' => 'unpaid',
            'notes' => sanitize($_POST['notes'] ?? '')
        ]);

        $patient = $storage->getById('patients', $patientId);
        logActivity('invoice_create', 'Created invoice ' . $invoiceNumber . ' for ' . $patient['name']);
        setFlashMessage('success', 'Invoice created: ' . $invoiceNumber);
        redirect('index.php?page=invoice-view&id=' . $invoiceId);
    }
}
?>

<div class="page-header">
    <h1><i class="bi bi-plus-lg me-2"></i>Create Invoice</h1>
    <div>
        <a href="index.php?page=billing" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<form method="POST" action="">
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
                    <span><i class="bi bi-list me-2"></i>Invoice Items</span>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addInvoiceItem()">
                        <i class="bi bi-plus me-1"></i>Add Item
                    </button>
                </div>
                <div class="card-body">
                    <div id="invoiceItems">
                        <div class="invoice-item row mb-3">
                            <div class="col-md-5 mb-2">
                                <input type="text" name="items[]" class="form-control" placeholder="Description" required>
                            </div>
                            <div class="col-md-2 mb-2">
                                <input type="number" name="quantities[]" class="form-control item-qty" placeholder="Qty" value="1" min="1" required>
                            </div>
                            <div class="col-md-2 mb-2">
                                <input type="number" name="prices[]" class="form-control item-price" placeholder="Price" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-2 mb-2">
                                <input type="text" class="form-control item-total" placeholder="Total" readonly>
                            </div>
                            <div class="col-md-1 mb-2">
                                <button type="button" class="btn btn-outline-danger" onclick="removeInvoiceItem(this)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Notes</div>
                <div class="card-body">
                    <textarea name="notes" class="form-control" rows="2" placeholder="Additional notes..."></textarea>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">Invoice Summary</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Subtotal</label>
                        <div class="input-group">
                            <span class="input-group-text"><?= CURRENCY_SYMBOL ?></span>
                            <input type="text" id="subtotal" class="form-control" readonly value="0.00">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Discount %</label>
                            <input type="number" name="discount" id="discount" class="form-control" value="0" min="0" max="100">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Discount Amt</label>
                            <input type="text" id="discountAmount" class="form-control" readonly value="0.00">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Tax %</label>
                            <input type="number" name="tax" id="tax" class="form-control" value="0" min="0" max="100">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Tax Amt</label>
                            <input type="text" id="taxAmount" class="form-control" readonly value="0.00">
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Grand Total</label>
                        <div class="input-group">
                            <span class="input-group-text"><?= CURRENCY_SYMBOL ?></span>
                            <input type="text" id="grandTotal" class="form-control fw-bold fs-5" readonly value="0.00">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-check-lg me-2"></i>Create Invoice
                    </button>
                    <a href="index.php?page=billing" class="btn btn-outline-secondary w-100">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.getElementById('discount').addEventListener('change', calculateTotal);
document.getElementById('tax').addEventListener('change', calculateTotal);
</script>
