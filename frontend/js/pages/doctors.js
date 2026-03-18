/**
 * Doctors Page
 */
const DoctorsPage = {
    async list() {
        App.showLoading();
        const response = await API.doctors.getAll();

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-person-badge me-2"></i>Doctors</h1>
                <div class="quick-actions">
                    <a href="#/doctors/add" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>Add Doctor
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">All Doctors (${response.data?.length || 0})</div>
                <div class="card-body">
                    ${!response.data?.length ? `
                        <div class="empty-state">
                            <i class="bi bi-person-badge"></i>
                            <h5>No Doctors Found</h5>
                            <a href="#/doctors/add" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Add Doctor</a>
                        </div>
                    ` : `
                        <div class="table-responsive">
                            <table class="table table-hover" id="doctors-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Specialization</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${response.data.map(doctor => `
                                        <tr>
                                            <td><a href="#/doctors/${doctor.id}">${doctor.name}</a></td>
                                            <td>${doctor.specialization || 'N/A'}</td>
                                            <td>${doctor.phone || 'N/A'}</td>
                                            <td>${doctor.email || 'N/A'}</td>
                                            <td>${App.getStatusBadge(doctor.status)}</td>
                                            <td class="action-btns">
                                                <a href="#/doctors/${doctor.id}" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
                                                <a href="#/doctors/${doctor.id}/edit" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                                <button class="btn btn-sm btn-danger" onclick="DoctorsPage.delete(${doctor.id})"><i class="bi bi-trash"></i></button>
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

        if (response.data?.length) App.initDataTable('#doctors-table');
    },

    async add() {
        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-person-plus me-2"></i>Add Doctor</h1>
                <a href="#/doctors" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <form id="doctor-form">
                        <div class="card">
                            <div class="card-header">Doctor Information</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Specialization <span class="text-danger">*</span></label>
                                        <input type="text" name="specialization" class="form-control" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone</label>
                                        <input type="tel" name="phone" class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Qualification</label>
                                        <input type="text" name="qualification" class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Consultation Fee</label>
                                        <input type="number" name="consultation_fee" class="form-control" min="0">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea name="address" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="#/doctors" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Save Doctor</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.getElementById('doctor-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target).entries());
            data.status = 'active';
            const response = await API.doctors.create(data);
            if (response.success) {
                App.showToast('Success', 'Doctor added successfully', 'success');
                Router.navigate('/doctors');
            } else {
                App.showToast('Error', response.message || 'Failed to add doctor', 'danger');
            }
        });
    },

    async view(params) {
        App.showLoading();
        const response = await API.doctors.getById(params.id);
        if (!response.success) {
            App.showToast('Error', 'Doctor not found', 'danger');
            Router.navigate('/doctors');
            return;
        }
        const doctor = response.data;

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-person-badge me-2"></i>${doctor.name}</h1>
                <div class="quick-actions">
                    <a href="#/doctors/${doctor.id}/edit" class="btn btn-warning"><i class="bi bi-pencil me-2"></i>Edit</a>
                    <a href="#/doctors" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">Doctor Details</div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr><th>Name:</th><td>${doctor.name}</td></tr>
                                <tr><th>Specialization:</th><td>${doctor.specialization || 'N/A'}</td></tr>
                                <tr><th>Qualification:</th><td>${doctor.qualification || 'N/A'}</td></tr>
                                <tr><th>Phone:</th><td>${doctor.phone || 'N/A'}</td></tr>
                                <tr><th>Email:</th><td>${doctor.email || 'N/A'}</td></tr>
                                <tr><th>Consultation Fee:</th><td>${App.formatCurrency(doctor.consultation_fee)}</td></tr>
                                <tr><th>Status:</th><td>${App.getStatusBadge(doctor.status)}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">Quick Actions</div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-2">
                                <a href="#/appointments/add?doctor_id=${doctor.id}" class="btn btn-outline-primary">
                                    <i class="bi bi-calendar-plus me-2"></i>Book Appointment
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    },

    async edit(params) {
        App.showLoading();
        const response = await API.doctors.getById(params.id);
        if (!response.success) {
            App.showToast('Error', 'Doctor not found', 'danger');
            Router.navigate('/doctors');
            return;
        }
        const doctor = response.data;

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-pencil me-2"></i>Edit Doctor</h1>
                <a href="#/doctors/${doctor.id}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <form id="doctor-form">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" value="${doctor.name || ''}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Specialization</label>
                                        <input type="text" name="specialization" class="form-control" value="${doctor.specialization || ''}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone</label>
                                        <input type="tel" name="phone" class="form-control" value="${doctor.phone || ''}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="${doctor.email || ''}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Qualification</label>
                                        <input type="text" name="qualification" class="form-control" value="${doctor.qualification || ''}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Consultation Fee</label>
                                        <input type="number" name="consultation_fee" class="form-control" value="${doctor.consultation_fee || ''}" min="0">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="active" ${doctor.status === 'active' ? 'selected' : ''}>Active</option>
                                            <option value="inactive" ${doctor.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="#/doctors/${doctor.id}" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Update</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.getElementById('doctor-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target).entries());
            const response = await API.doctors.update(params.id, data);
            if (response.success) {
                App.showToast('Success', 'Doctor updated successfully', 'success');
                Router.navigate(`/doctors/${params.id}`);
            } else {
                App.showToast('Error', response.message || 'Failed to update', 'danger');
            }
        });
    },

    async delete(id) {
        App.confirmDelete(async () => {
            const response = await API.doctors.delete(id);
            if (response.success) {
                App.showToast('Success', 'Doctor deleted', 'success');
                DoctorsPage.list();
            } else {
                App.showToast('Error', response.message || 'Failed to delete', 'danger');
            }
        });
    }
};
