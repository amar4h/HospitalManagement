<?php
/**
 * Helper Functions
 */

/**
 * Handle CORS headers
 */
function handleCORS() {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    // Check if origin is allowed
    if (in_array($origin, ALLOWED_ORIGINS)) {
        header("Access-Control-Allow-Origin: $origin");
    } else {
        // For development, allow all origins (remove in production)
        header("Access-Control-Allow-Origin: *");
    }

    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Max-Age: 86400");

    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

/**
 * Send JSON response
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

/**
 * Get JSON request body
 */
function getRequestBody() {
    $json = file_get_contents('php://input');
    return json_decode($json, true) ?? [];
}

/**
 * Sanitize input
 */
function sanitize($value) {
    if (is_array($value)) {
        return array_map('sanitize', $value);
    }
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate unique ID
 */
function generateId() {
    return time() . mt_rand(1000, 9999);
}

/**
 * Generate patient ID
 */
function generatePatientId() {
    return 'PT' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

/**
 * Generate invoice number
 */
function generateInvoiceNumber() {
    return 'INV' . date('Ymd') . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
}

/**
 * Check if user has required role
 */
function requireRole($user, $roles) {
    if (!is_array($roles)) {
        $roles = [$roles];
    }

    if (!in_array($user['role'], $roles) && $user['role'] !== 'admin') {
        jsonResponse(['success' => false, 'message' => 'Access denied'], 403);
    }
}

/**
 * Calculate age from date of birth
 */
function calculateAge($dob) {
    if (empty($dob)) return null;
    $birthDate = new DateTime($dob);
    $today = new DateTime();
    return $birthDate->diff($today)->y;
}

/**
 * Log activity
 */
function logActivity($action, $description, $userId = null) {
    $storage = new Storage();
    $storage->insert('activity_logs', [
        'action' => $action,
        'description' => $description,
        'user_id' => $userId,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        'created_at' => date('Y-m-d H:i:s')
    ]);
}
