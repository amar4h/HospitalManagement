<?php
/**
 * Pharmacy Endpoints
 */

function handlePharmacyRoute($method, $segments, $user) {
    $action = $segments[0] ?? '';
    $id = isset($segments[1]) && is_numeric($segments[1]) ? (int)$segments[1] : null;

    if ($action === 'medicines') {
        switch ($method) {
            case 'GET':
                if ($id) getMedicine($id, $user);
                else getMedicines($user);
                break;
            case 'POST':
                createMedicine($user);
                break;
            case 'PUT':
                if ($id) updateMedicine($id, $user);
                else jsonResponse(['success' => false, 'message' => 'ID required'], 400);
                break;
            case 'DELETE':
                if ($id) deleteMedicine($id, $user);
                else jsonResponse(['success' => false, 'message' => 'ID required'], 400);
                break;
        }
    } elseif ($action === 'dispense') {
        if ($method === 'POST') dispenseMedicine($user);
        else jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    } elseif ($action === 'dispenses') {
        if ($method === 'GET') getDispenses($user);
        else jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    } elseif ($action === 'low-stock') {
        if ($method === 'GET') getLowStock($user);
        else jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    } else {
        jsonResponse(['success' => false, 'message' => 'Invalid endpoint'], 404);
    }
}

function getMedicines($user) {
    $storage = new Storage();
    $medicines = $storage->getAll('medicines');
    usort($medicines, function($a, $b) { return strcmp($a['name'] ?? '', $b['name'] ?? ''); });
    jsonResponse(['success' => true, 'data' => $medicines]);
}

function getMedicine($id, $user) {
    $storage = new Storage();
    $medicine = $storage->getById('medicines', $id);
    if (!$medicine) jsonResponse(['success' => false, 'message' => 'Medicine not found'], 404);
    jsonResponse(['success' => true, 'data' => $medicine]);
}

function createMedicine($user) {
    requireRole($user, ['admin', 'pharmacist']);
    $data = getRequestBody();
    $storage = new Storage();

    if (empty($data['name']) || !isset($data['price'])) {
        jsonResponse(['success' => false, 'message' => 'Name and price are required'], 400);
    }

    $medicine = [
        'name' => sanitize($data['name']),
        'generic_name' => sanitize($data['generic_name'] ?? ''),
        'category' => sanitize($data['category'] ?? ''),
        'unit' => sanitize($data['unit'] ?? ''),
        'price' => (float)$data['price'],
        'stock' => (int)($data['stock'] ?? 0),
        'reorder_level' => (int)($data['reorder_level'] ?? 10),
        'expiry_date' => sanitize($data['expiry_date'] ?? ''),
        'description' => sanitize($data['description'] ?? '')
    ];

    $id = $storage->insert('medicines', $medicine);
    logActivity('medicine_add', "Added medicine: {$medicine['name']}", $user['sub']);
    jsonResponse(['success' => true, 'message' => 'Medicine added', 'id' => $id], 201);
}

function updateMedicine($id, $user) {
    requireRole($user, ['admin', 'pharmacist']);
    $storage = new Storage();
    if (!$storage->getById('medicines', $id)) {
        jsonResponse(['success' => false, 'message' => 'Medicine not found'], 404);
    }

    $data = getRequestBody();
    $updates = [];
    foreach (['name', 'generic_name', 'category', 'unit', 'expiry_date', 'description'] as $f) {
        if (isset($data[$f])) $updates[$f] = sanitize($data[$f]);
    }
    foreach (['price', 'stock', 'reorder_level'] as $f) {
        if (isset($data[$f])) $updates[$f] = $f === 'price' ? (float)$data[$f] : (int)$data[$f];
    }

    $storage->update('medicines', $id, $updates);
    logActivity('medicine_update', "Updated medicine ID: $id", $user['sub']);
    jsonResponse(['success' => true, 'message' => 'Medicine updated']);
}

function deleteMedicine($id, $user) {
    requireRole($user, ['admin']);
    $storage = new Storage();
    $medicine = $storage->getById('medicines', $id);
    if (!$medicine) jsonResponse(['success' => false, 'message' => 'Medicine not found'], 404);

    $storage->delete('medicines', $id);
    logActivity('medicine_delete', "Deleted medicine: {$medicine['name']}", $user['sub']);
    jsonResponse(['success' => true, 'message' => 'Medicine deleted']);
}

function dispenseMedicine($user) {
    requireRole($user, ['admin', 'pharmacist', 'doctor']);
    $data = getRequestBody();
    $storage = new Storage();

    if (empty($data['patient_id']) || empty($data['medicine_id']) || empty($data['quantity'])) {
        jsonResponse(['success' => false, 'message' => 'Patient, medicine, and quantity are required'], 400);
    }

    $medicine = $storage->getById('medicines', $data['medicine_id']);
    if (!$medicine) jsonResponse(['success' => false, 'message' => 'Medicine not found'], 404);

    $quantity = (int)$data['quantity'];
    if ($medicine['stock'] < $quantity) {
        jsonResponse(['success' => false, 'message' => 'Insufficient stock'], 400);
    }

    // Update stock
    $storage->update('medicines', $medicine['id'], ['stock' => $medicine['stock'] - $quantity]);

    // Record dispense
    $dispense = [
        'patient_id' => (int)$data['patient_id'],
        'medicine_id' => (int)$data['medicine_id'],
        'quantity' => $quantity,
        'unit_price' => $medicine['price'],
        'total' => $medicine['price'] * $quantity,
        'notes' => sanitize($data['notes'] ?? ''),
        'dispensed_by' => $user['sub'],
        'dispense_date' => date('Y-m-d H:i:s')
    ];

    $id = $storage->insert('dispenses', $dispense);
    logActivity('medicine_dispense', "Dispensed {$quantity} of {$medicine['name']}", $user['sub']);
    jsonResponse(['success' => true, 'message' => 'Medicine dispensed', 'id' => $id], 201);
}

function getDispenses($user) {
    $storage = new Storage();
    $dispenses = $storage->getAll('dispenses');

    foreach ($dispenses as &$d) {
        $patient = $storage->getById('patients', $d['patient_id'] ?? 0);
        $medicine = $storage->getById('medicines', $d['medicine_id'] ?? 0);
        $d['patient_name'] = $patient['name'] ?? 'N/A';
        $d['medicine_name'] = $medicine['name'] ?? 'N/A';
    }

    jsonResponse(['success' => true, 'data' => $dispenses]);
}

function getLowStock($user) {
    $storage = new Storage();
    $medicines = $storage->getAll('medicines');

    $lowStock = array_filter($medicines, function($m) {
        return ($m['stock'] ?? 0) <= ($m['reorder_level'] ?? 10);
    });

    jsonResponse(['success' => true, 'data' => array_values($lowStock)]);
}
