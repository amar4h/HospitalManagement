<?php
/**
 * Helper Functions
 */

/**
 * Get LocalStorage instance
 */
function getStorage() {
    static $storage = null;
    if ($storage === null) {
        $storage = new LocalStorage();
    }
    return $storage;
}

/**
 * Sanitize input
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to URL
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Set flash message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Display flash message HTML
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $alertClass = match($flash['type']) {
            'success' => 'alert-success',
            'error' => 'alert-danger',
            'warning' => 'alert-warning',
            'info' => 'alert-info',
            default => 'alert-secondary'
        };
        echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
        echo $flash['message'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
    }
}

/**
 * Format date for display
 */
function formatDate($date, $format = null) {
    if (empty($date)) return '-';
    $format = $format ?? DISPLAY_DATE_FORMAT;
    return date($format, strtotime($date));
}

/**
 * Format datetime for display
 */
function formatDateTime($datetime, $format = null) {
    if (empty($datetime)) return '-';
    $format = $format ?? DISPLAY_DATETIME_FORMAT;
    return date($format, strtotime($datetime));
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return CURRENCY_SYMBOL . number_format($amount, 2);
}

/**
 * Calculate age from date of birth
 */
function calculateAge($dob) {
    if (empty($dob)) return '-';
    $birthDate = new DateTime($dob);
    $today = new DateTime();
    $age = $today->diff($birthDate);
    return $age->y;
}

/**
 * Generate random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

/**
 * Get status badge HTML
 */
function getStatusBadge($status) {
    $badges = [
        'active' => '<span class="badge bg-success">Active</span>',
        'inactive' => '<span class="badge bg-secondary">Inactive</span>',
        'pending' => '<span class="badge bg-warning">Pending</span>',
        'confirmed' => '<span class="badge bg-primary">Confirmed</span>',
        'completed' => '<span class="badge bg-success">Completed</span>',
        'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
        'admitted' => '<span class="badge bg-info">Admitted</span>',
        'discharged' => '<span class="badge bg-success">Discharged</span>',
        'available' => '<span class="badge bg-success">Available</span>',
        'occupied' => '<span class="badge bg-danger">Occupied</span>',
        'maintenance' => '<span class="badge bg-warning">Maintenance</span>',
        'paid' => '<span class="badge bg-success">Paid</span>',
        'unpaid' => '<span class="badge bg-danger">Unpaid</span>',
        'partial' => '<span class="badge bg-warning">Partial</span>',
        'in_progress' => '<span class="badge bg-info">In Progress</span>',
        'scheduled' => '<span class="badge bg-primary">Scheduled</span>',
        'sample_collected' => '<span class="badge bg-info">Sample Collected</span>',
        'result_pending' => '<span class="badge bg-warning">Result Pending</span>',
        'result_available' => '<span class="badge bg-success">Result Available</span>'
    ];
    return $badges[strtolower($status)] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
}

/**
 * Get role display name
 */
function getRoleDisplayName($role) {
    $roles = [
        'admin' => 'Administrator',
        'doctor' => 'Doctor',
        'nurse' => 'Nurse',
        'receptionist' => 'Receptionist',
        'pharmacist' => 'Pharmacist',
        'lab_technician' => 'Lab Technician',
        'accountant' => 'Accountant'
    ];
    return $roles[$role] ?? ucfirst($role);
}

/**
 * Check if user has permission
 */
function hasPermission($permission) {
    if (!isLoggedIn()) return false;

    $role = $_SESSION['user']['role'];

    // Admin has all permissions
    if ($role === 'admin') return true;

    // Define role permissions
    $permissions = [
        'doctor' => ['view_patients', 'view_appointments', 'manage_opd', 'view_lab_results', 'view_pharmacy'],
        'nurse' => ['view_patients', 'view_appointments', 'manage_vitals', 'view_ipd'],
        'receptionist' => ['manage_patients', 'manage_appointments', 'view_doctors', 'create_invoices'],
        'pharmacist' => ['manage_pharmacy', 'view_prescriptions', 'dispense_medicine'],
        'lab_technician' => ['manage_lab', 'view_lab_orders', 'enter_results'],
        'accountant' => ['manage_billing', 'view_invoices', 'manage_payments', 'view_reports']
    ];

    return isset($permissions[$role]) && in_array($permission, $permissions[$role]);
}

/**
 * Log activity
 */
function logActivity($action, $details = '') {
    if (!isLoggedIn()) return;

    $storage = getStorage();
    $log = [
        'user_id' => $_SESSION['user']['id'],
        'user_name' => $_SESSION['user']['name'],
        'action' => $action,
        'details' => $details,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
    ];
    $storage->insert('activity_logs', $log);
}

/**
 * Get dashboard stats
 */
function getDashboardStats() {
    $storage = getStorage();
    $today = date('Y-m-d');

    return [
        'total_patients' => $storage->count('patients'),
        'total_doctors' => $storage->count('doctors', ['status' => 'active']),
        'today_appointments' => $storage->count('appointments', ['date' => $today]),
        'today_opd' => $storage->count('opd_visits', ['date' => $today]),
        'ipd_patients' => $storage->count('ipd_admissions', ['status' => 'admitted']),
        'available_beds' => $storage->count('beds', ['status' => 'available']),
        'pending_lab' => $storage->count('lab_orders', ['status' => 'pending']),
        'low_stock_medicines' => count(array_filter($storage->getAll('medicines'), function($m) {
            return $m['stock'] <= $m['reorder_level'];
        }))
    ];
}

/**
 * Pagination helper
 */
function paginate($data, $page = 1, $perPage = null) {
    $perPage = $perPage ?? RECORDS_PER_PAGE;
    $total = count($data);
    $totalPages = ceil($total / $perPage);
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;

    return [
        'data' => array_slice($data, $offset, $perPage),
        'current_page' => $page,
        'per_page' => $perPage,
        'total' => $total,
        'total_pages' => $totalPages,
        'has_prev' => $page > 1,
        'has_next' => $page < $totalPages
    ];
}

/**
 * Render pagination HTML
 */
function renderPagination($pagination, $baseUrl) {
    if ($pagination['total_pages'] <= 1) return '';

    $html = '<nav><ul class="pagination justify-content-center">';

    // Previous
    if ($pagination['has_prev']) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . ($pagination['current_page'] - 1) . '">Previous</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
    }

    // Page numbers
    for ($i = 1; $i <= $pagination['total_pages']; $i++) {
        if ($i == $pagination['current_page']) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $i . '">' . $i . '</a></li>';
        }
    }

    // Next
    if ($pagination['has_next']) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . ($pagination['current_page'] + 1) . '">Next</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Next</span></li>';
    }

    $html .= '</ul></nav>';
    return $html;
}

/**
 * Get time slots for appointments
 */
function getTimeSlots($startTime = '09:00', $endTime = '17:00', $interval = 30) {
    $slots = [];
    $current = strtotime($startTime);
    $end = strtotime($endTime);

    while ($current < $end) {
        $slots[] = date('H:i', $current);
        $current = strtotime("+{$interval} minutes", $current);
    }

    return $slots;
}

/**
 * Check if time slot is available
 */
function isSlotAvailable($doctorId, $date, $time) {
    $storage = getStorage();
    $appointments = $storage->getAll('appointments', [
        'doctor_id' => $doctorId,
        'date' => $date,
        'time' => $time
    ]);

    foreach ($appointments as $apt) {
        if ($apt['status'] !== 'cancelled') {
            return false;
        }
    }

    return true;
}

/**
 * Get blood group options
 */
function getBloodGroups() {
    return ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
}

/**
 * Get gender options
 */
function getGenders() {
    return ['Male', 'Female', 'Other'];
}

/**
 * Get marital status options
 */
function getMaritalStatuses() {
    return ['Single', 'Married', 'Divorced', 'Widowed'];
}

/**
 * CSRF Token generation
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF Token validation
 */
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF hidden field
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCsrfToken() . '">';
}
