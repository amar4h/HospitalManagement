/**
 * Dashboard Page
 */
const DashboardPage = {
    async render() {
        App.showLoading();

        const stats = await API.dashboard.stats();
        const user = Auth.getUser();

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-speedometer2 me-2"></i>Dashboard</h1>
                <div class="text-muted">Welcome back, ${user.name || user.username}!</div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary"><i class="bi bi-people"></i></div>
                    <div class="stat-info">
                        <h3>${stats.data?.patients || 0}</h3>
                        <p>Total Patients</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon success"><i class="bi bi-person-badge"></i></div>
                    <div class="stat-info">
                        <h3>${stats.data?.doctors || 0}</h3>
                        <p>Doctors</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon warning"><i class="bi bi-calendar-check"></i></div>
                    <div class="stat-info">
                        <h3>${stats.data?.todayAppointments || 0}</h3>
                        <p>Today's Appointments</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon info"><i class="bi bi-hospital"></i></div>
                    <div class="stat-info">
                        <h3>${stats.data?.ipdAdmissions || 0}</h3>
                        <p>IPD Admissions</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-lightning me-2"></i>Quick Actions
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-2">
                                <a href="#/patients/add" class="btn btn-outline-primary">
                                    <i class="bi bi-person-plus me-2"></i>New Patient
                                </a>
                                <a href="#/appointments/add" class="btn btn-outline-success">
                                    <i class="bi bi-calendar-plus me-2"></i>New Appointment
                                </a>
                                <a href="#/opd/add" class="btn btn-outline-info">
                                    <i class="bi bi-clipboard-plus me-2"></i>OPD Visit
                                </a>
                                <a href="#/billing/add" class="btn btn-outline-warning">
                                    <i class="bi bi-receipt me-2"></i>New Invoice
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-clock-history me-2"></i>Recent Activity
                        </div>
                        <div class="card-body" id="recent-activity">
                            <div class="loading-spinner"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-calendar-week me-2"></i>Today's Appointments
                        </div>
                        <div class="card-body" id="today-appointments">
                            <div class="loading-spinner"></div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-people me-2"></i>Recent Patients
                        </div>
                        <div class="card-body" id="recent-patients">
                            <div class="loading-spinner"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Load additional data
        DashboardPage.loadTodayAppointments();
        DashboardPage.loadRecentPatients();
        DashboardPage.loadRecentActivity();
    },

    async loadTodayAppointments() {
        const response = await API.dashboard.todayAppointments();
        const container = document.getElementById('today-appointments');

        if (!response.success || !response.data?.length) {
            container.innerHTML = '<p class="text-muted text-center">No appointments today</p>';
            return;
        }

        container.innerHTML = `
            <div class="list-group list-group-flush">
                ${response.data.slice(0, 5).map(apt => `
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${apt.patient_name}</strong>
                            <br><small class="text-muted">${apt.time} - Dr. ${apt.doctor_name}</small>
                        </div>
                        ${App.getStatusBadge(apt.status)}
                    </div>
                `).join('')}
            </div>
            <a href="#/appointments" class="btn btn-link btn-sm">View All</a>
        `;
    },

    async loadRecentPatients() {
        const response = await API.dashboard.recentPatients();
        const container = document.getElementById('recent-patients');

        if (!response.success || !response.data?.length) {
            container.innerHTML = '<p class="text-muted text-center">No recent patients</p>';
            return;
        }

        container.innerHTML = `
            <div class="list-group list-group-flush">
                ${response.data.slice(0, 5).map(patient => `
                    <a href="#/patients/${patient.id}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>${patient.name}</strong>
                                <br><small class="text-muted">${patient.patient_id} | ${patient.phone || 'N/A'}</small>
                            </div>
                            <small class="text-muted">${App.formatDate(patient.created_at)}</small>
                        </div>
                    </a>
                `).join('')}
            </div>
            <a href="#/patients" class="btn btn-link btn-sm">View All</a>
        `;
    },

    async loadRecentActivity() {
        const response = await API.dashboard.recentActivity();
        const container = document.getElementById('recent-activity');

        if (!response.success || !response.data?.length) {
            container.innerHTML = '<p class="text-muted text-center">No recent activity</p>';
            return;
        }

        container.innerHTML = `
            <div class="list-group list-group-flush">
                ${response.data.slice(0, 5).map(activity => `
                    <div class="list-group-item">
                        <small class="text-muted float-end">${App.formatDate(activity.created_at)}</small>
                        <strong>${activity.action}</strong>
                        <br><small class="text-muted">${activity.description || ''}</small>
                    </div>
                `).join('')}
            </div>
        `;
    }
};
