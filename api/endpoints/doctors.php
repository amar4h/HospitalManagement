<?php
/**
 * Doctors Endpoints
 */

function handleDoctorsRoute($method, $segments, $user) {
    $id = isset($segments[0]) && is_numeric($segments[0]) ? (int)$segments[0] : null;

    switch ($method) {
        case 'GET':
            if ($id) {
                getDoctor($id, $user);
            } else {
                getDoctors($user);
            }
            break;
        case 'POST':
            createDoctor($user);
            break;
        case 'PUT':
            if ($id) updateDoctor($id, $user);
            else jsonResponse(['success' => false, 'message' => 'ID required'], 400);
            break;
        case 'DELETE':
            if ($id) deleteDoctor($id, $user);
            else jsonResponse(['success' => false, 'message' => 'ID required'], 400);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
}

function getDoctors($user) {
    $storage = new Storage();
    $doctors = $storage->getAll('doctors');
    usort($doctors, function($a, $b) { return strcmp($a['name'] ?? '', $b['name'] ?? ''); });
    jsonResponse(['success' => true, 'data' => $doctors]);
}

function getDoctor($id, $user) {
    $storage = new Storage();
    $doctor = $storage->getById('doctors', $id);
    if (!$doctor) jsonResponse(['success' => false, 'message' => 'Doctor not found'], 404);
    jsonResponse(['success' => true, 'data' => $doctor]);
}

function createDoctor($user) {
    requireRole($user, ['admin']);
    $data = getRequestBody();
    $storage = new Storage();

    if (empty($data['name'])) {
        jsonResponse(['success' => false, 'message' => 'Name is required'], 400);
    }

    $doctor = [
        'name' => sanitize($data['name']),
        'specialization' => sanitize($data['specialization'] ?? ''),
        'qualification' => sanitize($data['qualification'] ?? ''),
        'phone' => sanitize($data['phone'] ?? ''),
        'email' => sanitize($data['email'] ?? ''),
        'consultation_fee' => (float)($data['consultation_fee'] ?? 0),
        'status' => sanitize($data['status'] ?? 'active')
    ];

    $id = $storage->insert('doctors', $doctor);
    logActivity('doctor_create', "Created doctor: {$doctor['name']}", $user['sub']);
    jsonResponse(['success' => true, 'message' => 'Doctor created', 'id' => $id], 201);
}

function updateDoctor($id, $user) {
    requireRole($user, ['admin']);
    $storage = new Storage();
    if (!$storage->getById('doctors', $id)) {
        jsonResponse(['success' => false, 'message' => 'Doctor not found'], 404);
    }

    $data = getRequestBody();
    $updates = [];
    foreach (['name', 'specialization', 'qualification', 'phone', 'email', 'status'] as $field) {
        if (isset($data[$field])) $updates[$field] = sanitize($data[$field]);
    }
    if (isset($data['consultation_fee'])) $updates['consultation_fee'] = (float)$data['consultation_fee'];

    $storage->update('doctors', $id, $updates);
    logActivity('doctor_update', "Updated doctor ID: $id", $user['sub']);
    jsonResponse(['success' => true, 'message' => 'Doctor updated']);
}

function deleteDoctor($id, $user) {
    requireRole($user, ['admin']);
    $storage = new Storage();
    $doctor = $storage->getById('doctors', $id);
    if (!$doctor) jsonResponse(['success' => false, 'message' => 'Doctor not found'], 404);

    $storage->delete('doctors', $id);
    logActivity('doctor_delete', "Deleted doctor: {$doctor['name']}", $user['sub']);
    jsonResponse(['success' => true, 'message' => 'Doctor deleted']);
}
