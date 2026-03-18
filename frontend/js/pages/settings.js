/**
 * Settings Page
 */
const SettingsPage = {
    async render() {
        App.showLoading();
        const [settingsRes, deptRes] = await Promise.all([
            API.settings.get(),
            API.settings.getDepartments()
        ]);

        const settings = settingsRes.data || {};
        const departments = deptRes.data || [];

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-gear me-2"></i>Settings</h1>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">Hospital Information</div>
                        <div class="card-body">
                            <form id="settings-form">
                                <div class="mb-3">
                                    <label class="form-label">Hospital Name</label>
                                    <input type="text" name="hospital_name" class="form-control" value="${settings.hospital_name || ''}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea name="address" class="form-control" rows="2">${settings.address || ''}</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone</label>
                                        <input type="tel" name="phone" class="form-control" value="${settings.phone || ''}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="${settings.email || ''}">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>Save Settings
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">System Information</div>
                        <div class="card-body">
                            <table class="table table-borderless mb-0">
                                <tr><th>Application:</th><td>${Config.APP_NAME}</td></tr>
                                <tr><th>Version:</th><td>${Config.APP_VERSION}</td></tr>
                                <tr><th>API URL:</th><td><small>${Config.API_URL}</small></td></tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Departments</span>
                            <button class="btn btn-sm btn-primary" id="add-dept-btn">
                                <i class="bi bi-plus-lg me-1"></i>Add
                            </button>
                        </div>
                        <div class="card-body">
                            ${!departments.length ? `
                                <p class="text-muted text-center">No departments configured</p>
                            ` : `
                                <ul class="list-group list-group-flush" id="dept-list">
                                    ${departments.map(dept => `
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            ${dept.name}
                                            <button class="btn btn-sm btn-outline-danger" onclick="SettingsPage.deleteDepartment(${dept.id})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </li>
                                    `).join('')}
                                </ul>
                            `}
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">Backup & Maintenance</div>
                        <div class="card-body">
                            <p class="text-muted">Data backup and maintenance options will be available in future updates.</p>
                            <button class="btn btn-outline-secondary" disabled>
                                <i class="bi bi-download me-2"></i>Export Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Department Modal -->
            <div class="modal fade" id="addDeptModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Department</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="add-dept-form">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Department Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Add Department</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;

        // Settings form
        document.getElementById('settings-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target).entries());
            const response = await API.settings.update(data);
            if (response.success) {
                App.showToast('Success', 'Settings saved', 'success');
            } else {
                App.showToast('Error', response.message || 'Failed to save', 'danger');
            }
        });

        // Add department modal
        document.getElementById('add-dept-btn').addEventListener('click', () => {
            new bootstrap.Modal(document.getElementById('addDeptModal')).show();
        });

        document.getElementById('add-dept-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target).entries());
            const response = await API.settings.createDepartment(data);
            if (response.success) {
                App.showToast('Success', 'Department added', 'success');
                bootstrap.Modal.getInstance(document.getElementById('addDeptModal')).hide();
                SettingsPage.render();
            } else {
                App.showToast('Error', response.message || 'Failed', 'danger');
            }
        });
    },

    async deleteDepartment(id) {
        App.confirmDelete(async () => {
            const response = await API.settings.deleteDepartment(id);
            if (response.success) {
                App.showToast('Success', 'Department deleted', 'success');
                SettingsPage.render();
            } else {
                App.showToast('Error', response.message || 'Failed', 'danger');
            }
        });
    }
};
