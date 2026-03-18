/**
 * Patients Page
 */
const PatientsPage = {
    async list() {
        App.showLoading();

        const response = await API.patients.getAll();

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-people me-2"></i>Patients</h1>
                <div class="quick-actions">
                    <a href="#/patients/add" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>Add Patient
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">All Patients (${response.data?.length || 0})</div>
                <div class="card-body">
                    ${!response.data?.length ? `
                        <div class="empty-state">
                            <i class="bi bi-people"></i>
                            <h5>No Patients Found</h5>
                            <p>Start by adding your first patient.</p>
                            <a href="#/patients/add" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-2"></i>Add Patient
                            </a>
                        </div>
                    ` : `
                        <div class="table-responsive">
                            <table class="table table-hover" id="patients-table">
                                <thead>
                                    <tr>
                                        <th>Patient ID</th>
                                        <th>Name</th>
                                        <th>Age/Gender</th>
                                        <th>Phone</th>
                                        <th>Blood Group</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${response.data.map(patient => `
                                        <tr>
                                            <td><strong>${patient.patient_id}</strong></td>
                                            <td>
                                                <a href="#/patients/${patient.id}">${patient.name}</a>
                                            </td>
                                            <td>${patient.age || 'N/A'} / ${patient.gender || 'N/A'}</td>
                                            <td>${patient.phone || 'N/A'}</td>
                                            <td>${patient.blood_group || 'N/A'}</td>
                                            <td class="action-btns">
                                                <a href="#/patients/${patient.id}" class="btn btn-sm btn-info" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="#/patients/${patient.id}/edit" class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button class="btn btn-sm btn-danger" onclick="PatientsPage.delete(${patient.id})" title="Delete">
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

        if (response.data?.length) {
            App.initDataTable('#patients-table');
        }
    },

    async add() {
        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-person-plus me-2"></i>Add Patient</h1>
                <a href="#/patients" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back
                </a>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <form id="patient-form">
                        <div class="card">
                            <div class="card-header"><i class="bi bi-person me-2"></i>Basic Information</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Date of Birth</label>
                                        <input type="date" name="dob" class="form-control">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Gender</label>
                                        <select name="gender" class="form-select">
                                            <option value="">Select</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Phone <span class="text-danger">*</span></label>
                                        <input type="tel" name="phone" class="form-control" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Blood Group</label>
                                        <select name="blood_group" class="form-select">
                                            <option value="">Select</option>
                                            <option value="A+">A+</option>
                                            <option value="A-">A-</option>
                                            <option value="B+">B+</option>
                                            <option value="B-">B-</option>
                                            <option value="AB+">AB+</option>
                                            <option value="AB-">AB-</option>
                                            <option value="O+">O+</option>
                                            <option value="O-">O-</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea name="address" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header"><i class="bi bi-telephone me-2"></i>Emergency Contact</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Contact Name</label>
                                        <input type="text" name="emergency_contact_name" class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Contact Phone</label>
                                        <input type="tel" name="emergency_contact_phone" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header"><i class="bi bi-heart-pulse me-2"></i>Medical Information</div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Allergies</label>
                                    <textarea name="allergies" class="form-control" rows="2" placeholder="List any known allergies..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Medical History</label>
                                    <textarea name="medical_history" class="form-control" rows="3" placeholder="Previous conditions, surgeries, etc..."></textarea>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="#/patients" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg me-2"></i>Save Patient
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.getElementById('patient-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());

            const response = await API.patients.create(data);

            if (response.success) {
                App.showToast('Success', 'Patient added successfully', 'success');
                Router.navigate('/patients');
            } else {
                App.showToast('Error', response.message || 'Failed to add patient', 'danger');
            }
        });
    },

    async view(params) {
        App.showLoading();

        const response = await API.patients.getById(params.id);

        if (!response.success) {
            App.showToast('Error', 'Patient not found', 'danger');
            Router.navigate('/patients');
            return;
        }

        const patient = response.data;

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-person me-2"></i>${patient.name}</h1>
                <div class="quick-actions">
                    <a href="#/patients/${patient.id}/edit" class="btn btn-warning">
                        <i class="bi bi-pencil me-2"></i>Edit
                    </a>
                    <a href="#/appointments/add?patient_id=${patient.id}" class="btn btn-success">
                        <i class="bi bi-calendar-plus me-2"></i>Book Appointment
                    </a>
                    <a href="#/patients" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">Patient Information</div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr><th>Patient ID:</th><td><strong>${patient.patient_id}</strong></td></tr>
                                <tr><th>Name:</th><td>${patient.name}</td></tr>
                                <tr><th>Age:</th><td>${patient.age || 'N/A'}</td></tr>
                                <tr><th>Gender:</th><td>${patient.gender || 'N/A'}</td></tr>
                                <tr><th>Blood Group:</th><td>${patient.blood_group || 'N/A'}</td></tr>
                                <tr><th>Phone:</th><td>${patient.phone || 'N/A'}</td></tr>
                                <tr><th>Email:</th><td>${patient.email || 'N/A'}</td></tr>
                                <tr><th>Address:</th><td>${patient.address || 'N/A'}</td></tr>
                            </table>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">Emergency Contact</div>
                        <div class="card-body">
                            <p><strong>Name:</strong> ${patient.emergency_contact_name || 'N/A'}</p>
                            <p><strong>Phone:</strong> ${patient.emergency_contact_phone || 'N/A'}</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">Medical Information</div>
                        <div class="card-body">
                            <h6>Allergies</h6>
                            <p>${patient.allergies || 'None recorded'}</p>
                            <hr>
                            <h6>Medical History</h6>
                            <p>${patient.medical_history || 'None recorded'}</p>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">Quick Actions</div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-2">
                                <a href="#/opd/add?patient_id=${patient.id}" class="btn btn-outline-primary">
                                    <i class="bi bi-clipboard-plus me-2"></i>New OPD Visit
                                </a>
                                <a href="#/ipd/add?patient_id=${patient.id}" class="btn btn-outline-info">
                                    <i class="bi bi-hospital me-2"></i>Admit to IPD
                                </a>
                                <a href="#/billing/add?patient_id=${patient.id}" class="btn btn-outline-warning">
                                    <i class="bi bi-receipt me-2"></i>Create Invoice
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

        const response = await API.patients.getById(params.id);

        if (!response.success) {
            App.showToast('Error', 'Patient not found', 'danger');
            Router.navigate('/patients');
            return;
        }

        const patient = response.data;

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-pencil me-2"></i>Edit Patient</h1>
                <a href="#/patients/${patient.id}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back
                </a>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <form id="patient-form">
                        <div class="card">
                            <div class="card-header"><i class="bi bi-person me-2"></i>Basic Information</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" value="${patient.name || ''}" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Date of Birth</label>
                                        <input type="date" name="dob" class="form-control" value="${patient.dob || ''}">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Gender</label>
                                        <select name="gender" class="form-select">
                                            <option value="">Select</option>
                                            <option value="Male" ${patient.gender === 'Male' ? 'selected' : ''}>Male</option>
                                            <option value="Female" ${patient.gender === 'Female' ? 'selected' : ''}>Female</option>
                                            <option value="Other" ${patient.gender === 'Other' ? 'selected' : ''}>Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Phone <span class="text-danger">*</span></label>
                                        <input type="tel" name="phone" class="form-control" value="${patient.phone || ''}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="${patient.email || ''}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Blood Group</label>
                                        <select name="blood_group" class="form-select">
                                            <option value="">Select</option>
                                            ${['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'].map(bg =>
                                                `<option value="${bg}" ${patient.blood_group === bg ? 'selected' : ''}>${bg}</option>`
                                            ).join('')}
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea name="address" class="form-control" rows="2">${patient.address || ''}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header"><i class="bi bi-telephone me-2"></i>Emergency Contact</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Contact Name</label>
                                        <input type="text" name="emergency_contact_name" class="form-control" value="${patient.emergency_contact_name || ''}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Contact Phone</label>
                                        <input type="tel" name="emergency_contact_phone" class="form-control" value="${patient.emergency_contact_phone || ''}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header"><i class="bi bi-heart-pulse me-2"></i>Medical Information</div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Allergies</label>
                                    <textarea name="allergies" class="form-control" rows="2">${patient.allergies || ''}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Medical History</label>
                                    <textarea name="medical_history" class="form-control" rows="3">${patient.medical_history || ''}</textarea>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="#/patients/${patient.id}" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg me-2"></i>Update Patient
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.getElementById('patient-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());

            const response = await API.patients.update(params.id, data);

            if (response.success) {
                App.showToast('Success', 'Patient updated successfully', 'success');
                Router.navigate(`/patients/${params.id}`);
            } else {
                App.showToast('Error', response.message || 'Failed to update patient', 'danger');
            }
        });
    },

    async delete(id) {
        App.confirmDelete(async () => {
            const response = await API.patients.delete(id);

            if (response.success) {
                App.showToast('Success', 'Patient deleted successfully', 'success');
                PatientsPage.list();
            } else {
                App.showToast('Error', response.message || 'Failed to delete patient', 'danger');
            }
        });
    }
};
