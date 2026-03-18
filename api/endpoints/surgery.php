<?php
/**
 * Surgery Endpoints
 */

function handleSurgeryRoute($method, $segments, $user) {
    $id = isset($segments[0]) && is_numeric($segments[0]) ? (int)$segments[0] : null;

    switch ($method) {
        case 'GET':
            if ($id) getSurgery($id, $user);
            else getSurgeries($user);
            break;
        case 'POST':
            createSurgery($user);
            break;
        case 'PUT':
            if ($id) updateSurgery($id, $user);
            else jsonResponse(['success' => false, 'message' => 'ID required'], 400);
            break;
        case 'DELETE':
            if ($id) deleteSurgery($id, $user);
            else jsonResponse(['success' => false, 'message' => 'ID required'], 400);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
}

function getSurgeries($user) {
    $storage = new Storage();
    $surgeries = $storage->getAll('surgeries');

    foreach ($surgeries as &$surgery) {
        $patient = $storage->getById('patients', $surgery['patient_id'] ?? 0);
        $doctor = $storage->getById('doctors', $surgery['doctor_id'] ?? 0);
        $surgery['patient_name'] = $patient['name'] ?? 'N/A';
        $surgery['doctor_name'] = $doctor['name'] ?? 'N/A';
    }

    usort($surgeries, function($a, $b) {
        return strtotime($b['date'] ?? 0) - strtotime($a['date'] ?? 0);
    });

    jsonResponse(['success' => true, 'data' => $surgeries]);
}

function getSurgery($id, $user) {
    $storage = new Storage();
    $surgery = $storage->getById('surgeries', $id);
    if (!$surgery) jsonResponse(['success' => false, 'message' => 'Surgery not found'], 404);

    $patient = $storage->getById('patients', $surgery['patient_id'] ?? 0);
    $doctor = $storage->getById('doctors', $surgery['doctor_id'] ?? 0);
    $surgery['patient_name'] = $patient['name'] ?? 'N/A';
    $surgery['doctor_name'] = $doctor['name'] ?? 'N/A';

    jsonResponse(['success' => true, 'data' => $surgery]);
}

function createSurgery($user) {
    requireRole($user, ['admin', 'doctor']);
    $data = getRequestBody();
    $storage = new Storage();

    if (empty($data['patient_id']) || empty($data['doctor_id']) || empty($data['surgery_name']) || empty($data['date'])) {
        jsonResponse(['success' => false, 'message' => 'Patient, doctor, surgery name and date are required'], 400);
    }

    $surgery = [
        'patient_id' => (int)$data['patient_id'],
        'doctor_id' => (int)$data['doctor_id'],
        'surgery_name' => sanitize($data['surgery_name']),
        'date' => sanitize($data['date']),
        'time' => sanitize($data['time'] ?? ''),
        'operation_theatre' => sanitize($data['operation_theatre'] ?? ''),
        'anesthesia_type' => sanitize($data['anesthesia_type'] ?? ''),
        'pre_op_notes' => sanitize($data['pre_op_notes'] ?? ''),
        'post_op_notes' => sanitize($data['post_op_notes'] ?? ''),
        'status' => sanitize($data['status'] ?? 'scheduled')
    ];

    $id = $storage->insert('surgeries', $surgery);
    logActivity('surgery_schedule', "Scheduled surgery: {$surgery['surgery_name']}", $user['sub']);
    jsonResponse(['success' => true, 'message' => 'Surgery scheduled', 'id' => $id], 201);
}

function updateSurgery($id, $user) {
    requireRole($user, ['admin', 'doctor']);
    $storage = new Storage();
    if (!$storage->getById('surgeries', $id)) {
        jsonResponse(['success' => false, 'message' => 'Surgery not found'], 404);
    }

    $data = getRequestBody();
    $updates = [];
    foreach (['patient_id', 'doctor_id'] as $f) {
        if (isset($data[$f])) $updates[$f] = (int)$data[$f];
    }
    foreach (['surgery_name', 'date', 'time', 'operation_theatre', 'anesthesia_type', 'pre_op_notes', 'post_op_notes', 'status'] as $f) {
        if (isset($data[$f])) $updates[$f] = sanitize($data[$f]);
    }

    $storage->update('surgeries', $id, $updates);
    logActivity('surgery_update', "Updated surgery ID: $id", $user['sub']);
    jsonResponse(['success' => true, 'message' => 'Surgery updated']);
}

function deleteSurgery($id, $user) {
    requireRole($user, ['admin']);
    $storage = new Storage();
    if (!$storage->getById('surgeries', $id)) {
        jsonResponse(['success' => false, 'message' => 'Surgery not found'], 404);
    }

    $storage->delete('surgeries', $id);
    logActivity('surgery_delete', "Deleted surgery ID: $id", $user['sub']);
    jsonResponse(['success' => true, 'message' => 'Surgery deleted']);
}
