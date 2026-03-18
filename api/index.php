<?php
/**
 * Hospital Management System - REST API
 * Main entry point / Router
 */

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Include configuration and helpers
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/Storage.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/helpers.php';

// Set headers
header('Content-Type: application/json');

// Handle CORS
handleCORS();

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Remove query string and base path
$uri = parse_url($uri, PHP_URL_PATH);
$basePath = '/api';
if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}
$uri = trim($uri, '/');

// Parse path segments
$segments = $uri ? explode('/', $uri) : [];

// Route the request
try {
    // Auth routes (no authentication required for login)
    if ($segments[0] === 'auth') {
        require_once __DIR__ . '/endpoints/auth.php';
        handleAuthRoute($method, array_slice($segments, 1));
        exit;
    }

    // All other routes require authentication
    $user = Auth::validateToken();
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    // Route to appropriate endpoint
    switch ($segments[0] ?? '') {
        case 'dashboard':
            require_once __DIR__ . '/endpoints/dashboard.php';
            handleDashboardRoute($method, array_slice($segments, 1), $user);
            break;

        case 'patients':
            require_once __DIR__ . '/endpoints/patients.php';
            handlePatientsRoute($method, array_slice($segments, 1), $user);
            break;

        case 'doctors':
            require_once __DIR__ . '/endpoints/doctors.php';
            handleDoctorsRoute($method, array_slice($segments, 1), $user);
            break;

        case 'appointments':
            require_once __DIR__ . '/endpoints/appointments.php';
            handleAppointmentsRoute($method, array_slice($segments, 1), $user);
            break;

        case 'opd':
            require_once __DIR__ . '/endpoints/opd.php';
            handleOPDRoute($method, array_slice($segments, 1), $user);
            break;

        case 'ipd':
            require_once __DIR__ . '/endpoints/ipd.php';
            handleIPDRoute($method, array_slice($segments, 1), $user);
            break;

        case 'surgery':
            require_once __DIR__ . '/endpoints/surgery.php';
            handleSurgeryRoute($method, array_slice($segments, 1), $user);
            break;

        case 'pharmacy':
            require_once __DIR__ . '/endpoints/pharmacy.php';
            handlePharmacyRoute($method, array_slice($segments, 1), $user);
            break;

        case 'laboratory':
            require_once __DIR__ . '/endpoints/laboratory.php';
            handleLaboratoryRoute($method, array_slice($segments, 1), $user);
            break;

        case 'billing':
            require_once __DIR__ . '/endpoints/billing.php';
            handleBillingRoute($method, array_slice($segments, 1), $user);
            break;

        case 'reports':
            require_once __DIR__ . '/endpoints/reports.php';
            handleReportsRoute($method, array_slice($segments, 1), $user);
            break;

        case 'users':
            require_once __DIR__ . '/endpoints/users.php';
            handleUsersRoute($method, array_slice($segments, 1), $user);
            break;

        case 'settings':
            require_once __DIR__ . '/endpoints/settings.php';
            handleSettingsRoute($method, array_slice($segments, 1), $user);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Endpoint not found'], 404);
    }

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
