<?php
/**
 * IPD Endpoints
 */

function handleIPDRoute($method, $segments, $user) {
    $id = isset($segments[0]) && is_numeric($segments[0]) ? (int)$segments[0] : null;
    $action = $segments[1] ?? ($id ? null : ($segments[0] ?? null));

    switch ($method) {
        case 'GET':
            if ($action === 'beds') getBeds($user);
            elseif ($id) getIPDAdmission($id, $user);
            else getIPDAdmissions($user);
            break;
        case 'POST':
            if ($action === 'discharge' && $id) dischargePatient($id, $user);
            else createIPDAdmission($user);
            break;
        case 'PUT':
            if ($id) updateIPDAdmission($id, $user);
            else jsonResponse(['success' => false, 'message' => 'ID required'], 400);
            break;
        case 'DELETE':
            if ($id) deleteIPDAdmission($id, $user);
            else jsonResponse(['success' => false, 'message' => 'ID required'], 400);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
}

function getIPDAdmissions($user) {
    $storage = new Storage();
    $admissions = $storage->getAll('ipd_admissions');

    foreach ($admissions as &$adm) {
        $patient = $storage->getById('patients', $adm['patient_id'] ?? 0);
        $doctor = $storage->getById('doctors', $adm['doctor_id'] ?? 0);
        $adm['patient_name'] = $patient['name'] ?? 'N/A';
        $adm['doctor_name'] = $doctor['name'] ?? 'N/A';
    }

    usort($admissions, function($a, $b) {
        return strtotime($b['admission_date'] ?? 0) - strtotime($a['admission_date'] ?? 0);
    });

    jsonResponse(['success' => true, 'data' => $admissions]);
}

function getIPDAdmission($id, $user) {
    $storage = new Storage();
    $adm = $storage->getById('ipd_admissions', $id);
    if (!$adm) jsonResponse(['success' => false, 'message' => 'IPD admission not found'], 404);

    $patient = $storage->getById('patients', $adm['patient_id'] ?? 0);
    $doctor = $storage->getById('doctors', $adm['doctor_id'] ?? 0);
    $adm['patient_name'] = $patient['name'] ?? 'N/A';
    $adm['doctor_name'] = $doctor['name'] ?? 'N/A';

    jsonResponse(['success' => true, 'data' => $adm]);
}

function getBeds($user) {
    $storage = new Storage();
    $beds = $storage->getAll('beds');
    jsonResponse(['success' => true, 'data' => $beds]);
}

function createIPDAdmission($user) {
    requireRole($user, ['admin', 'doctor', 'receptionist']);
    $data = getRequestBody();
    $storage = new Storage();

    if (empty($data['patient_id']) || empty($data['doctor_id'])) {
        jsonResponse(['success' => false, 'message' => 'Patient and doctor are required'], 400);
    }

    $adm = [
        'patient_id' => (int)$data['patient_id'],
        'doctor_id' => (int)$data['doctor_id'],
        'admission_date' => sanitize($data['admission_date'] ?? date('Y-m-d')),
        'ward' => sanitize($data['ward'] ?? ''),
        'bed_number' => sanitize($data['bed_number'] ?? ''),
        'admission_reason' => sanitize($data['admission_reason'] ?? ''),
        'diagnosis' => sanitize($data['diagnosis'] ?? ''),
        'status' => 'admitted'
    ];

    // Update bed status
    if (!empty($adm['bed_number'])) {
        $beds = $storage->getAll('beds');
        foreach ($beds as $bed) {
            if ($bed['bed_number'] === $adm['bed_number']) {
                $storage->update('beds', $bed['id'], ['status' => 'occupied']);
                break;
            }
        }
    }

    $id = $storage->insert('ipd_admissions', $adm);
    logActivity('ipd_admit', "Admitted patient ID: {$adm['patient_id']}", $user['sub']);
    jsonResponse(['success' => true, 'message' => 'Patient admitted', 'id' => $id], 201);
}

function dischargePatient($id, $user) {
    requireRole($user, ['admin', 'doctor']);
    $storage = new Storage();
    $adm = $storage->getById('ipd_admissions', $id);
    if (!$adm) jsonResponse(['success' => false, 'message' => 'IPD admission not found'], 404);

    $data = getRequestBody();

    // Free the bed
    if (!empty($adm['bed_number'])) {
        $beds = $storage->getAll('beds');
        foreach ($beds as $bed) {
            if ($bed['bed_number'] === $adm['bed_number']) {
                $storage->update('beds', $bed['id'], ['status' => 'available']);
                break;
            }
        }
    }

    $storage->update('ipd_admissions', $id, [
        'status' => 'discharged',
        'discharge_date' => sanitize($data['discharge_date'] ?? date('Y-m-d')),
        'discharge_notes' => sanitize($data['discharge_notes'] ?? ''),
        'followup_instructions' => sanitize($data['followup_instructions'] ?? '')
    ]);

    logActivity('ipd_discharge', "Discharged patient from IPD ID: $id", $user['sub']);
    jsonResponse(['success' => true, 'message' => 'Patient discharged']);
}

function updateIPDAdmission($id, $user) {
    requireRole($user, ['admin', 'doctor']);
    $storage = new Storage();
    if (!$storage->getById('ipd_admissions', $id)) {
        jsonResponse(['success' => false, 'message' => 'IPD admission not found'], 404);
    }

    $data = getRequestBody();
    $updates = [];
    foreach (['ward', 'bed_number', 'admission_reason', 'diagnosis'] as $f) {
        if (isset($data[$f])) $updates[$f] = sanitize($data[$f]);
    }

    $storage->update('ipd_admissions', $id, $updates);
    jsonResponse(['success' => true, 'message' => 'IPD admission updated']);
}

function deleteIPDAdmission($id, $user) {
    requireRole($user, ['admin']);
    $storage = new Storage();
    $adm = $storage->getById('ipd_admissions', $id);
    if (!$adm) jsonResponse(['success' => false, 'message' => 'IPD admission not found'], 404);

    // Free the bed if occupied
    if (!empty($adm['bed_number']) && $adm['status'] === 'admitted') {
        $beds = $storage->getAll('beds');
        foreach ($beds as $bed) {
            if ($bed['bed_number'] === $adm['bed_number']) {
                $storage->update('beds', $bed['id'], ['status' => 'available']);
                break;
            }
        }
    }

    $storage->delete('ipd_admissions', $id);
    logActivity('ipd_delete', "Deleted IPD admission ID: $id", $user['sub']);
    jsonResponse(['success' => true, 'message' => 'IPD admission deleted']);
}
