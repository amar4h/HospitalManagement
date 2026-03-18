<?php
/**
 * Hospital Management System - Configuration
 * Version: 1.0.0
 */

// Application Settings
define('APP_NAME', 'Hospital Management System');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/hospital-management');

// Path Settings
define('ROOT_PATH', dirname(__DIR__) . '/');
define('CONFIG_PATH', ROOT_PATH . 'config/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('CLASSES_PATH', ROOT_PATH . 'classes/');
define('MODULES_PATH', ROOT_PATH . 'modules/');
define('ASSETS_PATH', ROOT_PATH . 'assets/');
define('UPLOADS_PATH', ROOT_PATH . 'uploads/');
define('DATA_PATH', ROOT_PATH . 'data/');

// Session Settings
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_NAME', 'HMS_SESSION');

// Hospital Information
define('HOSPITAL_NAME', 'City Multispeciality Hospital');
define('HOSPITAL_ADDRESS', '123 Healthcare Avenue, Medical District');
define('HOSPITAL_PHONE', '+1-234-567-8900');
define('HOSPITAL_EMAIL', 'info@cityhospital.com');
define('HOSPITAL_LOGO', 'assets/images/logo.png');

// Currency Settings
define('CURRENCY_SYMBOL', '$');
define('CURRENCY_CODE', 'USD');

// Date/Time Format
define('DATE_FORMAT', 'Y-m-d');
define('TIME_FORMAT', 'H:i');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'd M Y');
define('DISPLAY_DATETIME_FORMAT', 'd M Y h:i A');

// Pagination
define('RECORDS_PER_PAGE', 20);

// File Upload Settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Set timezone
date_default_timezone_set('UTC');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once CLASSES_PATH . 'LocalStorage.php';
require_once INCLUDES_PATH . 'functions.php';
require_once INCLUDES_PATH . 'auth.php';
