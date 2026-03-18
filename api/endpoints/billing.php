<?php
/**
 * Billing Endpoints
 */

function handleBillingRoute($method, $segments, $user) {
    $action = $segments[0] ?? '';
    $id = isset($segments[1]) && is_numeric($segments[1]) ? (int)$segments[1] : (is_numeric($action) ? (int)$action : null);
    $subAction = $segments[2] ?? null;

    if ($action === 'invoices' || is_numeric($action) || $action === '') {
        switch ($method) {
            case 'GET':
                if ($id) getInvoice($id, $user);
                else getInvoices($user);
                break;
            case 'POST':
                if ($id && $subAction === 'payment') addPayment($id, $user);
                else createInvoice($user);
                break;
            case 'DELETE':
                if ($id) deleteInvoice($id, $user);
                else jsonResponse(['success' => false, 'message' => 'ID required'], 400);
                break;
        }
    } else {
        jsonResponse(['success' => false, 'message' => 'Invalid endpoint'], 404);
    }
}

function getInvoices($user) {
    $storage = new Storage();
    $invoices = $storage->getAll('invoices');

    foreach ($invoices as &$inv) {
        $patient = $storage->getById('patients', $inv['patient_id'] ?? 0);
        $inv['patient_name'] = $patient['name'] ?? 'N/A';
    }

    usort($invoices, function($a, $b) {
        return strtotime($b['date'] ?? 0) - strtotime($a['date'] ?? 0);
    });

    jsonResponse(['success' => true, 'data' => $invoices]);
}

function getInvoice($id, $user) {
    $storage = new Storage();
    $invoice = $storage->getById('invoices', $id);
    if (!$invoice) jsonResponse(['success' => false, 'message' => 'Invoice not found'], 404);

    $patient = $storage->getById('patients', $invoice['patient_id'] ?? 0);
    $invoice['patient_name'] = $patient['name'] ?? 'N/A';

    // Get payments
    $payments = array_filter($storage->getAll('payments'), function($p) use ($id) {
        return ($p['invoice_id'] ?? 0) == $id;
    });
    $invoice['payments'] = array_values($payments);

    jsonResponse(['success' => true, 'data' => $invoice]);
}

function createInvoice($user) {
    requireRole($user, ['admin', 'receptionist', 'accountant']);
    $data = getRequestBody();
    $storage = new Storage();

    if (empty($data['patient_id']) || empty($data['items'])) {
        jsonResponse(['success' => false, 'message' => 'Patient and items are required'], 400);
    }

    $invoice = [
        'invoice_number' => generateInvoiceNumber(),
        'patient_id' => (int)$data['patient_id'],
        'date' => sanitize($data['date'] ?? date('Y-m-d')),
        'items' => $data['items'],
        'discount' => (float)($data['discount'] ?? 0),
        'total' => (float)($data['total'] ?? 0),
        'paid' => 0,
        'status' => 'pending'
    ];

    $id = $storage->insert('invoices', $invoice);
    logActivity('invoice_create', "Created invoice: {$invoice['invoice_number']}", $user['sub']);
    jsonResponse(['success' => true, 'message' => 'Invoice created', 'id' => $id], 201);
}

function addPayment($invoiceId, $user) {
    requireRole($user, ['admin', 'receptionist', 'accountant']);
    $storage = new Storage();
    $invoice = $storage->getById('invoices', $invoiceId);
    if (!$invoice) jsonResponse(['success' => false, 'message' => 'Invoice not found'], 404);

    $data = getRequestBody();
    $amount = (float)($data['amount'] ?? 0);

    if ($amount <= 0) {
        jsonResponse(['success' => false, 'message' => 'Valid amount is required'], 400);
    }

    // Record payment
    $payment = [
        'invoice_id' => $invoiceId,
        'amount' => $amount,
        'payment_method' => sanitize($data['payment_method'] ?? 'Cash'),
        'reference' => sanitize($data['reference'] ?? ''),
        'date' => sanitize($data['date'] ?? date('Y-m-d')),
        'received_by' => $user['sub']
    ];

    $storage->insert('payments', $payment);

    // Update invoice
    $newPaid = ($invoice['paid'] ?? 0) + $amount;
    $newStatus = $newPaid >= ($invoice['total'] ?? 0) ? 'paid' : 'partial';

    $storage->update('invoices', $invoiceId, [
        'paid' => $newPaid,
        'status' => $newStatus
    ]);

    logActivity('payment_received', "Received payment of {$amount} for invoice ID: $invoiceId", $user['sub']);
    jsonResponse(['success' => true, 'message' => 'Payment recorded']);
}

function deleteInvoice($id, $user) {
    requireRole($user, ['admin']);
    $storage = new Storage();
    $invoice = $storage->getById('invoices', $id);
    if (!$invoice) jsonResponse(['success' => false, 'message' => 'Invoice not found'], 404);

    // Delete associated payments
    $payments = $storage->getAll('payments');
    foreach ($payments as $payment) {
        if (($payment['invoice_id'] ?? 0) == $id) {
            $storage->delete('payments', $payment['id']);
        }
    }

    $storage->delete('invoices', $id);
    logActivity('invoice_delete', "Deleted invoice: {$invoice['invoice_number']}", $user['sub']);
    jsonResponse(['success' => true, 'message' => 'Invoice deleted']);
}
