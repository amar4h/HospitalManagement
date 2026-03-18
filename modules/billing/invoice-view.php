<?php
/**
 * View Invoice
 */
requireAuth();

$storage = getStorage();
$id = (int)($_GET['id'] ?? 0);
$invoice = $storage->getById('invoices', $id);

if (!$invoice) {
    setFlashMessage('error', 'Invoice not found');
    redirect('index.php?page=billing');
}

$patient = $storage->getById('patients', $invoice['patient_id']);
$items = json_decode($invoice['items'], true) ?? [];
?>

<div class="page-header">
    <h1><i class="bi bi-receipt me-2"></i>Invoice</h1>
    <div class="quick-actions">
        <button onclick="printElement('printArea')" class="btn btn-info">
            <i class="bi bi-printer me-2"></i>Print
        </button>
        <?php if ($invoice['payment_status'] !== 'paid'): ?>
        <a href="index.php?page=payment&id=<?= $id ?>" class="btn btn-success">
            <i class="bi bi-cash me-2"></i>Record Payment
        </a>
        <?php endif; ?>
        <a href="index.php?page=billing" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div id="printArea">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <!-- Invoice Header -->
                <div class="card-header bg-primary text-white">
                    <div class="row">
                        <div class="col-8">
                            <h4 class="mb-0"><?= HOSPITAL_NAME ?></h4>
                            <small><?= HOSPITAL_ADDRESS ?></small><br>
                            <small>Phone: <?= HOSPITAL_PHONE ?></small>
                        </div>
                        <div class="col-4 text-end">
                            <h5 class="mb-0">INVOICE</h5>
                            <strong><?= $invoice['invoice_number'] ?></strong><br>
                            <small>Date: <?= formatDate($invoice['created_at']) ?></small>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Patient Info -->
                    <div class="row mb-4">
                        <div class="col-6">
                            <h6 class="text-muted">Bill To:</h6>
                            <p class="mb-1"><strong><?= htmlspecialchars($patient['name']) ?></strong></p>
                            <p class="mb-1"><?= $patient['patient_id'] ?></p>
                            <p class="mb-1"><?= $patient['phone'] ?></p>
                            <?php if (!empty($patient['address'])): ?>
                            <p class="mb-0"><?= htmlspecialchars($patient['address']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-6 text-end">
                            <h6 class="text-muted">Payment Status:</h6>
                            <?= getStatusBadge($invoice['payment_status']) ?>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Description</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($items as $item): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($item['description']) ?></td>
                                    <td class="text-center"><?= $item['quantity'] ?></td>
                                    <td class="text-end"><?= formatCurrency($item['price']) ?></td>
                                    <td class="text-end"><?= formatCurrency($item['total']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">Subtotal</th>
                                    <th class="text-end"><?= formatCurrency($invoice['subtotal']) ?></th>
                                </tr>
                                <?php if ($invoice['discount_percent'] > 0): ?>
                                <tr>
                                    <td colspan="4" class="text-end">Discount (<?= $invoice['discount_percent'] ?>%)</td>
                                    <td class="text-end text-danger">-<?= formatCurrency($invoice['discount_amount']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($invoice['tax_percent'] > 0): ?>
                                <tr>
                                    <td colspan="4" class="text-end">Tax (<?= $invoice['tax_percent'] ?>%)</td>
                                    <td class="text-end"><?= formatCurrency($invoice['tax_amount']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr class="table-primary">
                                    <th colspan="4" class="text-end">Grand Total</th>
                                    <th class="text-end fs-5"><?= formatCurrency($invoice['total_amount']) ?></th>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end">Paid Amount</td>
                                    <td class="text-end text-success"><?= formatCurrency($invoice['paid_amount'] ?? 0) ?></td>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">Balance Due</th>
                                    <th class="text-end text-danger"><?= formatCurrency($invoice['total_amount'] - ($invoice['paid_amount'] ?? 0)) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <?php if (!empty($invoice['notes'])): ?>
                    <div class="mt-4">
                        <h6>Notes:</h6>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($invoice['notes'])) ?></p>
                    </div>
                    <?php endif; ?>

                    <div class="mt-5 pt-4 border-top">
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">This is a computer generated invoice</small>
                            </div>
                            <div class="col-6 text-end">
                                <small class="text-muted">Thank you for choosing <?= HOSPITAL_NAME ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
