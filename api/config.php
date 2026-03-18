<?php
/**
 * API Configuration
 */

// Application settings
define('APP_NAME', 'Hospital Management System');
define('APP_VERSION', '1.0.0');

// JWT Secret - Auto-generated unique key
define('JWT_SECRET', 'hms-2024-' . md5(__DIR__) . '-secure-key');
define('JWT_EXPIRY', 86400); // 24 hours in seconds

// CORS settings - Same domain deployment (no CORS needed)
// If you need cross-domain access, add your domains here
define('ALLOWED_ORIGINS', ['*']);

// Storage path (for JSON file storage)
define('STORAGE_PATH', __DIR__ . '/data/');

// Ensure data directory exists
if (!is_dir(STORAGE_PATH)) {
    mkdir(STORAGE_PATH, 0755, true);
}

// Timezone
date_default_timezone_set('Asia/Kolkata');
