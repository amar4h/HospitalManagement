<?php
/**
 * Authentication Endpoints
 */

function handleAuthRoute($method, $segments) {
    $action = $segments[0] ?? '';

    switch ($action) {
        case 'login':
            if ($method === 'POST') {
                handleLogin();
            }
            break;

        case 'logout':
            if ($method === 'POST') {
                handleLogout();
            }
            break;

        case 'profile':
            if ($method === 'GET') {
                handleGetProfile();
            }
            break;

        case 'change-password':
            if ($method === 'POST') {
                handleChangePassword();
            }
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Invalid auth endpoint'], 404);
    }
}

function handleLogin() {
    $data = getRequestBody();
    $username = sanitize($data['username'] ?? '');
    $password = $data['password'] ?? '';

    if (empty($username) || empty($password)) {
        jsonResponse(['success' => false, 'message' => 'Username and password required'], 400);
    }

    $result = Auth::login($username, $password);

    if (!$result) {
        jsonResponse(['success' => false, 'message' => 'Invalid credentials'], 401);
    }

    logActivity('login', "User {$username} logged in", $result['user']['id']);

    jsonResponse([
        'success' => true,
        'token' => $result['token'],
        'user' => $result['user']
    ]);
}

function handleLogout() {
    // With JWT, logout is handled client-side by removing the token
    jsonResponse(['success' => true, 'message' => 'Logged out successfully']);
}

function handleGetProfile() {
    $user = Auth::validateToken();
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    $storage = new Storage();
    $userData = $storage->getById('users', $user['sub']);

    if ($userData) {
        unset($userData['password']);
    }

    jsonResponse(['success' => true, 'data' => $userData]);
}

function handleChangePassword() {
    $user = Auth::validateToken();
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    $data = getRequestBody();
    $currentPassword = $data['current_password'] ?? '';
    $newPassword = $data['new_password'] ?? '';

    if (empty($currentPassword) || empty($newPassword)) {
        jsonResponse(['success' => false, 'message' => 'Current and new password required'], 400);
    }

    if (strlen($newPassword) < 6) {
        jsonResponse(['success' => false, 'message' => 'New password must be at least 6 characters'], 400);
    }

    $storage = new Storage();
    $userData = $storage->getById('users', $user['sub']);

    if (!Auth::verifyPassword($currentPassword, $userData['password'])) {
        jsonResponse(['success' => false, 'message' => 'Current password is incorrect'], 400);
    }

    $storage->update('users', $user['sub'], [
        'password' => Auth::hashPassword($newPassword)
    ]);

    logActivity('password_change', 'Password changed', $user['sub']);

    jsonResponse(['success' => true, 'message' => 'Password updated successfully']);
}
