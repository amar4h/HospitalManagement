/**
 * Users Page
 */
const UsersPage = {
    async list() {
        App.showLoading();
        const response = await API.users.getAll();

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-people-fill me-2"></i>Users</h1>
                <div class="quick-actions">
                    <a href="#/users/add" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Add User</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">System Users (${response.data?.length || 0})</div>
                <div class="card-body">
                    ${!response.data?.length ? `
                        <div class="empty-state">
                            <i class="bi bi-people-fill"></i>
                            <h5>No Users</h5>
                            <a href="#/users/add" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Add User</a>
                        </div>
                    ` : `
                        <div class="table-responsive">
                            <table class="table table-hover" id="users-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${response.data.map(user => `
                                        <tr>
                                            <td>${user.name || 'N/A'}</td>
                                            <td>${user.username}</td>
                                            <td>${user.email || 'N/A'}</td>
                                            <td><span class="badge bg-primary">${user.role}</span></td>
                                            <td>${App.getStatusBadge(user.status || 'active')}</td>
                                            <td class="action-btns">
                                                <a href="#/users/${user.id}/edit" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                                <button class="btn btn-sm btn-danger" onclick="UsersPage.delete(${user.id})" ${user.username === 'admin' ? 'disabled' : ''}>
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    `}
                </div>
            </div>
        `;

        if (response.data?.length) App.initDataTable('#users-table');
    },

    async add() {
        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-person-plus me-2"></i>Add User</h1>
                <a href="#/users" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <form id="user-form">
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" name="username" class="form-control" required pattern="[a-z0-9_]+" title="Lowercase letters, numbers and underscore only">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control" required minlength="6">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Role <span class="text-danger">*</span></label>
                                    <select name="role" class="form-select" required>
                                        <option value="">Select Role</option>
                                        <option value="admin">Admin</option>
                                        <option value="doctor">Doctor</option>
                                        <option value="nurse">Nurse</option>
                                        <option value="receptionist">Receptionist</option>
                                        <option value="pharmacist">Pharmacist</option>
                                        <option value="lab_technician">Lab Technician</option>
                                        <option value="accountant">Accountant</option>
                                    </select>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="#/users" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Create User</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.getElementById('user-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target).entries());
            data.status = 'active';
            const response = await API.users.create(data);
            if (response.success) {
                App.showToast('Success', 'User created', 'success');
                Router.navigate('/users');
            } else {
                App.showToast('Error', response.message || 'Failed to create user', 'danger');
            }
        });
    },

    async edit(params) {
        App.showLoading();
        const response = await API.users.getById(params.id);
        if (!response.success) {
            App.showToast('Error', 'User not found', 'danger');
            Router.navigate('/users');
            return;
        }
        const user = response.data;

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-pencil me-2"></i>Edit User</h1>
                <a href="#/users" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <form id="user-form">
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control" value="${user.name || ''}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" name="username" class="form-control" value="${user.username}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="${user.email || ''}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">New Password <small class="text-muted">(leave blank to keep current)</small></label>
                                    <input type="password" name="password" class="form-control" minlength="6">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Role</label>
                                    <select name="role" class="form-select" ${user.username === 'admin' ? 'disabled' : ''}>
                                        ${['admin', 'doctor', 'nurse', 'receptionist', 'pharmacist', 'lab_technician', 'accountant'].map(role =>
                                            `<option value="${role}" ${user.role === role ? 'selected' : ''}>${role.replace('_', ' ')}</option>`
                                        ).join('')}
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select" ${user.username === 'admin' ? 'disabled' : ''}>
                                        <option value="active" ${user.status === 'active' ? 'selected' : ''}>Active</option>
                                        <option value="inactive" ${user.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                                    </select>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="#/users" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Update User</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.getElementById('user-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = {};
            formData.forEach((value, key) => {
                if (key === 'password' && !value) return; // Skip empty password
                data[key] = value;
            });

            const response = await API.users.update(params.id, data);
            if (response.success) {
                App.showToast('Success', 'User updated', 'success');
                Router.navigate('/users');
            } else {
                App.showToast('Error', response.message || 'Failed to update user', 'danger');
            }
        });
    },

    async delete(id) {
        App.confirmDelete(async () => {
            const response = await API.users.delete(id);
            if (response.success) {
                App.showToast('Success', 'User deleted', 'success');
                UsersPage.list();
            } else {
                App.showToast('Error', response.message || 'Failed to delete user', 'danger');
            }
        });
    }
};
