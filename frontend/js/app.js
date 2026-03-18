/**
 * Main Application
 */
const App = {
    /**
     * Initialize the application
     */
    init() {
        this.bindEvents();
        this.registerRoutes();

        if (Auth.isAuthenticated()) {
            this.showApp();
            Router.init();
        } else {
            this.showLogin();
        }
    },

    /**
     * Bind global events
     */
    bindEvents() {
        // Login form
        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.handleLogin();
        });

        // Logout button
        document.getElementById('logout-btn').addEventListener('click', (e) => {
            e.preventDefault();
            Auth.logout();
        });

        // Sidebar toggle
        document.getElementById('sidebar-toggle').addEventListener('click', () => {
            this.toggleSidebar();
        });

        // Close sidebar on overlay click (mobile)
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('sidebar-overlay')) {
                this.closeSidebar();
            }
        });

        // Delete confirmation
        document.getElementById('confirm-delete-btn').addEventListener('click', () => {
            if (this.deleteCallback) {
                this.deleteCallback();
                bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
            }
        });
    },

    /**
     * Register all routes
     */
    registerRoutes() {
        // Dashboard
        Router.register('/', DashboardPage.render);

        // Patients
        Router.register('/patients', PatientsPage.list);
        Router.register('/patients/add', PatientsPage.add);
        Router.register('/patients/:id', PatientsPage.view);
        Router.register('/patients/:id/edit', PatientsPage.edit);

        // Doctors
        Router.register('/doctors', DoctorsPage.list);
        Router.register('/doctors/add', DoctorsPage.add);
        Router.register('/doctors/:id', DoctorsPage.view);
        Router.register('/doctors/:id/edit', DoctorsPage.edit);

        // Appointments
        Router.register('/appointments', AppointmentsPage.list);
        Router.register('/appointments/add', AppointmentsPage.add);
        Router.register('/appointments/:id/edit', AppointmentsPage.edit);

        // OPD
        Router.register('/opd', OPDPage.list);
        Router.register('/opd/add', OPDPage.add);
        Router.register('/opd/:id', OPDPage.view);

        // IPD
        Router.register('/ipd', IPDPage.list);
        Router.register('/ipd/add', IPDPage.add);
        Router.register('/ipd/:id', IPDPage.view);
        Router.register('/ipd/:id/discharge', IPDPage.discharge);

        // Surgery
        Router.register('/surgery', SurgeryPage.list);
        Router.register('/surgery/add', SurgeryPage.add);
        Router.register('/surgery/:id/edit', SurgeryPage.edit);

        // Pharmacy
        Router.register('/pharmacy', PharmacyPage.list);
        Router.register('/pharmacy/add', PharmacyPage.addMedicine);
        Router.register('/pharmacy/:id/edit', PharmacyPage.editMedicine);
        Router.register('/pharmacy/dispense', PharmacyPage.dispense);

        // Laboratory
        Router.register('/laboratory', LaboratoryPage.list);
        Router.register('/laboratory/add', LaboratoryPage.addOrder);
        Router.register('/laboratory/:id/result', LaboratoryPage.enterResult);

        // Billing
        Router.register('/billing', BillingPage.list);
        Router.register('/billing/add', BillingPage.addInvoice);
        Router.register('/billing/:id', BillingPage.viewInvoice);
        Router.register('/billing/:id/payment', BillingPage.addPayment);

        // Reports
        Router.register('/reports', ReportsPage.render);

        // Users
        Router.register('/users', UsersPage.list);
        Router.register('/users/add', UsersPage.add);
        Router.register('/users/:id/edit', UsersPage.edit);

        // Settings
        Router.register('/settings', SettingsPage.render);

        // Profile
        Router.register('/profile', () => this.renderProfile());
    },

    /**
     * Handle login
     */
    async handleLogin() {
        const username = document.getElementById('login-username').value;
        const password = document.getElementById('login-password').value;
        const errorDiv = document.getElementById('login-error');

        errorDiv.classList.add('d-none');

        const result = await Auth.login(username, password);

        if (result.success) {
            this.showApp();
            Router.init();
        } else {
            errorDiv.textContent = result.message;
            errorDiv.classList.remove('d-none');
        }
    },

    /**
     * Show login page
     */
    showLogin() {
        document.getElementById('login-page').classList.remove('d-none');
        document.getElementById('app').classList.add('d-none');
        document.getElementById('login-username').value = '';
        document.getElementById('login-password').value = '';
        document.getElementById('login-error').classList.add('d-none');
    },

    /**
     * Show main application
     */
    showApp() {
        document.getElementById('login-page').classList.add('d-none');
        document.getElementById('app').classList.remove('d-none');

        const user = Auth.getUser();
        document.getElementById('current-user-name').textContent = user.name || user.username;

        this.buildSidebar();
    },

    /**
     * Build sidebar menu based on user role
     */
    buildSidebar() {
        const role = Auth.getRole();
        const menuItems = Config.MENU_ITEMS[role] || [];
        const menu = document.getElementById('sidebar-menu');

        menu.innerHTML = menuItems.map(item => {
            if (item.divider) {
                return '<li class="nav-divider"></li>';
            }
            if (item.header) {
                return `<li class="nav-header">${item.header}</li>`;
            }
            return `
                <li class="nav-item">
                    <a class="nav-link" href="#${item.route}">
                        <i class="bi ${item.icon}"></i>
                        <span>${item.label}</span>
                    </a>
                </li>
            `;
        }).join('');
    },

    /**
     * Toggle sidebar
     */
    toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');

        if (window.innerWidth < 992) {
            sidebar.classList.toggle('show');
            this.toggleOverlay(sidebar.classList.contains('show'));
        } else {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        }
    },

    /**
     * Close sidebar (mobile)
     */
    closeSidebar() {
        document.getElementById('sidebar').classList.remove('show');
        this.toggleOverlay(false);
    },

    /**
     * Toggle overlay
     */
    toggleOverlay(show) {
        let overlay = document.querySelector('.sidebar-overlay');

        if (show && !overlay) {
            overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay show';
            document.body.appendChild(overlay);
        } else if (!show && overlay) {
            overlay.remove();
        }
    },

    /**
     * Show toast notification
     */
    showToast(title, message, type = 'info') {
        const toast = document.getElementById('toast');
        const toastTitle = document.getElementById('toast-title');
        const toastMessage = document.getElementById('toast-message');

        toastTitle.textContent = title;
        toastMessage.textContent = message;

        // Update icon based on type
        const iconMap = {
            success: 'bi-check-circle text-success',
            danger: 'bi-x-circle text-danger',
            warning: 'bi-exclamation-triangle text-warning',
            info: 'bi-info-circle text-info'
        };

        const icon = toast.querySelector('.toast-header i');
        icon.className = `bi ${iconMap[type] || iconMap.info} me-2`;

        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
    },

    /**
     * Show delete confirmation modal
     */
    confirmDelete(callback) {
        this.deleteCallback = callback;
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    },

    /**
     * Render profile page
     */
    async renderProfile() {
        const user = Auth.getUser();

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-person me-2"></i>My Profile</h1>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">Profile Information</div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Name:</th>
                                    <td>${user.name || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Username:</th>
                                    <td>${user.username}</td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>${user.email || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Role:</th>
                                    <td><span class="badge bg-primary">${user.role}</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">Change Password</div>
                        <div class="card-body">
                            <form id="change-password-form">
                                <div class="mb-3">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" id="current-password" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" id="new-password" class="form-control" required minlength="6">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" id="confirm-password" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>Update Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('change-password-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const currentPassword = document.getElementById('current-password').value;
            const newPassword = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;

            if (newPassword !== confirmPassword) {
                App.showToast('Error', 'New passwords do not match', 'danger');
                return;
            }

            const result = await API.auth.changePassword({
                current_password: currentPassword,
                new_password: newPassword
            });

            if (result.success) {
                App.showToast('Success', 'Password updated successfully', 'success');
                e.target.reset();
            } else {
                App.showToast('Error', result.message || 'Failed to update password', 'danger');
            }
        });
    },

    /**
     * Format date
     */
    formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString(Config.DATE_FORMAT, {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    },

    /**
     * Format currency
     */
    formatCurrency(amount) {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR'
        }).format(amount || 0);
    },

    /**
     * Get status badge HTML
     */
    getStatusBadge(status) {
        const statusClass = (status || 'pending').toLowerCase().replace(' ', '_');
        const label = (status || 'Pending').replace('_', ' ');
        return `<span class="badge-status ${statusClass}">${label.charAt(0).toUpperCase() + label.slice(1)}</span>`;
    },

    /**
     * Show loading spinner
     */
    showLoading() {
        document.getElementById('page-content').innerHTML = '<div class="loading-spinner"></div>';
    },

    /**
     * Initialize DataTable
     */
    initDataTable(selector) {
        const table = $(selector);
        if (table.length && !$.fn.DataTable.isDataTable(selector)) {
            table.DataTable({
                responsive: true,
                pageLength: Config.ITEMS_PER_PAGE,
                language: {
                    search: '',
                    searchPlaceholder: 'Search...'
                }
            });
        }
    }
};

// Initialize app when DOM is ready
document.addEventListener('DOMContentLoaded', () => App.init());
