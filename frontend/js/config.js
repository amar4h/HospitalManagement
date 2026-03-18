/**
 * Application Configuration
 */
const Config = {
    // API Base URL - Relative path for same-domain deployment on Hostinger
    API_URL: '/api',

    // Application Settings
    APP_NAME: 'Hospital Management System',
    APP_VERSION: '1.0.0',

    // Pagination
    ITEMS_PER_PAGE: 10,

    // Date Format
    DATE_FORMAT: 'en-US',

    // Session timeout in milliseconds (30 minutes)
    SESSION_TIMEOUT: 30 * 60 * 1000,

    // Menu items by role
    MENU_ITEMS: {
        admin: [
            { icon: 'bi-speedometer2', label: 'Dashboard', route: '/' },
            { divider: true },
            { header: 'Patient Care' },
            { icon: 'bi-people', label: 'Patients', route: '/patients' },
            { icon: 'bi-person-badge', label: 'Doctors', route: '/doctors' },
            { icon: 'bi-calendar-check', label: 'Appointments', route: '/appointments' },
            { divider: true },
            { header: 'Clinical' },
            { icon: 'bi-clipboard2-pulse', label: 'OPD', route: '/opd' },
            { icon: 'bi-hospital', label: 'IPD', route: '/ipd' },
            { icon: 'bi-heart-pulse', label: 'Surgery', route: '/surgery' },
            { divider: true },
            { header: 'Services' },
            { icon: 'bi-capsule', label: 'Pharmacy', route: '/pharmacy' },
            { icon: 'bi-droplet', label: 'Laboratory', route: '/laboratory' },
            { icon: 'bi-receipt', label: 'Billing', route: '/billing' },
            { divider: true },
            { header: 'Administration' },
            { icon: 'bi-graph-up', label: 'Reports', route: '/reports' },
            { icon: 'bi-people-fill', label: 'Users', route: '/users' },
            { icon: 'bi-gear', label: 'Settings', route: '/settings' }
        ],
        doctor: [
            { icon: 'bi-speedometer2', label: 'Dashboard', route: '/' },
            { divider: true },
            { header: 'Patient Care' },
            { icon: 'bi-people', label: 'Patients', route: '/patients' },
            { icon: 'bi-calendar-check', label: 'Appointments', route: '/appointments' },
            { divider: true },
            { header: 'Clinical' },
            { icon: 'bi-clipboard2-pulse', label: 'OPD', route: '/opd' },
            { icon: 'bi-hospital', label: 'IPD', route: '/ipd' },
            { icon: 'bi-heart-pulse', label: 'Surgery', route: '/surgery' },
            { divider: true },
            { icon: 'bi-droplet', label: 'Laboratory', route: '/laboratory' }
        ],
        nurse: [
            { icon: 'bi-speedometer2', label: 'Dashboard', route: '/' },
            { divider: true },
            { icon: 'bi-people', label: 'Patients', route: '/patients' },
            { icon: 'bi-calendar-check', label: 'Appointments', route: '/appointments' },
            { icon: 'bi-hospital', label: 'IPD', route: '/ipd' }
        ],
        receptionist: [
            { icon: 'bi-speedometer2', label: 'Dashboard', route: '/' },
            { divider: true },
            { icon: 'bi-people', label: 'Patients', route: '/patients' },
            { icon: 'bi-calendar-check', label: 'Appointments', route: '/appointments' },
            { icon: 'bi-receipt', label: 'Billing', route: '/billing' }
        ],
        pharmacist: [
            { icon: 'bi-speedometer2', label: 'Dashboard', route: '/' },
            { divider: true },
            { icon: 'bi-capsule', label: 'Pharmacy', route: '/pharmacy' }
        ],
        lab_technician: [
            { icon: 'bi-speedometer2', label: 'Dashboard', route: '/' },
            { divider: true },
            { icon: 'bi-droplet', label: 'Laboratory', route: '/laboratory' }
        ],
        accountant: [
            { icon: 'bi-speedometer2', label: 'Dashboard', route: '/' },
            { divider: true },
            { icon: 'bi-receipt', label: 'Billing', route: '/billing' },
            { icon: 'bi-graph-up', label: 'Reports', route: '/reports' }
        ]
    }
};
