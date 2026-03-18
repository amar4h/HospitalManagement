/**
 * Surgery Page
 */
const SurgeryPage = {
    async list() {
        App.showLoading();
        const response = await API.surgery.getAll();

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-heart-pulse me-2"></i>Surgery Management</h1>
                <div class="quick-actions">
                    <a href="#/surgery/add" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Schedule Surgery</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Surgeries (${response.data?.length || 0})</div>
                <div class="card-body">
                    ${!response.data?.length ? `
                        <div class="empty-state">
                            <i class="bi bi-heart-pulse"></i>
                            <h5>No Surgeries Scheduled</h5>
                            <a href="#/surgery/add" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Schedule Surgery</a>
                        </div>
                    ` : `
                        <div class="table-responsive">
                            <table class="table table-hover" id="surgery-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Patient</th>
                                        <th>Surgery</th>
                                        <th>Surgeon</th>
                                        <th>OT</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${response.data.map(surgery => `
                                        <tr>
                                            <td>${App.formatDate(surgery.date)} ${surgery.time || ''}</td>
                                            <td>${surgery.patient_name || 'N/A'}</td>
                                            <td>${surgery.surgery_name || 'N/A'}</td>
                                            <td>${surgery.doctor_name || 'N/A'}</td>
                                            <td>${surgery.operation_theatre || 'N/A'}</td>
                                            <td>${App.getStatusBadge(surgery.status)}</td>
                                            <td class="action-btns">
                                                <a href="#/surgery/${surgery.id}/edit" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                                <button class="btn btn-sm btn-danger" onclick="SurgeryPage.delete(${surgery.id})"><i class="bi bi-trash"></i></button>
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

        if (response.data?.length) App.initDataTable('#surgery-table');
    },

    async add() {
        const [patientsRes, doctorsRes] = await Promise.all([
            API.patients.getAll(),
            API.doctors.getAll()
        ]);

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-plus-lg me-2"></i>Schedule Surgery</h1>
                <a href="#/surgery" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <form id="surgery-form">
                        <div class="card">
                            <div class="card-header">Patient & Surgeon</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Patient <span class="text-danger">*</span></label>
                                        <select name="patient_id" class="form-select" required>
                                            <option value="">Select Patient</option>
                                            ${(patientsRes.data || []).map(p =>
                                                `<option value="${p.id}">${p.name} (${p.patient_id})</option>`
                                            ).join('')}
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Surgeon <span class="text-danger">*</span></label>
                                        <select name="doctor_id" class="form-select" required>
                                            <option value="">Select Surgeon</option>
                                            ${(doctorsRes.data || []).filter(d => d.status === 'active').map(d =>
                                                `<option value="${d.id}">${d.name} - ${d.specialization}</option>`
                                            ).join('')}
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">Surgery Details</div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Surgery Name <span class="text-danger">*</span></label>
                                    <input type="text" name="surgery_name" class="form-control" placeholder="e.g., Appendectomy" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Date <span class="text-danger">*</span></label>
                                        <input type="date" name="date" class="form-control" min="${new Date().toISOString().split('T')[0]}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Time</label>
                                        <input type="time" name="time" class="form-control">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Operation Theatre</label>
                                        <select name="operation_theatre" class="form-select">
                                            <option value="">Select OT</option>
                                            <option value="OT-1">OT-1 (Main)</option>
                                            <option value="OT-2">OT-2</option>
                                            <option value="OT-3">OT-3 (Minor)</option>
                                            <option value="OT-4">OT-4 (Emergency)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Anesthesia Type</label>
                                    <select name="anesthesia_type" class="form-select">
                                        <option value="">Select</option>
                                        <option value="General">General</option>
                                        <option value="Local">Local</option>
                                        <option value="Spinal">Spinal</option>
                                        <option value="Epidural">Epidural</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Pre-operative Notes</label>
                                    <textarea name="pre_op_notes" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="#/surgery" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Schedule</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.getElementById('surgery-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target).entries());
            data.status = 'scheduled';
            const response = await API.surgery.create(data);
            if (response.success) {
                App.showToast('Success', 'Surgery scheduled', 'success');
                Router.navigate('/surgery');
            } else {
                App.showToast('Error', response.message || 'Failed', 'danger');
            }
        });
    },

    async edit(params) {
        App.showLoading();
        const [surgeryRes, patientsRes, doctorsRes] = await Promise.all([
            API.surgery.getById(params.id),
            API.patients.getAll(),
            API.doctors.getAll()
        ]);

        if (!surgeryRes.success) {
            App.showToast('Error', 'Surgery not found', 'danger');
            Router.navigate('/surgery');
            return;
        }

        const surgery = surgeryRes.data;

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-pencil me-2"></i>Edit Surgery</h1>
                <a href="#/surgery" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <form id="surgery-form">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Patient</label>
                                        <select name="patient_id" class="form-select" required>
                                            ${(patientsRes.data || []).map(p =>
                                                `<option value="${p.id}" ${surgery.patient_id == p.id ? 'selected' : ''}>${p.name}</option>`
                                            ).join('')}
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Surgeon</label>
                                        <select name="doctor_id" class="form-select" required>
                                            ${(doctorsRes.data || []).map(d =>
                                                `<option value="${d.id}" ${surgery.doctor_id == d.id ? 'selected' : ''}>${d.name}</option>`
                                            ).join('')}
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Surgery Name</label>
                                    <input type="text" name="surgery_name" class="form-control" value="${surgery.surgery_name || ''}" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Date</label>
                                        <input type="date" name="date" class="form-control" value="${surgery.date || ''}" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Time</label>
                                        <input type="time" name="time" class="form-control" value="${surgery.time || ''}">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">OT</label>
                                        <select name="operation_theatre" class="form-select">
                                            <option value="">Select</option>
                                            ${['OT-1', 'OT-2', 'OT-3', 'OT-4'].map(ot =>
                                                `<option value="${ot}" ${surgery.operation_theatre === ot ? 'selected' : ''}>${ot}</option>`
                                            ).join('')}
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            ${['scheduled', 'in_progress', 'completed', 'cancelled'].map(s =>
                                                `<option value="${s}" ${surgery.status === s ? 'selected' : ''}>${s.replace('_', ' ')}</option>`
                                            ).join('')}
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Anesthesia</label>
                                        <select name="anesthesia_type" class="form-select">
                                            <option value="">Select</option>
                                            ${['General', 'Local', 'Spinal', 'Epidural'].map(a =>
                                                `<option value="${a}" ${surgery.anesthesia_type === a ? 'selected' : ''}>${a}</option>`
                                            ).join('')}
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Pre-op Notes</label>
                                    <textarea name="pre_op_notes" class="form-control" rows="2">${surgery.pre_op_notes || ''}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Post-op Notes</label>
                                    <textarea name="post_op_notes" class="form-control" rows="2">${surgery.post_op_notes || ''}</textarea>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="#/surgery" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Update</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.getElementById('surgery-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target).entries());
            const response = await API.surgery.update(params.id, data);
            if (response.success) {
                App.showToast('Success', 'Surgery updated', 'success');
                Router.navigate('/surgery');
            } else {
                App.showToast('Error', response.message || 'Failed', 'danger');
            }
        });
    },

    async delete(id) {
        App.confirmDelete(async () => {
            const response = await API.surgery.delete(id);
            if (response.success) {
                App.showToast('Success', 'Surgery deleted', 'success');
                SurgeryPage.list();
            } else {
                App.showToast('Error', response.message || 'Failed', 'danger');
            }
        });
    }
};
