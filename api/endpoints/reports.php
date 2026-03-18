<?php
/**
 * Reports Endpoints
 */

function handleReportsRoute($method, $segments, $user) {
    if ($method !== 'GET') {
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }

    requireRole($user, ['admin', 'accountant']);

    $type = $segments[0] ?? 'revenue';
    $from = sanitize($_GET['from'] ?? date('Y-m-01'));
    $to = sanitize($_GET['to'] ?? date('Y-m-d'));

    switch ($type) {
        case 'revenue':
            getRevenueReport($from, $to);
            break;
        case 'patients':
            getPatientsReport($from, $to);
            break;
        case 'appointments':
            getAppointmentsReport($from, $to);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Invalid report type'], 404);
    }
}

function getRevenueReport($from, $to) {
    $storage = new Storage();
    $invoices = $storage->getAll('invoices');

    // Filter by date range
    $filtered = array_filter($invoices, function($inv) use ($from, $to) {
        $date = $inv['date'] ?? '';
        return $date >= $from && $date <= $to;
    });

    $totalRevenue = 0;
    $pendingAmount = 0;
    $dailyRevenue = [];

    foreach ($filtered as $inv) {
        $totalRevenue += $inv['paid'] ?? 0;
        $pendingAmount += ($inv['total'] ?? 0) - ($inv['paid'] ?? 0);

        $date = $inv['date'] ?? '';
        if (!isset($dailyRevenue[$date])) {
            $dailyRevenue[$date] = 0;
        }
        $dailyRevenue[$date] += $inv['paid'] ?? 0;
    }

    ksort($dailyRevenue);

    jsonResponse(['success' => true, 'data' => [
        'totalRevenue' => $totalRevenue,
        'totalInvoices' => count($filtered),
        'pendingAmount' => $pendingAmount,
        'chartData' => [
            'labels' => array_keys($dailyRevenue),
            'values' => array_values($dailyRevenue)
        ]
    ]]);
}

function getPatientsReport($from, $to) {
    $storage = new Storage();
    $patients = $storage->getAll('patients');
    $opdVisits = $storage->getAll('opd_visits');
    $ipdAdmissions = $storage->getAll('ipd_admissions');

    // New patients in date range
    $newPatients = count(array_filter($patients, function($p) use ($from, $to) {
        $date = substr($p['created_at'] ?? '', 0, 10);
        return $date >= $from && $date <= $to;
    }));

    // OPD visits in date range
    $opdCount = count(array_filter($opdVisits, function($v) use ($from, $to) {
        $date = $v['date'] ?? '';
        return $date >= $from && $date <= $to;
    }));

    // IPD admissions in date range
    $ipdCount = count(array_filter($ipdAdmissions, function($a) use ($from, $to) {
        $date = $a['admission_date'] ?? '';
        return $date >= $from && $date <= $to;
    }));

    // Gender distribution
    $genderCounts = ['Male' => 0, 'Female' => 0, 'Other' => 0];
    foreach ($patients as $p) {
        $gender = $p['gender'] ?? 'Other';
        if (!isset($genderCounts[$gender])) $gender = 'Other';
        $genderCounts[$gender]++;
    }

    // Age distribution
    $ageGroups = ['0-18' => 0, '19-35' => 0, '36-50' => 0, '51-65' => 0, '65+' => 0];
    foreach ($patients as $p) {
        $age = calculateAge($p['dob'] ?? null);
        if ($age === null) continue;
        if ($age <= 18) $ageGroups['0-18']++;
        elseif ($age <= 35) $ageGroups['19-35']++;
        elseif ($age <= 50) $ageGroups['36-50']++;
        elseif ($age <= 65) $ageGroups['51-65']++;
        else $ageGroups['65+']++;
    }

    jsonResponse(['success' => true, 'data' => [
        'totalPatients' => count($patients),
        'newPatients' => $newPatients,
        'opdVisits' => $opdCount,
        'ipdAdmissions' => $ipdCount,
        'genderData' => [
            'labels' => array_keys($genderCounts),
            'values' => array_values($genderCounts)
        ],
        'ageData' => [
            'labels' => array_keys($ageGroups),
            'values' => array_values($ageGroups)
        ]
    ]]);
}

function getAppointmentsReport($from, $to) {
    $storage = new Storage();
    $appointments = $storage->getAll('appointments');

    // Filter by date range
    $filtered = array_filter($appointments, function($apt) use ($from, $to) {
        $date = $apt['date'] ?? '';
        return $date >= $from && $date <= $to;
    });

    $total = count($filtered);
    $completed = 0;
    $cancelled = 0;
    $dailyCounts = [];

    foreach ($filtered as $apt) {
        if (($apt['status'] ?? '') === 'completed') $completed++;
        if (($apt['status'] ?? '') === 'cancelled') $cancelled++;

        $date = $apt['date'] ?? '';
        if (!isset($dailyCounts[$date])) $dailyCounts[$date] = 0;
        $dailyCounts[$date]++;
    }

    ksort($dailyCounts);

    jsonResponse(['success' => true, 'data' => [
        'total' => $total,
        'completed' => $completed,
        'cancelled' => $cancelled,
        'scheduled' => $total - $completed - $cancelled,
        'chartData' => [
            'labels' => array_keys($dailyCounts),
            'values' => array_values($dailyCounts)
        ]
    ]]);
}
