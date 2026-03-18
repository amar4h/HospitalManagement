/**
 * Reports Page
 */
const ReportsPage = {
    async render() {
        App.showLoading();

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-graph-up me-2"></i>Reports</h1>
            </div>

            <div class="row">
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">Report Type</div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <a href="#" class="list-group-item list-group-item-action active" data-report="revenue">
                                    <i class="bi bi-cash-stack me-2"></i>Revenue Report
                                </a>
                                <a href="#" class="list-group-item list-group-item-action" data-report="patients">
                                    <i class="bi bi-people me-2"></i>Patient Statistics
                                </a>
                                <a href="#" class="list-group-item list-group-item-action" data-report="appointments">
                                    <i class="bi bi-calendar-check me-2"></i>Appointment Report
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">Filters</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">From Date</label>
                                <input type="date" id="from-date" class="form-control" value="${this.getDefaultFromDate()}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">To Date</label>
                                <input type="date" id="to-date" class="form-control" value="${new Date().toISOString().split('T')[0]}">
                            </div>
                            <button class="btn btn-primary w-100" id="generate-report-btn">
                                <i class="bi bi-play-fill me-2"></i>Generate Report
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header" id="report-title">Revenue Report</div>
                        <div class="card-body" id="report-content">
                            <div class="loading-spinner"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        this.currentReport = 'revenue';
        this.bindEvents();
        this.loadReport('revenue');
    },

    getDefaultFromDate() {
        const date = new Date();
        date.setMonth(date.getMonth() - 1);
        return date.toISOString().split('T')[0];
    },

    bindEvents() {
        document.querySelectorAll('[data-report]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                document.querySelectorAll('[data-report]').forEach(l => l.classList.remove('active'));
                link.classList.add('active');
                this.currentReport = link.dataset.report;
                document.getElementById('report-title').textContent = link.textContent.trim();
                this.loadReport(link.dataset.report);
            });
        });

        document.getElementById('generate-report-btn').addEventListener('click', () => {
            this.loadReport(this.currentReport);
        });
    },

    async loadReport(type) {
        const fromDate = document.getElementById('from-date').value;
        const toDate = document.getElementById('to-date').value;
        const container = document.getElementById('report-content');
        container.innerHTML = '<div class="loading-spinner"></div>';

        try {
            let response;
            switch (type) {
                case 'revenue':
                    response = await API.reports.revenue({ from: fromDate, to: toDate });
                    this.renderRevenueReport(response.data || {});
                    break;
                case 'patients':
                    response = await API.reports.patients({ from: fromDate, to: toDate });
                    this.renderPatientsReport(response.data || {});
                    break;
                case 'appointments':
                    response = await API.reports.appointments({ from: fromDate, to: toDate });
                    this.renderAppointmentsReport(response.data || {});
                    break;
            }
        } catch (error) {
            container.innerHTML = '<div class="alert alert-danger">Failed to load report</div>';
        }
    },

    renderRevenueReport(data) {
        const container = document.getElementById('report-content');
        container.innerHTML = `
            <div class="stats-grid mb-4">
                <div class="stat-card">
                    <div class="stat-icon success"><i class="bi bi-cash"></i></div>
                    <div class="stat-info">
                        <h3>${App.formatCurrency(data.totalRevenue || 0)}</h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon primary"><i class="bi bi-receipt"></i></div>
                    <div class="stat-info">
                        <h3>${data.totalInvoices || 0}</h3>
                        <p>Invoices</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon warning"><i class="bi bi-hourglass-split"></i></div>
                    <div class="stat-info">
                        <h3>${App.formatCurrency(data.pendingAmount || 0)}</h3>
                        <p>Pending</p>
                    </div>
                </div>
            </div>
            <canvas id="revenue-chart" height="200"></canvas>
        `;

        if (data.chartData) {
            new Chart(document.getElementById('revenue-chart'), {
                type: 'line',
                data: {
                    labels: data.chartData.labels || [],
                    datasets: [{
                        label: 'Revenue',
                        data: data.chartData.values || [],
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } }
                }
            });
        }
    },

    renderPatientsReport(data) {
        const container = document.getElementById('report-content');
        container.innerHTML = `
            <div class="stats-grid mb-4">
                <div class="stat-card">
                    <div class="stat-icon primary"><i class="bi bi-people"></i></div>
                    <div class="stat-info">
                        <h3>${data.totalPatients || 0}</h3>
                        <p>Total Patients</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon success"><i class="bi bi-person-plus"></i></div>
                    <div class="stat-info">
                        <h3>${data.newPatients || 0}</h3>
                        <p>New Patients</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon info"><i class="bi bi-clipboard2-pulse"></i></div>
                    <div class="stat-info">
                        <h3>${data.opdVisits || 0}</h3>
                        <p>OPD Visits</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon warning"><i class="bi bi-hospital"></i></div>
                    <div class="stat-info">
                        <h3>${data.ipdAdmissions || 0}</h3>
                        <p>IPD Admissions</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h6>Gender Distribution</h6>
                    <canvas id="gender-chart" height="200"></canvas>
                </div>
                <div class="col-md-6">
                    <h6>Age Distribution</h6>
                    <canvas id="age-chart" height="200"></canvas>
                </div>
            </div>
        `;

        if (data.genderData) {
            new Chart(document.getElementById('gender-chart'), {
                type: 'doughnut',
                data: {
                    labels: data.genderData.labels || ['Male', 'Female', 'Other'],
                    datasets: [{ data: data.genderData.values || [0, 0, 0], backgroundColor: ['#0d6efd', '#dc3545', '#ffc107'] }]
                }
            });
        }

        if (data.ageData) {
            new Chart(document.getElementById('age-chart'), {
                type: 'bar',
                data: {
                    labels: data.ageData.labels || ['0-18', '19-35', '36-50', '51-65', '65+'],
                    datasets: [{ data: data.ageData.values || [0, 0, 0, 0, 0], backgroundColor: '#198754' }]
                },
                options: { plugins: { legend: { display: false } } }
            });
        }
    },

    renderAppointmentsReport(data) {
        const container = document.getElementById('report-content');
        container.innerHTML = `
            <div class="stats-grid mb-4">
                <div class="stat-card">
                    <div class="stat-icon primary"><i class="bi bi-calendar-check"></i></div>
                    <div class="stat-info">
                        <h3>${data.total || 0}</h3>
                        <p>Total Appointments</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon success"><i class="bi bi-check-circle"></i></div>
                    <div class="stat-info">
                        <h3>${data.completed || 0}</h3>
                        <p>Completed</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon danger"><i class="bi bi-x-circle"></i></div>
                    <div class="stat-info">
                        <h3>${data.cancelled || 0}</h3>
                        <p>Cancelled</p>
                    </div>
                </div>
            </div>
            <canvas id="appointments-chart" height="200"></canvas>
        `;

        if (data.chartData) {
            new Chart(document.getElementById('appointments-chart'), {
                type: 'bar',
                data: {
                    labels: data.chartData.labels || [],
                    datasets: [{
                        label: 'Appointments',
                        data: data.chartData.values || [],
                        backgroundColor: '#0d6efd'
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } } }
            });
        }
    }
};
