<?php
/**
 * Appointments Endpoints
 */

function handleAppointmentsRoute($method, $segments, $user) {
    $id = isset($segments[0]) && is_numeric($segments[0]) ? (int)$segments[0] : null;

    switch ($method) {
        case 'GET':
            if ($id) getAppointment($id, $user);
            else getAppointments($user);
            break;
        case 'POST':
            createAppointment($user);
            break;
        case 'PUT':
            if ($id) updateAppointment($id, $user);
            else jsonResponse(['success' => false, 'message' => 'ID required'], 400);
            break;
        case 'DELETE':
            if ($id) deleteAppointment($id, $user);
            else jsonResponse(['success' => false, 'message' => 'ID required'], 400);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
}

function getAppointments($user) {
    $storage = new Storage();
    $appointments = $storage->getAll('appointments');

    foreach ($appointments as &$apt) {
        $patient = $storage->getById('patients', $apt['patient_id'] ?? 0);
        $doctor = $storage->getById('doctors', $apt['doctor_id'] ?? 0);
        $apt['patient_name'] = $patient['name'] ?? 'N/A';
        $apt['doctor_name'] = $doctor['name'] ?? 'N/A';
    }

    usort($appointments, function($a, $b) {
        $dateCompare = strcmp($b['date'] ?? '', $a['date'] ?? '');
        return $dateCompare !== 0 ? $dateCompare : strcmp($a['time'] ?? '', $b['time'] ?? '');
    });

    jsonResponse(['success' => true, 'data' => $appointments]);
}

function getAppointment($id, $user) {
    $storage = new Storage();
    $apt = $storage->getById('appointments', $id);
    if (!$apt) jsonResponse(['success' => false, 'message' => 'Appointment not found'], 404);

    $patient = $storage->getById('patients', $apt['patient_id'] ?? 0);
    $doctor = $storage->getById('doctors', $apt['doctor_id'] ?? 0);
    $apt['patient_name'] = $patient['name'] ?? 'N/A';
    $apt['doctor_name'] = $doctor['name'] ?? 'N/A';

    jsonResponse(['success' => true, 'data' => $apt]);
}

function createAppointment($user) {
    requireRole($user, ['admin', 'receptionist', 'doctor']);
    $data = getRequestBody();
    $storage = new Storage();

    if (empty($data['patient_id']) || empty($data['doctor_id']) || empty($data['date'])) {
        jsonResponse(['success' => false, 'message' => 'Patient, doctor, and date are required'], 400);
    }

    $apt = [
        'patient_id' => (int)$data['patient_id'],
        'doctor_id' => (int)$data['doctor_id'],
        'date' => sanitize($data['date']),
        'time' => sanitize($data['time'] ?? ''),
        'type' => sanitize($data['type'] ?? 'General'),
        'notes' => sanitize($data['notes'] ?? ''),
        'status' => sanitize($data['status'] ?? 'scheduled')
    ];

    $id = $storage->insert('appointments', $apt);
    logActivity('appointment_create', "Created appointment for patient ID: {$apt['patient_id']}", $user['sub']);
    jsonResponse(['success' => true, 'message' => 'Appointment created', 'id' => $id], 201);
}

function updateAppointment($id, $user) {
    requireRole($user, ['admin', 'receptionist', 'doctor']);
    $storage = new Storage();
    if (!$storage->getById('appointments', $id)) {
        jsonResponse(['success' => false, 'message' => 'Appointment not found'], 404);
    }

    $data = getRequestBody();
    $updates = [];
    foreach (['patient_id', 'doctor_id'] as $f) {
        if (isset($data[$f])) $updates[$f] = (int)$data[$f];
    }
    foreach (['date', 'time', 'type', 'notes', 'status'] as $f) {
        if (isset($data[$f])) $updates[$f] = sanitize($data[$f]);
    }

    $storage->update('appointments', $id, $updates);
    logActivity('appointment_update', "Updated appointment ID: $id", $user['sub']);
    jsonResponse(['success' => true, 'message' => 'Appointment updated']);
}

function deleteAppointment($id, $user) {
    requireRole($user, ['admin', 'receptionist']);
    $storage = new Storage();
    if (!$storage->getById('appointments', $id)) {
        jsonResponse(['success' => false, 'message' => 'Appointment not found'], 404);
    }

    $storage->delete('appointments', $id);
    logActivity('appointment_delete', "Deleted appointment ID: $id", $user['sub']);
    jsonResponse(['success' => true, 'message' => 'Appointment deleted']);
}
