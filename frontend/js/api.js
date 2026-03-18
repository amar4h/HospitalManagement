/**
 * API Service - Handles all communication with the backend
 */
const API = {
    /**
     * Make API request
     */
    async request(endpoint, options = {}) {
        const url = `${Config.API_URL}${endpoint}`;
        const token = Auth.getToken();

        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                ...(token && { 'Authorization': `Bearer ${token}` })
            }
        };

        const config = {
            ...defaultOptions,
            ...options,
            headers: {
                ...defaultOptions.headers,
                ...options.headers
            }
        };

        try {
            const response = await fetch(url, config);

            // Handle 401 Unauthorized
            if (response.status === 401) {
                Auth.logout();
                return { success: false, message: 'Session expired. Please login again.' };
            }

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('API Error:', error);
            return { success: false, message: 'Network error. Please check your connection.' };
        }
    },

    /**
     * GET request
     */
    async get(endpoint) {
        return this.request(endpoint, { method: 'GET' });
    },

    /**
     * POST request
     */
    async post(endpoint, data) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },

    /**
     * PUT request
     */
    async put(endpoint, data) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },

    /**
     * DELETE request
     */
    async delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    },

    // ============ AUTH ============
    auth: {
        login: (credentials) => API.post('/auth/login', credentials),
        logout: () => API.post('/auth/logout'),
        profile: () => API.get('/auth/profile'),
        changePassword: (data) => API.post('/auth/change-password', data)
    },

    // ============ DASHBOARD ============
    dashboard: {
        stats: () => API.get('/dashboard/stats'),
        recentPatients: () => API.get('/dashboard/recent-patients'),
        todayAppointments: () => API.get('/dashboard/today-appointments'),
        recentActivity: () => API.get('/dashboard/recent-activity')
    },

    // ============ PATIENTS ============
    patients: {
        getAll: () => API.get('/patients'),
        getById: (id) => API.get(`/patients/${id}`),
        create: (data) => API.post('/patients', data),
        update: (id, data) => API.put(`/patients/${id}`, data),
        delete: (id) => API.delete(`/patients/${id}`),
        search: (query) => API.get(`/patients/search?q=${encodeURIComponent(query)}`)
    },

    // ============ DOCTORS ============
    doctors: {
        getAll: () => API.get('/doctors'),
        getById: (id) => API.get(`/doctors/${id}`),
        create: (data) => API.post('/doctors', data),
        update: (id, data) => API.put(`/doctors/${id}`, data),
        delete: (id) => API.delete(`/doctors/${id}`),
        getSchedule: (id) => API.get(`/doctors/${id}/schedule`)
    },

    // ============ APPOINTMENTS ============
    appointments: {
        getAll: () => API.get('/appointments'),
        getById: (id) => API.get(`/appointments/${id}`),
        create: (data) => API.post('/appointments', data),
        update: (id, data) => API.put(`/appointments/${id}`, data),
        delete: (id) => API.delete(`/appointments/${id}`),
        getByDate: (date) => API.get(`/appointments/date/${date}`),
        getByDoctor: (doctorId) => API.get(`/appointments/doctor/${doctorId}`)
    },

    // ============ OPD ============
    opd: {
        getAll: () => API.get('/opd'),
        getById: (id) => API.get(`/opd/${id}`),
        create: (data) => API.post('/opd', data),
        update: (id, data) => API.put(`/opd/${id}`, data),
        delete: (id) => API.delete(`/opd/${id}`),
        addPrescription: (id, data) => API.post(`/opd/${id}/prescription`, data)
    },

    // ============ IPD ============
    ipd: {
        getAll: () => API.get('/ipd'),
        getById: (id) => API.get(`/ipd/${id}`),
        create: (data) => API.post('/ipd', data),
        update: (id, data) => API.put(`/ipd/${id}`, data),
        delete: (id) => API.delete(`/ipd/${id}`),
        discharge: (id, data) => API.post(`/ipd/${id}/discharge`, data),
        addTreatment: (id, data) => API.post(`/ipd/${id}/treatment`, data),
        getBeds: () => API.get('/ipd/beds')
    },

    // ============ SURGERY ============
    surgery: {
        getAll: () => API.get('/surgery'),
        getById: (id) => API.get(`/surgery/${id}`),
        create: (data) => API.post('/surgery', data),
        update: (id, data) => API.put(`/surgery/${id}`, data),
        delete: (id) => API.delete(`/surgery/${id}`)
    },

    // ============ PHARMACY ============
    pharmacy: {
        getMedicines: () => API.get('/pharmacy/medicines'),
        getMedicineById: (id) => API.get(`/pharmacy/medicines/${id}`),
        createMedicine: (data) => API.post('/pharmacy/medicines', data),
        updateMedicine: (id, data) => API.put(`/pharmacy/medicines/${id}`, data),
        deleteMedicine: (id) => API.delete(`/pharmacy/medicines/${id}`),
        dispense: (data) => API.post('/pharmacy/dispense', data),
        getDispenses: () => API.get('/pharmacy/dispenses'),
        getLowStock: () => API.get('/pharmacy/low-stock')
    },

    // ============ LABORATORY ============
    laboratory: {
        getTests: () => API.get('/laboratory/tests'),
        getOrders: () => API.get('/laboratory/orders'),
        getOrderById: (id) => API.get(`/laboratory/orders/${id}`),
        createOrder: (data) => API.post('/laboratory/orders', data),
        updateResult: (id, data) => API.put(`/laboratory/orders/${id}/result`, data),
        deleteOrder: (id) => API.delete(`/laboratory/orders/${id}`)
    },

    // ============ BILLING ============
    billing: {
        getInvoices: () => API.get('/billing/invoices'),
        getInvoiceById: (id) => API.get(`/billing/invoices/${id}`),
        createInvoice: (data) => API.post('/billing/invoices', data),
        addPayment: (id, data) => API.post(`/billing/invoices/${id}/payment`, data),
        deleteInvoice: (id) => API.delete(`/billing/invoices/${id}`)
    },

    // ============ REPORTS ============
    reports: {
        revenue: (params) => API.get(`/reports/revenue?${new URLSearchParams(params)}`),
        patients: (params) => API.get(`/reports/patients?${new URLSearchParams(params)}`),
        appointments: (params) => API.get(`/reports/appointments?${new URLSearchParams(params)}`)
    },

    // ============ USERS ============
    users: {
        getAll: () => API.get('/users'),
        getById: (id) => API.get(`/users/${id}`),
        create: (data) => API.post('/users', data),
        update: (id, data) => API.put(`/users/${id}`, data),
        delete: (id) => API.delete(`/users/${id}`)
    },

    // ============ SETTINGS ============
    settings: {
        get: () => API.get('/settings'),
        update: (data) => API.put('/settings', data),
        getDepartments: () => API.get('/settings/departments'),
        createDepartment: (data) => API.post('/settings/departments', data),
        deleteDepartment: (id) => API.delete(`/settings/departments/${id}`)
    }
};
