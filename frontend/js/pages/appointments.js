/**
 * Appointments Page
 */
const AppointmentsPage = {
    async list() {
        App.showLoading();
        const response = await API.appointments.getAll();

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-calendar-check me-2"></i>Appointments</h1>
                <div class="quick-actions">
                    <a href="#/appointments/add" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>New Appointment
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">All Appointments (${response.data?.length || 0})</div>
                <div class="card-body">
                    ${!response.data?.length ? `
                        <div class="empty-state">
                            <i class="bi bi-calendar-check"></i>
                            <h5>No Appointments</h5>
                            <a href="#/appointments/add" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>New Appointment</a>
                        </div>
                    ` : `
                        <div class="table-responsive">
                            <table class="table table-hover" id="appointments-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Patient</th>
                                        <th>Doctor</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${response.data.map(apt => `
                                        <tr>
                                            <td>${App.formatDate(apt.date)}</td>
                                            <td>${apt.time || 'N/A'}</td>
                                            <td>${apt.patient_name || 'N/A'}</td>
                                            <td>${apt.doctor_name || 'N/A'}</td>
                                            <td>${apt.type || 'General'}</td>
                                            <td>${App.getStatusBadge(apt.status)}</td>
                                            <td class="action-btns">
                                                <a href="#/appointments/${apt.id}/edit" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                                <button class="btn btn-sm btn-danger" onclick="AppointmentsPage.delete(${apt.id})"><i class="bi bi-trash"></i></button>
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

        if (response.data?.length) App.initDataTable('#appointments-table');
    },

    async add() {
        const [patientsRes, doctorsRes] = await Promise.all([
            API.patients.getAll(),
            API.doctors.getAll()
        ]);

        const params = Router.getParams();

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-calendar-plus me-2"></i>New Appointment</h1>
                <a href="#/appointments" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <form id="appointment-form">
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Patient <span class="text-danger">*</span></label>
                                    <select name="patient_id" class="form-select" required>
                                        <option value="">Select Patient</option>
                                        ${(patientsRes.data || []).map(p =>
                                            `<option value="${p.id}" ${params.patient_id == p.id ? 'selected' : ''}>${p.name} (${p.patient_id})</option>`
                                        ).join('')}
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Doctor <span class="text-danger">*</span></label>
                                    <select name="doctor_id" class="form-select" required>
                                        <option value="">Select Doctor</option>
                                        ${(doctorsRes.data || []).filter(d => d.status === 'active').map(d =>
                                            `<option value="${d.id}" ${params.doctor_id == d.id ? 'selected' : ''}>${d.name} - ${d.specialization}</option>`
                                        ).join('')}
                                    </select>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Date <span class="text-danger">*</span></label>
                                        <input type="date" name="date" class="form-control" min="${new Date().toISOString().split('T')[0]}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Time <span class="text-danger">*</span></label>
                                        <input type="time" name="time" class="form-control" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Appointment Type</label>
                                    <select name="type" class="form-select">
                                        <option value="General">General Consultation</option>
                                        <option value="Follow-up">Follow-up</option>
                                        <option value="Emergency">Emergency</option>
                                        <option value="Specialist">Specialist Referral</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="#/appointments" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Book Appointment</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.getElementById('appointment-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target).entries());
            data.status = 'scheduled';
            const response = await API.appointments.create(data);
            if (response.success) {
                App.showToast('Success', 'Appointment booked', 'success');
                Router.navigate('/appointments');
            } else {
                App.showToast('Error', response.message || 'Failed', 'danger');
            }
        });
    },

    async edit(params) {
        App.showLoading();
        const [aptRes, patientsRes, doctorsRes] = await Promise.all([
            API.appointments.getById(params.id),
            API.patients.getAll(),
            API.doctors.getAll()
        ]);

        if (!aptRes.success) {
            App.showToast('Error', 'Appointment not found', 'danger');
            Router.navigate('/appointments');
            return;
        }

        const apt = aptRes.data;

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-pencil me-2"></i>Edit Appointment</h1>
                <a href="#/appointments" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <form id="appointment-form">
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Patient</label>
                                    <select name="patient_id" class="form-select" required>
                                        ${(patientsRes.data || []).map(p =>
                                            `<option value="${p.id}" ${apt.patient_id == p.id ? 'selected' : ''}>${p.name}</option>`
                                        ).join('')}
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Doctor</label>
                                    <select name="doctor_id" class="form-select" required>
                                        ${(doctorsRes.data || []).map(d =>
                                            `<option value="${d.id}" ${apt.doctor_id == d.id ? 'selected' : ''}>${d.name}</option>`
                                        ).join('')}
                                    </select>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Date</label>
                                        <input type="date" name="date" class="form-control" value="${apt.date || ''}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Time</label>
                                        <input type="time" name="time" class="form-control" value="${apt.time || ''}" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Type</label>
                                        <select name="type" class="form-select">
                                            ${['General', 'Follow-up', 'Emergency', 'Specialist'].map(t =>
                                                `<option value="${t}" ${apt.type === t ? 'selected' : ''}>${t}</option>`
                                            ).join('')}
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            ${['scheduled', 'completed', 'cancelled'].map(s =>
                                                `<option value="${s}" ${apt.status === s ? 'selected' : ''}>${s.charAt(0).toUpperCase() + s.slice(1)}</option>`
                                            ).join('')}
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="2">${apt.notes || ''}</textarea>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="#/appointments" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Update</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.getElementById('appointment-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target).entries());
            const response = await API.appointments.update(params.id, data);
            if (response.success) {
                App.showToast('Success', 'Appointment updated', 'success');
                Router.navigate('/appointments');
            } else {
                App.showToast('Error', response.message || 'Failed', 'danger');
            }
        });
    },

    async delete(id) {
        App.confirmDelete(async () => {
            const response = await API.appointments.delete(id);
            if (response.success) {
                App.showToast('Success', 'Appointment deleted', 'success');
                AppointmentsPage.list();
            } else {
                App.showToast('Error', response.message || 'Failed', 'danger');
            }
        });
    }
};
