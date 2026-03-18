<?php
/**
 * Settings Endpoints
 */

function handleSettingsRoute($method, $segments, $user) {
    $action = $segments[0] ?? '';
    $id = isset($segments[1]) && is_numeric($segments[1]) ? (int)$segments[1] : null;

    if ($action === 'departments') {
        switch ($method) {
            case 'GET':
                getDepartments();
                break;
            case 'POST':
                requireRole($user, ['admin']);
                createDepartment($user);
                break;
            case 'DELETE':
                requireRole($user, ['admin']);
                if ($id) deleteDepartment($id, $user);
                else jsonResponse(['success' => false, 'message' => 'ID required'], 400);
                break;
            default:
                jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }
    } else {
        // General settings
        switch ($method) {
            case 'GET':
                getSettings();
                break;
            case 'PUT':
                requireRole($user, ['admin']);
                updateSettings($user);
                break;
            default:
                jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }
    }
}

function getSettings() {
    $storage = new Storage();
    $settings = $storage->getAll('settings');

    // Settings is stored as a single object, not array
    if (is_array($settings) && !isset($settings['hospital_name'])) {
        $settings = $settings[0] ?? [];
    }

    jsonResponse(['success' => true, 'data' => $settings]);
}

function updateSettings($user) {
    $data = getRequestBody();
    $storage = new Storage();

    $settings = [
        'hospital_name' => sanitize($data['hospital_name'] ?? ''),
        'address' => sanitize($data['address'] ?? ''),
        'phone' => sanitize($data['phone'] ?? ''),
        'email' => sanitize($data['email'] ?? ''),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    // Save settings (overwrite the file)
    file_put_contents(STORAGE_PATH . 'settings.json', json_encode($settings, JSON_PRETTY_PRINT));

    logActivity('settings_update', 'Updated system settings', $user['sub']);
    jsonResponse(['success' => true, 'message' => 'Settings updated']);
}

function getDepartments() {
    $storage = new Storage();
    $departments = $storage->getAll('departments');
    jsonResponse(['success' => true, 'data' => $departments]);
}

function createDepartment($user) {
    $data = getRequestBody();
    $storage = new Storage();

    if (empty($data['name'])) {
        jsonResponse(['success' => false, 'message' => 'Department name is required'], 400);
    }

    $department = [
        'name' => sanitize($data['name']),
        'description' => sanitize($data['description'] ?? ''),
        'status' => 'active'
    ];

    $id = $storage->insert('departments', $department);
    logActivity('department_create', "Created department: {$department['name']}", $user['sub']);
    jsonResponse(['success' => true, 'message' => 'Department created', 'id' => $id], 201);
}

function deleteDepartment($id, $user) {
    $storage = new Storage();
    $dept = $storage->getById('departments', $id);
    if (!$dept) jsonResponse(['success' => false, 'message' => 'Department not found'], 404);

    $storage->delete('departments', $id);
    logActivity('department_delete', "Deleted department: {$dept['name']}", $user['sub']);
    jsonResponse(['success' => true, 'message' => 'Department deleted']);
}
