<?php
/**
 * Record Payment
 */
requireAuth();
requireRole(['admin', 'accountant', 'receptionist']);

$storage = getStorage();
$id = (int)($_GET['id'] ?? 0);
$invoice = $storage->getById('invoices', $id);

if (!$invoice || $invoice['payment_status'] === 'paid') {
    setFlashMessage('error', 'Invalid invoice or already paid');
    redirect('index.php?page=billing');
}

$patient = $storage->getById('patients', $invoice['patient_id']);
$balance = $invoice['total_amount'] - ($invoice['paid_amount'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float)($_POST['amount'] ?? 0);
    $paymentMode = sanitize($_POST['payment_mode'] ?? 'Cash');
    $reference = sanitize($_POST['reference'] ?? '');

    if ($amount <= 0 || $amount > $balance) {
        setFlashMessage('error', 'Invalid payment amount');
    } else {
        // Record payment
        $storage->insert('payments', [
            'invoice_id' => $id,
            'patient_id' => $invoice['patient_id'],
            'amount' => $amount,
            'payment_mode' => $paymentMode,
            'reference' => $reference,
            'received_by' => getCurrentUserId()
        ]);

        // Update invoice
        $newPaidAmount = ($invoice['paid_amount'] ?? 0) + $amount;
        $newStatus = $newPaidAmount >= $invoice['total_amount'] ? 'paid' : 'partial';

        $storage->update('invoices', $id, [
            'paid_amount' => $newPaidAmount,
            'payment_status' => $newStatus
        ]);

        logActivity('payment', 'Recorded payment of ' . formatCurrency($amount) . ' for ' . $invoice['invoice_number']);
        setFlashMessage('success', 'Payment recorded successfully');
        redirect('index.php?page=invoice-view&id=' . $id);
    }
}
?>

<div class="page-header">
    <h1><i class="bi bi-cash me-2"></i>Record Payment</h1>
    <div>
        <a href="index.php?page=invoice-view&id=<?= $id ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Invoice
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <!-- Invoice Summary -->
        <div class="card mb-4">
            <div class="card-header">Invoice Summary</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <p class="mb-1"><strong>Invoice:</strong> <?= $invoice['invoice_number'] ?></p>
                        <p class="mb-0"><strong>Patient:</strong> <?= htmlspecialchars($patient['name']) ?></p>
                    </div>
                    <div class="col-6 text-end">
                        <p class="mb-1"><strong>Total:</strong> <?= formatCurrency($invoice['total_amount']) ?></p>
                        <p class="mb-1"><strong>Paid:</strong> <span class="text-success"><?= formatCurrency($invoice['paid_amount'] ?? 0) ?></span></p>
                        <p class="mb-0"><strong>Balance:</strong> <span class="text-danger fs-5"><?= formatCurrency($balance) ?></span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="card">
            <div class="card-body">
                <form method="POST" action="" class="needs-validation" novalidate>
                    <?= csrfField() ?>

                    <div class="mb-3">
                        <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><?= CURRENCY_SYMBOL ?></span>
                            <input type="number" name="amount" class="form-control fs-5" step="0.01" min="0.01" max="<?= $balance ?>" value="<?= $balance ?>" required>
                        </div>
                        <small class="text-muted">Maximum: <?= formatCurrency($balance) ?></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Mode</label>
                        <select name="payment_mode" class="form-select">
                            <option value="Cash">Cash</option>
                            <option value="Card">Credit/Debit Card</option>
                            <option value="UPI">UPI</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Insurance">Insurance</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reference / Transaction ID</label>
                        <input type="text" name="reference" class="form-control" placeholder="Optional">
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-check-lg me-2"></i>Record Payment
                        </button>
                        <a href="index.php?page=invoice-view&id=<?= $id ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
