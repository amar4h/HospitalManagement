<?php
/**
 * Authentication Functions
 */

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
}

/**
 * Get current logged in user
 */
function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user']['id'] ?? null;
}

/**
 * Get current user role
 */
function getCurrentUserRole() {
    return $_SESSION['user']['role'] ?? null;
}

/**
 * Require authentication
 */
function requireAuth() {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Please login to continue');
        redirect('index.php?page=login');
    }
}

/**
 * Require specific role(s)
 */
function requireRole($roles) {
    requireAuth();

    if (is_string($roles)) {
        $roles = [$roles];
    }

    $userRole = getCurrentUserRole();

    // Admin always has access
    if ($userRole === 'admin') {
        return true;
    }

    if (!in_array($userRole, $roles)) {
        setFlashMessage('error', 'You do not have permission to access this page');
        redirect('index.php?page=dashboard');
    }

    return true;
}

/**
 * Attempt login
 */
function attemptLogin($username, $password) {
    $storage = getStorage();
    $user = $storage->getByField('users', 'username', $username);

    if (!$user) {
        return ['success' => false, 'message' => 'Invalid username or password'];
    }

    if ($user['status'] !== 'active') {
        return ['success' => false, 'message' => 'Your account is inactive. Please contact administrator.'];
    }

    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid username or password'];
    }

    // Set session
    $_SESSION['user'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
        'doctor_id' => $user['doctor_id'] ?? null
    ];

    // Log activity
    logActivity('login', 'User logged in successfully');

    // Update last login
    $storage->update('users', $user['id'], ['last_login' => date('Y-m-d H:i:s')]);

    return ['success' => true, 'message' => 'Login successful'];
}

/**
 * Logout user
 */
function logout() {
    if (isLoggedIn()) {
        logActivity('logout', 'User logged out');
    }

    session_unset();
    session_destroy();
    session_start();

    setFlashMessage('success', 'You have been logged out successfully');
}

/**
 * Change password
 */
function changePassword($userId, $currentPassword, $newPassword) {
    $storage = getStorage();
    $user = $storage->getById('users', $userId);

    if (!$user) {
        return ['success' => false, 'message' => 'User not found'];
    }

    if (!password_verify($currentPassword, $user['password'])) {
        return ['success' => false, 'message' => 'Current password is incorrect'];
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $storage->update('users', $userId, ['password' => $hashedPassword]);

    logActivity('password_change', 'Password changed successfully');

    return ['success' => true, 'message' => 'Password changed successfully'];
}

/**
 * Create new user
 */
function createUser($data) {
    $storage = getStorage();

    // Check if username exists
    $existing = $storage->getByField('users', 'username', $data['username']);
    if ($existing) {
        return ['success' => false, 'message' => 'Username already exists'];
    }

    // Check if email exists
    $existing = $storage->getByField('users', 'email', $data['email']);
    if ($existing) {
        return ['success' => false, 'message' => 'Email already exists'];
    }

    // Hash password
    $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    $data['status'] = $data['status'] ?? 'active';

    $id = $storage->insert('users', $data);

    logActivity('user_create', 'Created new user: ' . $data['username']);

    return ['success' => true, 'message' => 'User created successfully', 'id' => $id];
}

/**
 * Update user
 */
function updateUser($id, $data) {
    $storage = getStorage();

    // If password is provided, hash it
    if (!empty($data['password'])) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    } else {
        unset($data['password']);
    }

    $storage->update('users', $id, $data);

    logActivity('user_update', 'Updated user ID: ' . $id);

    return ['success' => true, 'message' => 'User updated successfully'];
}

/**
 * Delete user
 */
function deleteUser($id) {
    $storage = getStorage();

    // Prevent self-deletion
    if ($id == getCurrentUserId()) {
        return ['success' => false, 'message' => 'You cannot delete your own account'];
    }

    // Prevent deletion of last admin
    $user = $storage->getById('users', $id);
    if ($user['role'] === 'admin') {
        $admins = $storage->getAll('users', ['role' => 'admin', 'status' => 'active']);
        if (count($admins) <= 1) {
            return ['success' => false, 'message' => 'Cannot delete the last admin account'];
        }
    }

    $storage->delete('users', $id);

    logActivity('user_delete', 'Deleted user ID: ' . $id);

    return ['success' => true, 'message' => 'User deleted successfully'];
}

/**
 * Get menu items based on role
 */
function getMenuItems() {
    $role = getCurrentUserRole();

    $allMenus = [
        'dashboard' => ['icon' => 'bi-speedometer2', 'label' => 'Dashboard', 'roles' => ['all']],
        'patients' => ['icon' => 'bi-people', 'label' => 'Patients', 'roles' => ['admin', 'doctor', 'nurse', 'receptionist']],
        'doctors' => ['icon' => 'bi-person-badge', 'label' => 'Doctors', 'roles' => ['admin', 'receptionist']],
        'appointments' => ['icon' => 'bi-calendar-check', 'label' => 'Appointments', 'roles' => ['admin', 'doctor', 'nurse', 'receptionist']],
        'opd' => ['icon' => 'bi-clipboard2-pulse', 'label' => 'OPD', 'roles' => ['admin', 'doctor', 'nurse', 'receptionist']],
        'ipd' => ['icon' => 'bi-hospital', 'label' => 'IPD', 'roles' => ['admin', 'doctor', 'nurse', 'receptionist']],
        'surgery' => ['icon' => 'bi-heart-pulse', 'label' => 'Surgery', 'roles' => ['admin', 'doctor', 'nurse']],
        'pharmacy' => ['icon' => 'bi-capsule', 'label' => 'Pharmacy', 'roles' => ['admin', 'pharmacist', 'doctor']],
        'laboratory' => ['icon' => 'bi-droplet', 'label' => 'Laboratory', 'roles' => ['admin', 'lab_technician', 'doctor']],
        'billing' => ['icon' => 'bi-receipt', 'label' => 'Billing', 'roles' => ['admin', 'accountant', 'receptionist']],
        'reports' => ['icon' => 'bi-graph-up', 'label' => 'Reports', 'roles' => ['admin', 'accountant']],
        'users' => ['icon' => 'bi-person-gear', 'label' => 'Users', 'roles' => ['admin']],
        'settings' => ['icon' => 'bi-gear', 'label' => 'Settings', 'roles' => ['admin']]
    ];

    $menus = [];
    foreach ($allMenus as $key => $menu) {
        if (in_array('all', $menu['roles']) || $role === 'admin' || in_array($role, $menu['roles'])) {
            $menus[$key] = $menu;
        }
    }

    return $menus;
}
