/**
 * OPD Page
 */
const OPDPage = {
    async list() {
        App.showLoading();
        const response = await API.opd.getAll();

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-clipboard2-pulse me-2"></i>OPD Visits</h1>
                <div class="quick-actions">
                    <a href="#/opd/add" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>New OPD Visit</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">OPD Records (${response.data?.length || 0})</div>
                <div class="card-body">
                    ${!response.data?.length ? `
                        <div class="empty-state">
                            <i class="bi bi-clipboard2-pulse"></i>
                            <h5>No OPD Visits</h5>
                            <a href="#/opd/add" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>New OPD Visit</a>
                        </div>
                    ` : `
                        <div class="table-responsive">
                            <table class="table table-hover" id="opd-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Patient</th>
                                        <th>Doctor</th>
                                        <th>Diagnosis</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${response.data.map(visit => `
                                        <tr>
                                            <td>${App.formatDate(visit.date)}</td>
                                            <td>${visit.patient_name || 'N/A'}</td>
                                            <td>${visit.doctor_name || 'N/A'}</td>
                                            <td>${visit.diagnosis || 'N/A'}</td>
                                            <td class="action-btns">
                                                <a href="#/opd/${visit.id}" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
                                                <button class="btn btn-sm btn-danger" onclick="OPDPage.delete(${visit.id})"><i class="bi bi-trash"></i></button>
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

        if (response.data?.length) App.initDataTable('#opd-table');
    },

    async add() {
        const [patientsRes, doctorsRes] = await Promise.all([
            API.patients.getAll(),
            API.doctors.getAll()
        ]);
        const params = Router.getParams();

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-clipboard-plus me-2"></i>New OPD Visit</h1>
                <a href="#/opd" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <form id="opd-form">
                        <div class="card">
                            <div class="card-header">Visit Details</div>
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
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Date</label>
                                        <input type="date" name="date" class="form-control" value="${new Date().toISOString().split('T')[0]}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Consultation Fee</label>
                                        <input type="number" name="consultation_fee" class="form-control" min="0" value="500">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">Vitals</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">BP (mmHg)</label>
                                        <input type="text" name="bp" class="form-control" placeholder="120/80">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Temperature (°F)</label>
                                        <input type="text" name="temperature" class="form-control" placeholder="98.6">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Pulse (bpm)</label>
                                        <input type="text" name="pulse" class="form-control" placeholder="72">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Weight (kg)</label>
                                        <input type="text" name="weight" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">Consultation</div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Chief Complaints</label>
                                    <textarea name="complaints" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Diagnosis</label>
                                    <textarea name="diagnosis" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Prescription</label>
                                    <textarea name="prescription" class="form-control" rows="4" placeholder="Medicine name - Dosage - Duration"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="#/opd" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Save OPD Visit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.getElementById('opd-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target).entries());
            const response = await API.opd.create(data);
            if (response.success) {
                App.showToast('Success', 'OPD visit recorded', 'success');
                Router.navigate('/opd');
            } else {
                App.showToast('Error', response.message || 'Failed', 'danger');
            }
        });
    },

    async view(params) {
        App.showLoading();
        const response = await API.opd.getById(params.id);
        if (!response.success) {
            App.showToast('Error', 'OPD record not found', 'danger');
            Router.navigate('/opd');
            return;
        }
        const visit = response.data;

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-clipboard2-pulse me-2"></i>OPD Visit Details</h1>
                <div class="quick-actions">
                    <button class="btn btn-secondary" onclick="window.print()"><i class="bi bi-printer me-2"></i>Print</button>
                    <a href="#/opd" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">Visit Information</div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr><th>Date:</th><td>${App.formatDate(visit.date)}</td></tr>
                                <tr><th>Patient:</th><td>${visit.patient_name || 'N/A'}</td></tr>
                                <tr><th>Doctor:</th><td>${visit.doctor_name || 'N/A'}</td></tr>
                                <tr><th>Fee:</th><td>${App.formatCurrency(visit.consultation_fee)}</td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">Vitals</div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-3"><strong>BP</strong><br>${visit.bp || 'N/A'}</div>
                                <div class="col-3"><strong>Temp</strong><br>${visit.temperature || 'N/A'}</div>
                                <div class="col-3"><strong>Pulse</strong><br>${visit.pulse || 'N/A'}</div>
                                <div class="col-3"><strong>Weight</strong><br>${visit.weight || 'N/A'}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">Consultation</div>
                        <div class="card-body">
                            <h6>Complaints</h6>
                            <p>${visit.complaints || 'N/A'}</p>
                            <h6>Diagnosis</h6>
                            <p>${visit.diagnosis || 'N/A'}</p>
                            <h6>Prescription</h6>
                            <pre style="white-space: pre-wrap;">${visit.prescription || 'N/A'}</pre>
                            <h6>Notes</h6>
                            <p>${visit.notes || 'N/A'}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
    },

    async delete(id) {
        App.confirmDelete(async () => {
            const response = await API.opd.delete(id);
            if (response.success) {
                App.showToast('Success', 'OPD record deleted', 'success');
                OPDPage.list();
            } else {
                App.showToast('Error', response.message || 'Failed', 'danger');
            }
        });
    }
};
