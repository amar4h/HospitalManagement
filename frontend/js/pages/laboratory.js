/**
 * Laboratory Page
 */
const LaboratoryPage = {
    async list() {
        App.showLoading();
        const response = await API.laboratory.getOrders();

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-droplet me-2"></i>Laboratory</h1>
                <div class="quick-actions">
                    <a href="#/laboratory/add" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>New Test Order</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Lab Orders (${response.data?.length || 0})</div>
                <div class="card-body">
                    ${!response.data?.length ? `
                        <div class="empty-state">
                            <i class="bi bi-droplet"></i>
                            <h5>No Lab Orders</h5>
                            <a href="#/laboratory/add" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>New Test Order</a>
                        </div>
                    ` : `
                        <div class="table-responsive">
                            <table class="table table-hover" id="lab-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Patient</th>
                                        <th>Test</th>
                                        <th>Ordered By</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${response.data.map(order => `
                                        <tr>
                                            <td>${App.formatDate(order.order_date)}</td>
                                            <td>${order.patient_name || 'N/A'}</td>
                                            <td>${order.test_name || 'N/A'}</td>
                                            <td>${order.doctor_name || 'N/A'}</td>
                                            <td>${App.getStatusBadge(order.status)}</td>
                                            <td class="action-btns">
                                                ${order.status === 'pending' ? `
                                                    <a href="#/laboratory/${order.id}/result" class="btn btn-sm btn-success"><i class="bi bi-clipboard-check"></i></a>
                                                ` : ''}
                                                <button class="btn btn-sm btn-danger" onclick="LaboratoryPage.delete(${order.id})"><i class="bi bi-trash"></i></button>
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

        if (response.data?.length) App.initDataTable('#lab-table');
    },

    async addOrder() {
        const [patientsRes, doctorsRes, testsRes] = await Promise.all([
            API.patients.getAll(),
            API.doctors.getAll(),
            API.laboratory.getTests()
        ]);

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-plus-lg me-2"></i>New Lab Test Order</h1>
                <a href="#/laboratory" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <form id="lab-order-form">
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Patient <span class="text-danger">*</span></label>
                                    <select name="patient_id" class="form-select" required>
                                        <option value="">Select Patient</option>
                                        ${(patientsRes.data || []).map(p =>
                                            `<option value="${p.id}">${p.name} (${p.patient_id})</option>`
                                        ).join('')}
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Ordering Doctor <span class="text-danger">*</span></label>
                                    <select name="doctor_id" class="form-select" required>
                                        <option value="">Select Doctor</option>
                                        ${(doctorsRes.data || []).filter(d => d.status === 'active').map(d =>
                                            `<option value="${d.id}">${d.name}</option>`
                                        ).join('')}
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Test <span class="text-danger">*</span></label>
                                    <select name="test_id" class="form-select" required>
                                        <option value="">Select Test</option>
                                        ${(testsRes.data || []).map(t =>
                                            `<option value="${t.id}">${t.name} - ${App.formatCurrency(t.price)}</option>`
                                        ).join('')}
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Priority</label>
                                    <select name="priority" class="form-select">
                                        <option value="normal">Normal</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="#/laboratory" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Create Order</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.getElementById('lab-order-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target).entries());
            data.status = 'pending';
            data.order_date = new Date().toISOString().split('T')[0];
            const response = await API.laboratory.createOrder(data);
            if (response.success) {
                App.showToast('Success', 'Lab order created', 'success');
                Router.navigate('/laboratory');
            } else {
                App.showToast('Error', response.message || 'Failed', 'danger');
            }
        });
    },

    async enterResult(params) {
        App.showLoading();
        const response = await API.laboratory.getOrderById(params.id);
        if (!response.success) {
            App.showToast('Error', 'Lab order not found', 'danger');
            Router.navigate('/laboratory');
            return;
        }
        const order = response.data;

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-clipboard-check me-2"></i>Enter Test Result</h1>
                <a href="#/laboratory" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <form id="result-form">
                        <div class="card">
                            <div class="card-header">Order Details</div>
                            <div class="card-body">
                                <p><strong>Patient:</strong> ${order.patient_name}</p>
                                <p><strong>Test:</strong> ${order.test_name}</p>
                                <p><strong>Order Date:</strong> ${App.formatDate(order.order_date)}</p>
                                <hr>
                                <div class="mb-3">
                                    <label class="form-label">Result <span class="text-danger">*</span></label>
                                    <textarea name="result" class="form-control" rows="4" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Normal Range (Reference)</label>
                                    <input type="text" name="normal_range" class="form-control" placeholder="e.g., 70-100 mg/dL">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Remarks</label>
                                    <textarea name="remarks" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="#/laboratory" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-2"></i>Save Result</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.getElementById('result-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target).entries());
            data.result_date = new Date().toISOString().split('T')[0];
            const response = await API.laboratory.updateResult(params.id, data);
            if (response.success) {
                App.showToast('Success', 'Result saved', 'success');
                Router.navigate('/laboratory');
            } else {
                App.showToast('Error', response.message || 'Failed', 'danger');
            }
        });
    },

    async delete(id) {
        App.confirmDelete(async () => {
            const response = await API.laboratory.deleteOrder(id);
            if (response.success) {
                App.showToast('Success', 'Lab order deleted', 'success');
                LaboratoryPage.list();
            } else {
                App.showToast('Error', response.message || 'Failed', 'danger');
            }
        });
    }
};
