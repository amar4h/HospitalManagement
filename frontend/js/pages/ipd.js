/**
 * IPD Page
 */
const IPDPage = {
    async list() {
        App.showLoading();
        const response = await API.ipd.getAll();

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-hospital me-2"></i>IPD Admissions</h1>
                <div class="quick-actions">
                    <a href="#/ipd/add" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>New Admission</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">IPD Records (${response.data?.length || 0})</div>
                <div class="card-body">
                    ${!response.data?.length ? `
                        <div class="empty-state">
                            <i class="bi bi-hospital"></i>
                            <h5>No IPD Admissions</h5>
                            <a href="#/ipd/add" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>New Admission</a>
                        </div>
                    ` : `
                        <div class="table-responsive">
                            <table class="table table-hover" id="ipd-table">
                                <thead>
                                    <tr>
                                        <th>Admission Date</th>
                                        <th>Patient</th>
                                        <th>Doctor</th>
                                        <th>Bed/Ward</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${response.data.map(ipd => `
                                        <tr>
                                            <td>${App.formatDate(ipd.admission_date)}</td>
                                            <td>${ipd.patient_name || 'N/A'}</td>
                                            <td>${ipd.doctor_name || 'N/A'}</td>
                                            <td>${ipd.bed_number || 'N/A'} / ${ipd.ward || 'N/A'}</td>
                                            <td>${App.getStatusBadge(ipd.status)}</td>
                                            <td class="action-btns">
                                                <a href="#/ipd/${ipd.id}" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
                                                ${ipd.status === 'admitted' ? `
                                                    <a href="#/ipd/${ipd.id}/discharge" class="btn btn-sm btn-success"><i class="bi bi-box-arrow-right"></i></a>
                                                ` : ''}
                                                <button class="btn btn-sm btn-danger" onclick="IPDPage.delete(${ipd.id})"><i class="bi bi-trash"></i></button>
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

        if (response.data?.length) App.initDataTable('#ipd-table');
    },

    async add() {
        const [patientsRes, doctorsRes, bedsRes] = await Promise.all([
            API.patients.getAll(),
            API.doctors.getAll(),
            API.ipd.getBeds()
        ]);
        const params = Router.getParams();

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-hospital me-2"></i>New IPD Admission</h1>
                <a href="#/ipd" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <form id="ipd-form">
                        <div class="card">
                            <div class="card-header">Admission Details</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Patient <span class="text-danger">*</span></label>
                                        <select name="patient_id" class="form-select" required>
                                            <option value="">Select Patient</option>
                                            ${(patientsRes.data || []).map(p =>
                                                `<option value="${p.id}" ${params.patient_id == p.id ? 'selected' : ''}>${p.name} (${p.patient_id})</option>`
                                            ).join('')}
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Doctor <span class="text-danger">*</span></label>
                                        <select name="doctor_id" class="form-select" required>
                                            <option value="">Select Doctor</option>
                                            ${(doctorsRes.data || []).filter(d => d.status === 'active').map(d =>
                                                `<option value="${d.id}">${d.name} - ${d.specialization}</option>`
                                            ).join('')}
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Admission Date</label>
                                        <input type="date" name="admission_date" class="form-control" value="${new Date().toISOString().split('T')[0]}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Ward</label>
                                        <select name="ward" class="form-select">
                                            <option value="General">General Ward</option>
                                            <option value="Semi-Private">Semi-Private</option>
                                            <option value="Private">Private Room</option>
                                            <option value="ICU">ICU</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Bed Number</label>
                                        <select name="bed_number" class="form-select">
                                            <option value="">Select Bed</option>
                                            ${(bedsRes.data || []).filter(b => b.status === 'available').map(b =>
                                                `<option value="${b.bed_number}">${b.bed_number} - ${b.ward}</option>`
                                            ).join('')}
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Admission Reason</label>
                                    <textarea name="admission_reason" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Initial Diagnosis</label>
                                    <textarea name="diagnosis" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="#/ipd" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Admit Patient</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.getElementById('ipd-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target).entries());
            data.status = 'admitted';
            const response = await API.ipd.create(data);
            if (response.success) {
                App.showToast('Success', 'Patient admitted', 'success');
                Router.navigate('/ipd');
            } else {
                App.showToast('Error', response.message || 'Failed', 'danger');
            }
        });
    },

    async view(params) {
        App.showLoading();
        const response = await API.ipd.getById(params.id);
        if (!response.success) {
            App.showToast('Error', 'IPD record not found', 'danger');
            Router.navigate('/ipd');
            return;
        }
        const ipd = response.data;

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-hospital me-2"></i>IPD Details</h1>
                <div class="quick-actions">
                    ${ipd.status === 'admitted' ? `
                        <a href="#/ipd/${ipd.id}/discharge" class="btn btn-success"><i class="bi bi-box-arrow-right me-2"></i>Discharge</a>
                    ` : ''}
                    <a href="#/ipd" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">Admission Information</div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr><th>Patient:</th><td>${ipd.patient_name || 'N/A'}</td></tr>
                                <tr><th>Doctor:</th><td>${ipd.doctor_name || 'N/A'}</td></tr>
                                <tr><th>Admission Date:</th><td>${App.formatDate(ipd.admission_date)}</td></tr>
                                <tr><th>Ward:</th><td>${ipd.ward || 'N/A'}</td></tr>
                                <tr><th>Bed:</th><td>${ipd.bed_number || 'N/A'}</td></tr>
                                <tr><th>Status:</th><td>${App.getStatusBadge(ipd.status)}</td></tr>
                                ${ipd.discharge_date ? `<tr><th>Discharge Date:</th><td>${App.formatDate(ipd.discharge_date)}</td></tr>` : ''}
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">Medical Details</div>
                        <div class="card-body">
                            <h6>Admission Reason</h6>
                            <p>${ipd.admission_reason || 'N/A'}</p>
                            <h6>Diagnosis</h6>
                            <p>${ipd.diagnosis || 'N/A'}</p>
                            ${ipd.discharge_notes ? `<h6>Discharge Notes</h6><p>${ipd.discharge_notes}</p>` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
    },

    async discharge(params) {
        App.showLoading();
        const response = await API.ipd.getById(params.id);
        if (!response.success) {
            App.showToast('Error', 'IPD record not found', 'danger');
            Router.navigate('/ipd');
            return;
        }
        const ipd = response.data;

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-box-arrow-right me-2"></i>Discharge Patient</h1>
                <a href="#/ipd/${ipd.id}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <form id="discharge-form">
                        <div class="card">
                            <div class="card-header">Patient: ${ipd.patient_name}</div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Discharge Date</label>
                                    <input type="date" name="discharge_date" class="form-control" value="${new Date().toISOString().split('T')[0]}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Discharge Notes</label>
                                    <textarea name="discharge_notes" class="form-control" rows="3" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Follow-up Instructions</label>
                                    <textarea name="followup_instructions" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="#/ipd/${ipd.id}" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-2"></i>Discharge</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.getElementById('discharge-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target).entries());
            const response = await API.ipd.discharge(params.id, data);
            if (response.success) {
                App.showToast('Success', 'Patient discharged', 'success');
                Router.navigate('/ipd');
            } else {
                App.showToast('Error', response.message || 'Failed', 'danger');
            }
        });
    },

    async delete(id) {
        App.confirmDelete(async () => {
            const response = await API.ipd.delete(id);
            if (response.success) {
                App.showToast('Success', 'IPD record deleted', 'success');
                IPDPage.list();
            } else {
                App.showToast('Error', response.message || 'Failed', 'danger');
            }
        });
    }
};
