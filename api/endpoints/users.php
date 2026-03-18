<?php
/**
 * Users Endpoints
 */

function handleUsersRoute($method, $segments, $user) {
    requireRole($user, ['admin']);

    $id = isset($segments[0]) && is_numeric($segments[0]) ? (int)$segments[0] : null;

    switch ($method) {
        case 'GET':
            if ($id) getUser($id);
            else getUsers();
            break;
        case 'POST':
            createUser();
            break;
        case 'PUT':
            if ($id) updateUser($id, $user);
            else jsonResponse(['success' => false, 'message' => 'ID required'], 400);
            break;
        case 'DELETE':
            if ($id) deleteUser($id, $user);
            else jsonResponse(['success' => false, 'message' => 'ID required'], 400);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
}

function getUsers() {
    $storage = new Storage();
    $users = $storage->getAll('users');

    // Remove passwords
    foreach ($users as &$u) {
        unset($u['password']);
    }

    jsonResponse(['success' => true, 'data' => $users]);
}

function getUser($id) {
    $storage = new Storage();
    $user = $storage->getById('users', $id);
    if (!$user) jsonResponse(['success' => false, 'message' => 'User not found'], 404);

    unset($user['password']);
    jsonResponse(['success' => true, 'data' => $user]);
}

function createUser() {
    $data = getRequestBody();
    $storage = new Storage();

    if (empty($data['username']) || empty($data['password']) || empty($data['role'])) {
        jsonResponse(['success' => false, 'message' => 'Username, password, and role are required'], 400);
    }

    // Check if username exists
    if ($storage->getBy('users', 'username', $data['username'])) {
        jsonResponse(['success' => false, 'message' => 'Username already exists'], 400);
    }

    $user = [
        'username' => sanitize($data['username']),
        'password' => Auth::hashPassword($data['password']),
        'name' => sanitize($data['name'] ?? ''),
        'email' => sanitize($data['email'] ?? ''),
        'role' => sanitize($data['role']),
        'status' => sanitize($data['status'] ?? 'active')
    ];

    $id = $storage->insert('users', $user);
    logActivity('user_create', "Created user: {$user['username']}");
    jsonResponse(['success' => true, 'message' => 'User created', 'id' => $id], 201);
}

function updateUser($id, $currentUser) {
    $storage = new Storage();
    $user = $storage->getById('users', $id);
    if (!$user) jsonResponse(['success' => false, 'message' => 'User not found'], 404);

    $data = getRequestBody();
    $updates = [];

    // Don't allow changing admin username
    if ($user['username'] === 'admin' && isset($data['username']) && $data['username'] !== 'admin') {
        jsonResponse(['success' => false, 'message' => 'Cannot change admin username'], 400);
    }

    foreach (['name', 'email', 'role', 'status'] as $f) {
        if (isset($data[$f])) $updates[$f] = sanitize($data[$f]);
    }

    // Update password if provided
    if (!empty($data['password'])) {
        $updates['password'] = Auth::hashPassword($data['password']);
    }

    $storage->update('users', $id, $updates);
    logActivity('user_update', "Updated user ID: $id", $currentUser['sub']);
    jsonResponse(['success' => true, 'message' => 'User updated']);
}

function deleteUser($id, $currentUser) {
    $storage = new Storage();
    $user = $storage->getById('users', $id);
    if (!$user) jsonResponse(['success' => false, 'message' => 'User not found'], 404);

    // Don't allow deleting admin
    if ($user['username'] === 'admin') {
        jsonResponse(['success' => false, 'message' => 'Cannot delete admin user'], 400);
    }

    // Don't allow deleting self
    if ($user['id'] == $currentUser['sub']) {
        jsonResponse(['success' => false, 'message' => 'Cannot delete your own account'], 400);
    }

    $storage->delete('users', $id);
    logActivity('user_delete', "Deleted user: {$user['username']}", $currentUser['sub']);
    jsonResponse(['success' => true, 'message' => 'User deleted']);
}
