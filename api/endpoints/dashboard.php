<?php
/**
 * Dashboard Endpoints
 */

function handleDashboardRoute($method, $segments, $user) {
    if ($method !== 'GET') {
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }

    $action = $segments[0] ?? 'stats';

    switch ($action) {
        case 'stats':
            getDashboardStats($user);
            break;
        case 'recent-patients':
            getRecentPatients($user);
            break;
        case 'today-appointments':
            getTodayAppointments($user);
            break;
        case 'recent-activity':
            getRecentActivity($user);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Invalid endpoint'], 404);
    }
}

function getDashboardStats($user) {
    $storage = new Storage();
    $today = date('Y-m-d');

    $stats = [
        'patients' => $storage->count('patients'),
        'doctors' => $storage->count('doctors', ['status' => 'active']),
        'todayAppointments' => count(array_filter($storage->getAll('appointments'), function($a) use ($today) {
            return ($a['date'] ?? '') === $today;
        })),
        'ipdAdmissions' => $storage->count('ipd_admissions', ['status' => 'admitted']),
        'pendingLabOrders' => $storage->count('lab_orders', ['status' => 'pending']),
        'lowStockMedicines' => count(array_filter($storage->getAll('medicines'), function($m) {
            return ($m['stock'] ?? 0) <= ($m['reorder_level'] ?? 10);
        }))
    ];

    jsonResponse(['success' => true, 'data' => $stats]);
}

function getRecentPatients($user) {
    $storage = new Storage();
    $patients = $storage->getAll('patients');

    // Sort by created_at descending
    usort($patients, function($a, $b) {
        return strtotime($b['created_at'] ?? 0) - strtotime($a['created_at'] ?? 0);
    });

    // Get top 10
    $patients = array_slice($patients, 0, 10);

    jsonResponse(['success' => true, 'data' => $patients]);
}

function getTodayAppointments($user) {
    $storage = new Storage();
    $today = date('Y-m-d');

    $appointments = array_filter($storage->getAll('appointments'), function($a) use ($today) {
        return ($a['date'] ?? '') === $today;
    });

    // Enrich with patient and doctor names
    foreach ($appointments as &$apt) {
        $patient = $storage->getById('patients', $apt['patient_id'] ?? 0);
        $doctor = $storage->getById('doctors', $apt['doctor_id'] ?? 0);
        $apt['patient_name'] = $patient['name'] ?? 'N/A';
        $apt['doctor_name'] = $doctor['name'] ?? 'N/A';
    }

    // Sort by time
    usort($appointments, function($a, $b) {
        return strcmp($a['time'] ?? '', $b['time'] ?? '');
    });

    jsonResponse(['success' => true, 'data' => array_values($appointments)]);
}

function getRecentActivity($user) {
    $storage = new Storage();
    $logs = $storage->getAll('activity_logs');

    // Sort by created_at descending
    usort($logs, function($a, $b) {
        return strtotime($b['created_at'] ?? 0) - strtotime($a['created_at'] ?? 0);
    });

    // Get top 10
    $logs = array_slice($logs, 0, 10);

    jsonResponse(['success' => true, 'data' => $logs]);
}
