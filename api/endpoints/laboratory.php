<?php
/**
 * Laboratory Endpoints
 */

function handleLaboratoryRoute($method, $segments, $user) {
    $action = $segments[0] ?? '';
    $id = isset($segments[1]) && is_numeric($segments[1]) ? (int)$segments[1] : (is_numeric($action) ? (int)$action : null);
    $subAction = $segments[2] ?? null;

    if ($action === 'tests') {
        if ($method === 'GET') getLabTests($user);
        else jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    } elseif ($action === 'orders' || is_numeric($action)) {
        switch ($method) {
            case 'GET':
                if ($id && !$subAction) getLabOrder($id, $user);
                else getLabOrders($user);
                break;
            case 'POST':
                createLabOrder($user);
                break;
            case 'PUT':
                if ($id && $subAction === 'result') updateLabResult($id, $user);
                elseif ($id) updateLabOrder($id, $user);
                else jsonResponse(['success' => false, 'message' => 'ID required'], 400);
                break;
            case 'DELETE':
                if ($id) deleteLabOrder($id, $user);
                else jsonResponse(['success' => false, 'message' => 'ID required'], 400);
                break;
        }
    } else {
        jsonResponse(['success' => false, 'message' => 'Invalid endpoint'], 404);
    }
}

function getLabTests($user) {
    $storage = new Storage();
    $tests = $storage->getAll('lab_tests');
    jsonResponse(['success' => true, 'data' => $tests]);
}

function getLabOrders($user) {
    $storage = new Storage();
    $orders = $storage->getAll('lab_orders');

    foreach ($orders as &$order) {
        $patient = $storage->getById('patients', $order['patient_id'] ?? 0);
        $doctor = $storage->getById('doctors', $order['doctor_id'] ?? 0);
        $test = $storage->getById('lab_tests', $order['test_id'] ?? 0);
        $order['patient_name'] = $patient['name'] ?? 'N/A';
        $order['doctor_name'] = $doctor['name'] ?? 'N/A';
        $order['test_name'] = $test['name'] ?? 'N/A';
    }

    usort($orders, function($a, $b) {
        return strtotime($b['order_date'] ?? 0) - strtotime($a['order_date'] ?? 0);
    });

    jsonResponse(['success' => true, 'data' => $orders]);
}

function getLabOrder($id, $user) {
    $storage = new Storage();
    $order = $storage->getById('lab_orders', $id);
    if (!$order) jsonResponse(['success' => false, 'message' => 'Lab order not found'], 404);

    $patient = $storage->getById('patients', $order['patient_id'] ?? 0);
    $doctor = $storage->getById('doctors', $order['doctor_id'] ?? 0);
    $test = $storage->getById('lab_tests', $order['test_id'] ?? 0);
    $order['patient_name'] = $patient['name'] ?? 'N/A';
    $order['doctor_name'] = $doctor['name'] ?? 'N/A';
    $order['test_name'] = $test['name'] ?? 'N/A';

    jsonResponse(['success' => true, 'data' => $order]);
}

function createLabOrder($user) {
    requireRole($user, ['admin', 'doctor', 'lab_technician']);
    $data = getRequestBody();
    $storage = new Storage();

    if (empty($data['patient_id']) || empty($data['doctor_id']) || empty($data['test_id'])) {
        jsonResponse(['success' => false, 'message' => 'Patient, doctor, and test are required'], 400);
    }

    $order = [
        'patient_id' => (int)$data['patient_id'],
        'doctor_id' => (int)$data['doctor_id'],
        'test_id' => (int)$data['test_id'],
        'order_date' => sanitize($data['order_date'] ?? date('Y-m-d')),
        'priority' => sanitize($data['priority'] ?? 'normal'),
        'notes' => sanitize($data['notes'] ?? ''),
        'status' => 'pending'
    ];

    $id = $storage->insert('lab_orders', $order);
    logActivity('lab_order', "Created lab order for patient ID: {$order['patient_id']}", $user['sub']);
    jsonResponse(['success' => true, 'message' => 'Lab order created', 'id' => $id], 201);
}

function updateLabResult($id, $user) {
    requireRole($user, ['admin', 'lab_technician']);
    $storage = new Storage();
    $order = $storage->getById('lab_orders', $id);
    if (!$order) jsonResponse(['success' => false, 'message' => 'Lab order not found'], 404);

    $data = getRequestBody();

    $storage->update('lab_orders', $id, [
        'result' => sanitize($data['result'] ?? ''),
        'normal_range' => sanitize($data['normal_range'] ?? ''),
        'remarks' => sanitize($data['remarks'] ?? ''),
        'result_date' => sanitize($data['result_date'] ?? date('Y-m-d')),
        'status' => 'completed'
    ]);

    logActivity('lab_result', "Entered result for lab order ID: $id", $user['sub']);
    jsonResponse(['success' => true, 'message' => 'Lab result saved']);
}

function updateLabOrder($id, $user) {
    requireRole($user, ['admin', 'lab_technician']);
    $storage = new Storage();
    if (!$storage->getById('lab_orders', $id)) {
        jsonResponse(['success' => false, 'message' => 'Lab order not found'], 404);
    }

    $data = getRequestBody();
    $updates = [];
    foreach (['priority', 'notes', 'status'] as $f) {
        if (isset($data[$f])) $updates[$f] = sanitize($data[$f]);
    }

    $storage->update('lab_orders', $id, $updates);
    jsonResponse(['success' => true, 'message' => 'Lab order updated']);
}

function deleteLabOrder($id, $user) {
    requireRole($user, ['admin']);
    $storage = new Storage();
    if (!$storage->getById('lab_orders', $id)) {
        jsonResponse(['success' => false, 'message' => 'Lab order not found'], 404);
    }

    $storage->delete('lab_orders', $id);
    logActivity('lab_delete', "Deleted lab order ID: $id", $user['sub']);
    jsonResponse(['success' => true, 'message' => 'Lab order deleted']);
}
