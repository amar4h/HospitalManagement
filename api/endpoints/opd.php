<?php
/**
 * OPD Endpoints
 */

function handleOPDRoute($method, $segments, $user) {
    $id = isset($segments[0]) && is_numeric($segments[0]) ? (int)$segments[0] : null;

    switch ($method) {
        case 'GET':
            if ($id) getOPDVisit($id, $user);
            else getOPDVisits($user);
            break;
        case 'POST':
            createOPDVisit($user);
            break;
        case 'PUT':
            if ($id) updateOPDVisit($id, $user);
            else jsonResponse(['success' => false, 'message' => 'ID required'], 400);
            break;
        case 'DELETE':
            if ($id) deleteOPDVisit($id, $user);
            else jsonResponse(['success' => false, 'message' => 'ID required'], 400);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
}

function getOPDVisits($user) {
    $storage = new Storage();
    $visits = $storage->getAll('opd_visits');

    foreach ($visits as &$visit) {
        $patient = $storage->getById('patients', $visit['patient_id'] ?? 0);
        $doctor = $storage->getById('doctors', $visit['doctor_id'] ?? 0);
        $visit['patient_name'] = $patient['name'] ?? 'N/A';
        $visit['doctor_name'] = $doctor['name'] ?? 'N/A';
    }

    usort($visits, function($a, $b) {
        return strtotime($b['date'] ?? 0) - strtotime($a['date'] ?? 0);
    });

    jsonResponse(['success' => true, 'data' => $visits]);
}

function getOPDVisit($id, $user) {
    $storage = new Storage();
    $visit = $storage->getById('opd_visits', $id);
    if (!$visit) jsonResponse(['success' => false, 'message' => 'OPD visit not found'], 404);

    $patient = $storage->getById('patients', $visit['patient_id'] ?? 0);
    $doctor = $storage->getById('doctors', $visit['doctor_id'] ?? 0);
    $visit['patient_name'] = $patient['name'] ?? 'N/A';
    $visit['doctor_name'] = $doctor['name'] ?? 'N/A';

    jsonResponse(['success' => true, 'data' => $visit]);
}

function createOPDVisit($user) {
    requireRole($user, ['admin', 'doctor', 'receptionist']);
    $data = getRequestBody();
    $storage = new Storage();

    if (empty($data['patient_id']) || empty($data['doctor_id'])) {
        jsonResponse(['success' => false, 'message' => 'Patient and doctor are required'], 400);
    }

    $visit = [
        'patient_id' => (int)$data['patient_id'],
        'doctor_id' => (int)$data['doctor_id'],
        'date' => sanitize($data['date'] ?? date('Y-m-d')),
        'consultation_fee' => (float)($data['consultation_fee'] ?? 0),
        'bp' => sanitize($data['bp'] ?? ''),
        'temperature' => sanitize($data['temperature'] ?? ''),
        'pulse' => sanitize($data['pulse'] ?? ''),
        'weight' => sanitize($data['weight'] ?? ''),
        'complaints' => sanitize($data['complaints'] ?? ''),
        'diagnosis' => sanitize($data['diagnosis'] ?? ''),
        'prescription' => sanitize($data['prescription'] ?? ''),
        'notes' => sanitize($data['notes'] ?? '')
    ];

    $id = $storage->insert('opd_visits', $visit);
    logActivity('opd_create', "Created OPD visit for patient ID: {$visit['patient_id']}", $user['sub']);
    jsonResponse(['success' => true, 'message' => 'OPD visit created', 'id' => $id], 201);
}

function updateOPDVisit($id, $user) {
    requireRole($user, ['admin', 'doctor']);
    $storage = new Storage();
    if (!$storage->getById('opd_visits', $id)) {
        jsonResponse(['success' => false, 'message' => 'OPD visit not found'], 404);
    }

    $data = getRequestBody();
    $updates = [];
    $fields = ['date', 'bp', 'temperature', 'pulse', 'weight', 'complaints', 'diagnosis', 'prescription', 'notes'];
    foreach ($fields as $f) {
        if (isset($data[$f])) $updates[$f] = sanitize($data[$f]);
    }
    if (isset($data['consultation_fee'])) $updates['consultation_fee'] = (float)$data['consultation_fee'];

    $storage->update('opd_visits', $id, $updates);
    jsonResponse(['success' => true, 'message' => 'OPD visit updated']);
}

function deleteOPDVisit($id, $user) {
    requireRole($user, ['admin']);
    $storage = new Storage();
    if (!$storage->getById('opd_visits', $id)) {
        jsonResponse(['success' => false, 'message' => 'OPD visit not found'], 404);
    }

    $storage->delete('opd_visits', $id);
    logActivity('opd_delete', "Deleted OPD visit ID: $id", $user['sub']);
    jsonResponse(['success' => true, 'message' => 'OPD visit deleted']);
}
