<?php
/**
 * Reports Dashboard
 */
requireAuth();
requireRole(['admin', 'accountant']);

$storage = getStorage();

// Date filters
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Get data for reports
$patients = $storage->getAll('patients');
$appointments = $storage->getAll('appointments');
$opdVisits = $storage->getAll('opd_visits');
$ipdAdmissions = $storage->getAll('ipd_admissions');
$invoices = $storage->getAll('invoices');
$labOrders = $storage->getAll('lab_orders');

// Filter by date
$filteredInvoices = array_filter($invoices, function($inv) use ($startDate, $endDate) {
    $date = date('Y-m-d', strtotime($inv['created_at']));
    return $date >= $startDate && $date <= $endDate;
});

$filteredOPD = array_filter($opdVisits, function($v) use ($startDate, $endDate) {
    return $v['date'] >= $startDate && $v['date'] <= $endDate;
});

$filteredAppointments = array_filter($appointments, function($a) use ($startDate, $endDate) {
    return $a['date'] >= $startDate && $a['date'] <= $endDate;
});

// Calculate statistics
$totalRevenue = array_sum(array_column($filteredInvoices, 'total_amount'));
$collectedAmount = array_sum(array_column(array_filter($filteredInvoices, fn($i) => $i['payment_status'] === 'paid'), 'total_amount'));
$pendingAmount = $totalRevenue - $collectedAmount;

// Monthly revenue data for chart
$monthlyRevenue = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $monthlyRevenue[$month] = 0;
}
foreach ($invoices as $inv) {
    $month = date('Y-m', strtotime($inv['created_at']));
    if (isset($monthlyRevenue[$month])) {
        $monthlyRevenue[$month] += $inv['total_amount'];
    }
}

// Department-wise patient count
$doctors = $storage->getAll('doctors');
$departments = $storage->getAll('departments');
$deptStats = [];
foreach ($departments as $dept) {
    $deptDoctors = array_filter($doctors, fn($d) => $d['department_id'] == $dept['id']);
    $deptVisits = 0;
    foreach ($deptDoctors as $doc) {
        $deptVisits += count(array_filter($opdVisits, fn($v) => $v['doctor_id'] == $doc['id']));
    }
    $deptStats[$dept['name']] = $deptVisits;
}
?>

<div class="page-header">
    <h1><i class="bi bi-graph-up me-2"></i>Reports</h1>
</div>

<!-- Date Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row align-items-end">
            <input type="hidden" name="page" value="reports">
            <div class="col-md-4 mb-2">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?= $startDate ?>">
            </div>
            <div class="col-md-4 mb-2">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?= $endDate ?>">
            </div>
            <div class="col-md-4 mb-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-filter me-2"></i>Filter
                </button>
                <a href="index.php?page=reports" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Revenue Stats -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="stat-card bg-primary">
            <i class="bi bi-currency-dollar stat-icon"></i>
            <div class="stat-value"><?= formatCurrency($totalRevenue) ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="stat-card bg-success">
            <i class="bi bi-check-circle stat-icon"></i>
            <div class="stat-value"><?= formatCurrency($collectedAmount) ?></div>
            <div class="stat-label">Collected</div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="stat-card bg-warning">
            <i class="bi bi-clock stat-icon"></i>
            <div class="stat-value"><?= formatCurrency($pendingAmount) ?></div>
            <div class="stat-label">Pending</div>
        </div>
    </div>
</div>

<!-- Activity Stats -->
<div class="row mb-4">
    <div class="col-md-3 col-6 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <h3 class="text-primary"><?= count($filteredAppointments) ?></h3>
                <p class="mb-0">Appointments</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <h3 class="text-success"><?= count($filteredOPD) ?></h3>
                <p class="mb-0">OPD Visits</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <h3 class="text-info"><?= count(array_filter($ipdAdmissions, fn($a) => $a['admission_date'] >= $startDate && $a['admission_date'] <= $endDate)) ?></h3>
                <p class="mb-0">IPD Admissions</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <h3 class="text-warning"><?= count(array_filter($labOrders, fn($l) => date('Y-m-d', strtotime($l['created_at'])) >= $startDate && date('Y-m-d', strtotime($l['created_at'])) <= $endDate)) ?></h3>
                <p class="mb-0">Lab Tests</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Revenue Chart -->
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-bar-chart me-2"></i>Monthly Revenue (Last 6 Months)
            </div>
            <div class="card-body">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Department Stats -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-pie-chart me-2"></i>Department Visits
            </div>
            <div class="card-body">
                <canvas id="deptChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Quick Reports -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">Top Doctors (By Visits)</div>
            <div class="card-body">
                <?php
                $doctorVisits = [];
                foreach ($doctors as $doc) {
                    $count = count(array_filter($opdVisits, fn($v) => $v['doctor_id'] == $doc['id']));
                    $doctorVisits[$doc['id']] = ['name' => $doc['name'], 'count' => $count];
                }
                uasort($doctorVisits, fn($a, $b) => $b['count'] - $a['count']);
                $topDoctors = array_slice($doctorVisits, 0, 5, true);
                ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($topDoctors as $doc): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <?= htmlspecialchars($doc['name']) ?>
                        <span class="badge bg-primary"><?= $doc['count'] ?> visits</span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">Recent High-Value Invoices</div>
            <div class="card-body">
                <?php
                usort($filteredInvoices, fn($a, $b) => $b['total_amount'] - $a['total_amount']);
                $topInvoices = array_slice($filteredInvoices, 0, 5);
                ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($topInvoices as $inv): ?>
                    <?php $p = $storage->getById('patients', $inv['patient_id']); ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><?= $inv['invoice_number'] ?> - <?= htmlspecialchars($p['name'] ?? '') ?></span>
                        <span class="badge bg-success"><?= formatCurrency($inv['total_amount']) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
$pageScripts = <<<JS
<script>
// Revenue Chart
new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels: ['{$labels = implode("','", array_keys($monthlyRevenue))}'],
        datasets: [{
            label: 'Revenue',
            data: [{$values = implode(',', array_values($monthlyRevenue))}],
            backgroundColor: 'rgba(13, 110, 253, 0.7)',
            borderColor: 'rgba(13, 110, 253, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Department Chart
new Chart(document.getElementById('deptChart'), {
    type: 'doughnut',
    data: {
        labels: ['General Medicine', 'Cardiology', 'Orthopedics', 'Pediatrics', 'Other'],
        datasets: [{
            data: [30, 20, 15, 10, 25],
            backgroundColor: ['#667eea', '#28a745', '#ffc107', '#17a2b8', '#6c757d']
        }]
    },
    options: {
        responsive: true
    }
});
</script>
JS;
?>
