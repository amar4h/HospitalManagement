<?php
/**
 * Hospital Management System
 * Main Entry Point / Router
 */

require_once 'config/config.php';

// Handle logout action
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logout();
    redirect('index.php?page=login');
}

// Get current page
$page = $_GET['page'] ?? 'login';

// If logged in and trying to access login page, redirect to dashboard
if (isLoggedIn() && $page === 'login') {
    redirect('index.php?page=dashboard');
}

// If not logged in and not on login page, redirect to login
if (!isLoggedIn() && $page !== 'login') {
    redirect('index.php?page=login');
}

// Define valid pages
$validPages = [
    'login', 'dashboard', 'profile', 'change-password',
    'patients', 'patient-view', 'patient-add', 'patient-edit',
    'doctors', 'doctor-view', 'doctor-add', 'doctor-edit',
    'appointments', 'appointment-add', 'appointment-edit',
    'opd', 'opd-visit', 'opd-add',
    'ipd', 'ipd-admission', 'ipd-add', 'ipd-discharge',
    'surgery', 'surgery-add', 'surgery-edit',
    'pharmacy', 'medicine-add', 'medicine-edit', 'dispense',
    'laboratory', 'lab-order-add', 'lab-result',
    'billing', 'invoice-add', 'invoice-view', 'payment',
    'reports', 'users', 'user-add', 'user-edit', 'settings'
];

// Validate page
if (!in_array($page, $validPages)) {
    $page = isLoggedIn() ? 'dashboard' : 'login';
}

// Set page title
$pageTitles = [
    'login' => 'Login',
    'dashboard' => 'Dashboard',
    'profile' => 'My Profile',
    'change-password' => 'Change Password',
    'patients' => 'Patient Management',
    'patient-view' => 'Patient Details',
    'patient-add' => 'Add Patient',
    'patient-edit' => 'Edit Patient',
    'doctors' => 'Doctor Management',
    'doctor-view' => 'Doctor Details',
    'doctor-add' => 'Add Doctor',
    'doctor-edit' => 'Edit Doctor',
    'appointments' => 'Appointments',
    'appointment-add' => 'New Appointment',
    'appointment-edit' => 'Edit Appointment',
    'opd' => 'OPD Management',
    'opd-visit' => 'OPD Visit Details',
    'opd-add' => 'New OPD Visit',
    'ipd' => 'IPD Management',
    'ipd-admission' => 'Admission Details',
    'ipd-add' => 'New Admission',
    'ipd-discharge' => 'Discharge Patient',
    'surgery' => 'Surgery Management',
    'surgery-add' => 'Schedule Surgery',
    'surgery-edit' => 'Edit Surgery',
    'pharmacy' => 'Pharmacy',
    'medicine-add' => 'Add Medicine',
    'medicine-edit' => 'Edit Medicine',
    'dispense' => 'Dispense Medicine',
    'laboratory' => 'Laboratory',
    'lab-order-add' => 'New Lab Order',
    'lab-result' => 'Lab Result Entry',
    'billing' => 'Billing',
    'invoice-add' => 'New Invoice',
    'invoice-view' => 'Invoice Details',
    'payment' => 'Record Payment',
    'reports' => 'Reports',
    'users' => 'User Management',
    'user-add' => 'Add User',
    'user-edit' => 'Edit User',
    'settings' => 'Settings'
];

$pageTitle = ($pageTitles[$page] ?? 'Page') . ' - ' . APP_NAME;

// Include header
include INCLUDES_PATH . 'header.php';

// Include page content
$pageFile = MODULES_PATH . str_replace('-', '/', $page) . '.php';

// Handle sub-pages with directories
$pageMap = [
    'login' => 'auth/login.php',
    'dashboard' => 'dashboard/index.php',
    'profile' => 'auth/profile.php',
    'change-password' => 'auth/change-password.php',
    'patients' => 'patients/index.php',
    'patient-view' => 'patients/view.php',
    'patient-add' => 'patients/add.php',
    'patient-edit' => 'patients/edit.php',
    'doctors' => 'doctors/index.php',
    'doctor-view' => 'doctors/view.php',
    'doctor-add' => 'doctors/add.php',
    'doctor-edit' => 'doctors/edit.php',
    'appointments' => 'appointments/index.php',
    'appointment-add' => 'appointments/add.php',
    'appointment-edit' => 'appointments/edit.php',
    'opd' => 'opd/index.php',
    'opd-visit' => 'opd/view.php',
    'opd-add' => 'opd/add.php',
    'ipd' => 'ipd/index.php',
    'ipd-admission' => 'ipd/view.php',
    'ipd-add' => 'ipd/add.php',
    'ipd-discharge' => 'ipd/discharge.php',
    'surgery' => 'surgery/index.php',
    'surgery-add' => 'surgery/add.php',
    'surgery-edit' => 'surgery/edit.php',
    'pharmacy' => 'pharmacy/index.php',
    'medicine-add' => 'pharmacy/medicine-add.php',
    'medicine-edit' => 'pharmacy/medicine-edit.php',
    'dispense' => 'pharmacy/dispense.php',
    'laboratory' => 'laboratory/index.php',
    'lab-order-add' => 'laboratory/order-add.php',
    'lab-result' => 'laboratory/result.php',
    'billing' => 'billing/index.php',
    'invoice-add' => 'billing/invoice-add.php',
    'invoice-view' => 'billing/invoice-view.php',
    'payment' => 'billing/payment.php',
    'reports' => 'reports/index.php',
    'users' => 'users/index.php',
    'user-add' => 'users/add.php',
    'user-edit' => 'users/edit.php',
    'settings' => 'settings/index.php'
];

$pageFile = MODULES_PATH . ($pageMap[$page] ?? 'dashboard/index.php');

if (file_exists($pageFile)) {
    include $pageFile;
} else {
    echo '<div class="alert alert-warning">Page not found: ' . htmlspecialchars($page) . '</div>';
}

// Include footer
include INCLUDES_PATH . 'footer.php';
