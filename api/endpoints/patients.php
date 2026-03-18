<?php
/**
 * Patients Endpoints
 */

function handlePatientsRoute($method, $segments, $user) {
    $id = isset($segments[0]) && is_numeric($segments[0]) ? (int)$segments[0] : null;
    $action = $segments[1] ?? ($id ? null : ($segments[0] ?? null));

    switch ($method) {
        case 'GET':
            if ($action === 'search') {
                searchPatients($user);
            } elseif ($id) {
                getPatient($id, $user);
            } else {
                getPatients($user);
            }
            break;

        case 'POST':
            createPatient($user);
            break;

        case 'PUT':
            if ($id) {
                updatePatient($id, $user);
            } else {
                jsonResponse(['success' => false, 'message' => 'Patient ID required'], 400);
            }
            break;

        case 'DELETE':
            if ($id) {
                deletePatient($id, $user);
            } else {
                jsonResponse(['success' => false, 'message' => 'Patient ID required'], 400);
            }
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
}

function getPatients($user) {
    $storage = new Storage();
    $patients = $storage->getAll('patients');

    // Calculate age for each patient
    foreach ($patients as &$patient) {
        $patient['age'] = calculateAge($patient['dob'] ?? null);
    }

    // Sort by created_at descending
    usort($patients, function($a, $b) {
        return strtotime($b['created_at'] ?? 0) - strtotime($a['created_at'] ?? 0);
    });

    jsonResponse(['success' => true, 'data' => $patients]);
}

function getPatient($id, $user) {
    $storage = new Storage();
    $patient = $storage->getById('patients', $id);

    if (!$patient) {
        jsonResponse(['success' => false, 'message' => 'Patient not found'], 404);
    }

    $patient['age'] = calculateAge($patient['dob'] ?? null);

    jsonResponse(['success' => true, 'data' => $patient]);
}

function searchPatients($user) {
    $query = sanitize($_GET['q'] ?? '');
    $storage = new Storage();
    $patients = $storage->getAll('patients');

    if (!empty($query)) {
        $query = strtolower($query);
        $patients = array_filter($patients, function($p) use ($query) {
            return strpos(strtolower($p['name'] ?? ''), $query) !== false ||
                   strpos(strtolower($p['patient_id'] ?? ''), $query) !== false ||
                   strpos($p['phone'] ?? '', $query) !== false;
        });
    }

    jsonResponse(['success' => true, 'data' => array_values($patients)]);
}

function createPatient($user) {
    requireRole($user, ['admin', 'receptionist', 'doctor']);

    $data = getRequestBody();
    $storage = new Storage();

    // Validate required fields
    if (empty($data['name']) || empty($data['phone'])) {
        jsonResponse(['success' => false, 'message' => 'Name and phone are required'], 400);
    }

    // Generate patient ID
    $data['patient_id'] = generatePatientId();
    $data['name'] = sanitize($data['name']);
    $data['phone'] = sanitize($data['phone']);
    $data['email'] = sanitize($data['email'] ?? '');
    $data['gender'] = sanitize($data['gender'] ?? '');
    $data['dob'] = sanitize($data['dob'] ?? '');
    $data['blood_group'] = sanitize($data['blood_group'] ?? '');
    $data['address'] = sanitize($data['address'] ?? '');
    $data['emergency_contact_name'] = sanitize($data['emergency_contact_name'] ?? '');
    $data['emergency_contact_phone'] = sanitize($data['emergency_contact_phone'] ?? '');
    $data['allergies'] = sanitize($data['allergies'] ?? '');
    $data['medical_history'] = sanitize($data['medical_history'] ?? '');

    $id = $storage->insert('patients', $data);

    logActivity('patient_create', "Created patient: {$data['name']}", $user['sub']);

    jsonResponse(['success' => true, 'message' => 'Patient created', 'id' => $id], 201);
}

function updatePatient($id, $user) {
    requireRole($user, ['admin', 'receptionist', 'doctor']);

    $storage = new Storage();
    $patient = $storage->getById('patients', $id);

    if (!$patient) {
        jsonResponse(['success' => false, 'message' => 'Patient not found'], 404);
    }

    $data = getRequestBody();

    // Sanitize input
    $updates = [];
    $allowedFields = ['name', 'phone', 'email', 'gender', 'dob', 'blood_group', 'address',
                      'emergency_contact_name', 'emergency_contact_phone', 'allergies', 'medical_history'];

    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updates[$field] = sanitize($data[$field]);
        }
    }

    $storage->update('patients', $id, $updates);

    logActivity('patient_update', "Updated patient ID: $id", $user['sub']);

    jsonResponse(['success' => true, 'message' => 'Patient updated']);
}

function deletePatient($id, $user) {
    requireRole($user, ['admin']);

    $storage = new Storage();
    $patient = $storage->getById('patients', $id);

    if (!$patient) {
        jsonResponse(['success' => false, 'message' => 'Patient not found'], 404);
    }

    $storage->delete('patients', $id);

    logActivity('patient_delete', "Deleted patient: {$patient['name']}", $user['sub']);

    jsonResponse(['success' => true, 'message' => 'Patient deleted']);
}
