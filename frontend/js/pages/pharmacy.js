/**
 * Pharmacy Page
 */
const PharmacyPage = {
    async list() {
        App.showLoading();
        const response = await API.pharmacy.getMedicines();

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-capsule me-2"></i>Pharmacy</h1>
                <div class="quick-actions">
                    <a href="#/pharmacy/dispense" class="btn btn-success"><i class="bi bi-cart-plus me-2"></i>Dispense</a>
                    <a href="#/pharmacy/add" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Add Medicine</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Medicine Inventory (${response.data?.length || 0})</div>
                <div class="card-body">
                    ${!response.data?.length ? `
                        <div class="empty-state">
                            <i class="bi bi-capsule"></i>
                            <h5>No Medicines</h5>
                            <a href="#/pharmacy/add" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Add Medicine</a>
                        </div>
                    ` : `
                        <div class="table-responsive">
                            <table class="table table-hover" id="pharmacy-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Stock</th>
                                        <th>Price</th>
                                        <th>Expiry</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${response.data.map(med => `
                                        <tr class="${med.stock <= med.reorder_level ? 'table-warning' : ''}">
                                            <td><strong>${med.name}</strong><br><small class="text-muted">${med.generic_name || ''}</small></td>
                                            <td>${med.category || 'N/A'}</td>
                                            <td>${med.stock} ${med.unit || ''}</td>
                                            <td>${App.formatCurrency(med.price)}</td>
                                            <td>${med.expiry_date ? App.formatDate(med.expiry_date) : 'N/A'}</td>
                                            <td class="action-btns">
                                                <a href="#/pharmacy/${med.id}/edit" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                                <button class="btn btn-sm btn-danger" onclick="PharmacyPage.delete(${med.id})"><i class="bi bi-trash"></i></button>
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

        if (response.data?.length) App.initDataTable('#pharmacy-table');
    },

    async addMedicine() {
        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-plus-lg me-2"></i>Add Medicine</h1>
                <a href="#/pharmacy" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <form id="medicine-form">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Medicine Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Generic Name</label>
                                        <input type="text" name="generic_name" class="form-control">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Category</label>
                                        <select name="category" class="form-select">
                                            <option value="Tablet">Tablet</option>
                                            <option value="Capsule">Capsule</option>
                                            <option value="Syrup">Syrup</option>
                                            <option value="Injection">Injection</option>
                                            <option value="Cream">Cream/Ointment</option>
                                            <option value="Drops">Drops</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Unit</label>
                                        <input type="text" name="unit" class="form-control" placeholder="e.g., tablets, ml, units">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Price <span class="text-danger">*</span></label>
                                        <input type="number" name="price" class="form-control" min="0" step="0.01" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Current Stock</label>
                                        <input type="number" name="stock" class="form-control" min="0" value="0">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Reorder Level</label>
                                        <input type="number" name="reorder_level" class="form-control" min="0" value="10">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Expiry Date</label>
                                        <input type="date" name="expiry_date" class="form-control">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="#/pharmacy" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Save</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.getElementById('medicine-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target).entries());
            const response = await API.pharmacy.createMedicine(data);
            if (response.success) {
                App.showToast('Success', 'Medicine added', 'success');
                Router.navigate('/pharmacy');
            } else {
                App.showToast('Error', response.message || 'Failed', 'danger');
            }
        });
    },

    async editMedicine(params) {
        App.showLoading();
        const response = await API.pharmacy.getMedicineById(params.id);
        if (!response.success) {
            App.showToast('Error', 'Medicine not found', 'danger');
            Router.navigate('/pharmacy');
            return;
        }
        const med = response.data;

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-pencil me-2"></i>Edit Medicine</h1>
                <a href="#/pharmacy" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <form id="medicine-form">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Medicine Name</label>
                                        <input type="text" name="name" class="form-control" value="${med.name || ''}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Generic Name</label>
                                        <input type="text" name="generic_name" class="form-control" value="${med.generic_name || ''}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Category</label>
                                        <select name="category" class="form-select">
                                            ${['Tablet', 'Capsule', 'Syrup', 'Injection', 'Cream', 'Drops', 'Other'].map(c =>
                                                `<option value="${c}" ${med.category === c ? 'selected' : ''}>${c}</option>`
                                            ).join('')}
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Unit</label>
                                        <input type="text" name="unit" class="form-control" value="${med.unit || ''}">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Price</label>
                                        <input type="number" name="price" class="form-control" value="${med.price || ''}" min="0" step="0.01" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Stock</label>
                                        <input type="number" name="stock" class="form-control" value="${med.stock || 0}" min="0">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Reorder Level</label>
                                        <input type="number" name="reorder_level" class="form-control" value="${med.reorder_level || 10}" min="0">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Expiry Date</label>
                                        <input type="date" name="expiry_date" class="form-control" value="${med.expiry_date || ''}">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="#/pharmacy" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Update</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.getElementById('medicine-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target).entries());
            const response = await API.pharmacy.updateMedicine(params.id, data);
            if (response.success) {
                App.showToast('Success', 'Medicine updated', 'success');
                Router.navigate('/pharmacy');
            } else {
                App.showToast('Error', response.message || 'Failed', 'danger');
            }
        });
    },

    async dispense() {
        const [patientsRes, medicinesRes] = await Promise.all([
            API.patients.getAll(),
            API.pharmacy.getMedicines()
        ]);

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-cart-plus me-2"></i>Dispense Medicine</h1>
                <a href="#/pharmacy" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <form id="dispense-form">
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
                                    <label class="form-label">Medicine <span class="text-danger">*</span></label>
                                    <select name="medicine_id" id="medicine-select" class="form-select" required>
                                        <option value="">Select Medicine</option>
                                        ${(medicinesRes.data || []).map(m =>
                                            `<option value="${m.id}" data-price="${m.price}" data-stock="${m.stock}">${m.name} (Stock: ${m.stock})</option>`
                                        ).join('')}
                                    </select>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                        <input type="number" name="quantity" id="quantity-input" class="form-control" min="1" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Unit Price</label>
                                        <input type="number" name="unit_price" id="unit-price" class="form-control" readonly>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Total</label>
                                        <input type="number" name="total" id="total-price" class="form-control" readonly>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="#/pharmacy" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-2"></i>Dispense</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        `;

        const medSelect = document.getElementById('medicine-select');
        const qtyInput = document.getElementById('quantity-input');
        const unitPrice = document.getElementById('unit-price');
        const totalPrice = document.getElementById('total-price');

        const updateTotal = () => {
            const selected = medSelect.options[medSelect.selectedIndex];
            const price = parseFloat(selected?.dataset?.price || 0);
            const qty = parseInt(qtyInput.value || 0);
            unitPrice.value = price;
            totalPrice.value = (price * qty).toFixed(2);
        };

        medSelect.addEventListener('change', updateTotal);
        qtyInput.addEventListener('input', updateTotal);

        document.getElementById('dispense-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target).entries());
            const response = await API.pharmacy.dispense(data);
            if (response.success) {
                App.showToast('Success', 'Medicine dispensed', 'success');
                Router.navigate('/pharmacy');
            } else {
                App.showToast('Error', response.message || 'Failed', 'danger');
            }
        });
    },

    async delete(id) {
        App.confirmDelete(async () => {
            const response = await API.pharmacy.deleteMedicine(id);
            if (response.success) {
                App.showToast('Success', 'Medicine deleted', 'success');
                PharmacyPage.list();
            } else {
                App.showToast('Error', response.message || 'Failed', 'danger');
            }
        });
    }
};
