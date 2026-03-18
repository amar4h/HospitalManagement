/**
 * Billing Page
 */
const BillingPage = {
    async list() {
        App.showLoading();
        const response = await API.billing.getInvoices();

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-receipt me-2"></i>Billing</h1>
                <div class="quick-actions">
                    <a href="#/billing/add" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>New Invoice</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Invoices (${response.data?.length || 0})</div>
                <div class="card-body">
                    ${!response.data?.length ? `
                        <div class="empty-state">
                            <i class="bi bi-receipt"></i>
                            <h5>No Invoices</h5>
                            <a href="#/billing/add" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>New Invoice</a>
                        </div>
                    ` : `
                        <div class="table-responsive">
                            <table class="table table-hover" id="billing-table">
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Date</th>
                                        <th>Patient</th>
                                        <th>Total</th>
                                        <th>Paid</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${response.data.map(inv => `
                                        <tr>
                                            <td><strong>${inv.invoice_number}</strong></td>
                                            <td>${App.formatDate(inv.date)}</td>
                                            <td>${inv.patient_name || 'N/A'}</td>
                                            <td>${App.formatCurrency(inv.total)}</td>
                                            <td>${App.formatCurrency(inv.paid || 0)}</td>
                                            <td>${App.getStatusBadge(inv.status)}</td>
                                            <td class="action-btns">
                                                <a href="#/billing/${inv.id}" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
                                                ${inv.status !== 'paid' ? `
                                                    <a href="#/billing/${inv.id}/payment" class="btn btn-sm btn-success"><i class="bi bi-cash"></i></a>
                                                ` : ''}
                                                <button class="btn btn-sm btn-danger" onclick="BillingPage.delete(${inv.id})"><i class="bi bi-trash"></i></button>
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

        if (response.data?.length) App.initDataTable('#billing-table');
    },

    async addInvoice() {
        const patientsRes = await API.patients.getAll();
        const params = Router.getParams();

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-plus-lg me-2"></i>New Invoice</h1>
                <a href="#/billing" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <form id="invoice-form">
                        <div class="card">
                            <div class="card-header">Invoice Details</div>
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
                                        <label class="form-label">Date</label>
                                        <input type="date" name="date" class="form-control" value="${new Date().toISOString().split('T')[0]}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Invoice Items</span>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="add-item-btn">
                                    <i class="bi bi-plus-lg me-1"></i>Add Item
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="invoice-items">
                                    <div class="invoice-item row mb-2">
                                        <div class="col-md-5">
                                            <input type="text" name="items[0][description]" class="form-control" placeholder="Description" required>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" name="items[0][quantity]" class="form-control item-qty" placeholder="Qty" value="1" min="1" required>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="items[0][price]" class="form-control item-price" placeholder="Price" min="0" step="0.01" required>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-outline-danger remove-item-btn"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6 offset-md-6">
                                        <div class="d-flex justify-content-between mb-2">
                                            <strong>Subtotal:</strong>
                                            <span id="subtotal">0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <label>Discount:</label>
                                            <input type="number" name="discount" id="discount-input" class="form-control form-control-sm" style="width: 100px" min="0" value="0">
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <strong>Total:</strong>
                                            <strong id="total">0.00</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mb-4">
                            <a href="#/billing" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Create Invoice</button>
                        </div>
                    </form>
                </div>
            </div>
        `;

        let itemIndex = 1;

        document.getElementById('add-item-btn').addEventListener('click', () => {
            const container = document.getElementById('invoice-items');
            const newItem = document.createElement('div');
            newItem.className = 'invoice-item row mb-2';
            newItem.innerHTML = `
                <div class="col-md-5">
                    <input type="text" name="items[${itemIndex}][description]" class="form-control" placeholder="Description" required>
                </div>
                <div class="col-md-2">
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control item-qty" placeholder="Qty" value="1" min="1" required>
                </div>
                <div class="col-md-3">
                    <input type="number" name="items[${itemIndex}][price]" class="form-control item-price" placeholder="Price" min="0" step="0.01" required>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger remove-item-btn"><i class="bi bi-trash"></i></button>
                </div>
            `;
            container.appendChild(newItem);
            itemIndex++;
            bindItemEvents();
        });

        const calculateTotals = () => {
            let subtotal = 0;
            document.querySelectorAll('.invoice-item').forEach(item => {
                const qty = parseFloat(item.querySelector('.item-qty')?.value || 0);
                const price = parseFloat(item.querySelector('.item-price')?.value || 0);
                subtotal += qty * price;
            });
            const discount = parseFloat(document.getElementById('discount-input').value || 0);
            document.getElementById('subtotal').textContent = subtotal.toFixed(2);
            document.getElementById('total').textContent = (subtotal - discount).toFixed(2);
        };

        const bindItemEvents = () => {
            document.querySelectorAll('.remove-item-btn').forEach(btn => {
                btn.onclick = () => {
                    if (document.querySelectorAll('.invoice-item').length > 1) {
                        btn.closest('.invoice-item').remove();
                        calculateTotals();
                    }
                };
            });
            document.querySelectorAll('.item-qty, .item-price').forEach(input => {
                input.oninput = calculateTotals;
            });
        };

        bindItemEvents();
        document.getElementById('discount-input').addEventListener('input', calculateTotals);

        document.getElementById('invoice-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = {
                patient_id: formData.get('patient_id'),
                date: formData.get('date'),
                discount: parseFloat(formData.get('discount') || 0),
                items: [],
                status: 'pending'
            };

            document.querySelectorAll('.invoice-item').forEach((item, i) => {
                const desc = item.querySelector(`[name="items[${i}][description]"]`)?.value;
                const qty = item.querySelector('.item-qty')?.value;
                const price = item.querySelector('.item-price')?.value;
                if (desc) {
                    data.items.push({ description: desc, quantity: parseInt(qty), price: parseFloat(price) });
                }
            });

            data.total = data.items.reduce((sum, item) => sum + item.quantity * item.price, 0) - data.discount;

            const response = await API.billing.createInvoice(data);
            if (response.success) {
                App.showToast('Success', 'Invoice created', 'success');
                Router.navigate('/billing');
            } else {
                App.showToast('Error', response.message || 'Failed', 'danger');
            }
        });
    },

    async viewInvoice(params) {
        App.showLoading();
        const response = await API.billing.getInvoiceById(params.id);
        if (!response.success) {
            App.showToast('Error', 'Invoice not found', 'danger');
            Router.navigate('/billing');
            return;
        }
        const inv = response.data;

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-receipt me-2"></i>Invoice ${inv.invoice_number}</h1>
                <div class="quick-actions">
                    <button class="btn btn-secondary" onclick="window.print()"><i class="bi bi-printer me-2"></i>Print</button>
                    ${inv.status !== 'paid' ? `
                        <a href="#/billing/${inv.id}/payment" class="btn btn-success"><i class="bi bi-cash me-2"></i>Add Payment</a>
                    ` : ''}
                    <a href="#/billing" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Patient</h5>
                            <p class="mb-0">${inv.patient_name || 'N/A'}</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <h5>Invoice Details</h5>
                            <p class="mb-0"><strong>Invoice #:</strong> ${inv.invoice_number}</p>
                            <p class="mb-0"><strong>Date:</strong> ${App.formatDate(inv.date)}</p>
                            <p class="mb-0"><strong>Status:</strong> ${App.getStatusBadge(inv.status)}</p>
                        </div>
                    </div>
                    <hr>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${(inv.items || []).map(item => `
                                <tr>
                                    <td>${item.description}</td>
                                    <td class="text-center">${item.quantity}</td>
                                    <td class="text-end">${App.formatCurrency(item.price)}</td>
                                    <td class="text-end">${App.formatCurrency(item.quantity * item.price)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                <td class="text-end">${App.formatCurrency((inv.total || 0) + (inv.discount || 0))}</td>
                            </tr>
                            ${inv.discount ? `
                                <tr>
                                    <td colspan="3" class="text-end">Discount:</td>
                                    <td class="text-end text-danger">-${App.formatCurrency(inv.discount)}</td>
                                </tr>
                            ` : ''}
                            <tr>
                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                <td class="text-end"><strong>${App.formatCurrency(inv.total)}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end">Paid:</td>
                                <td class="text-end text-success">${App.formatCurrency(inv.paid || 0)}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Balance:</strong></td>
                                <td class="text-end"><strong>${App.formatCurrency((inv.total || 0) - (inv.paid || 0))}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        `;
    },

    async addPayment(params) {
        App.showLoading();
        const response = await API.billing.getInvoiceById(params.id);
        if (!response.success) {
            App.showToast('Error', 'Invoice not found', 'danger');
            Router.navigate('/billing');
            return;
        }
        const inv = response.data;
        const balance = (inv.total || 0) - (inv.paid || 0);

        document.getElementById('page-content').innerHTML = `
            <div class="page-header">
                <h1><i class="bi bi-cash me-2"></i>Add Payment</h1>
                <a href="#/billing/${inv.id}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <form id="payment-form">
                        <div class="card">
                            <div class="card-header">Invoice: ${inv.invoice_number}</div>
                            <div class="card-body">
                                <p><strong>Patient:</strong> ${inv.patient_name}</p>
                                <p><strong>Total:</strong> ${App.formatCurrency(inv.total)}</p>
                                <p><strong>Paid:</strong> ${App.formatCurrency(inv.paid || 0)}</p>
                                <p><strong>Balance:</strong> ${App.formatCurrency(balance)}</p>
                                <hr>
                                <div class="mb-3">
                                    <label class="form-label">Amount <span class="text-danger">*</span></label>
                                    <input type="number" name="amount" class="form-control" max="${balance}" min="0.01" step="0.01" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Payment Method</label>
                                    <select name="payment_method" class="form-select">
                                        <option value="Cash">Cash</option>
                                        <option value="Card">Card</option>
                                        <option value="UPI">UPI</option>
                                        <option value="Bank Transfer">Bank Transfer</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Reference</label>
                                    <input type="text" name="reference" class="form-control" placeholder="Transaction ID, receipt number, etc.">
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="#/billing/${inv.id}" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-2"></i>Record Payment</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.getElementById('payment-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target).entries());
            data.date = new Date().toISOString().split('T')[0];
            const response = await API.billing.addPayment(params.id, data);
            if (response.success) {
                App.showToast('Success', 'Payment recorded', 'success');
                Router.navigate(`/billing/${params.id}`);
            } else {
                App.showToast('Error', response.message || 'Failed', 'danger');
            }
        });
    },

    async delete(id) {
        App.confirmDelete(async () => {
            const response = await API.billing.deleteInvoice(id);
            if (response.success) {
                App.showToast('Success', 'Invoice deleted', 'success');
                BillingPage.list();
            } else {
                App.showToast('Error', response.message || 'Failed', 'danger');
            }
        });
    }
};
