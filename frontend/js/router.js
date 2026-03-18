/**
 * Client-side Router
 */
const Router = {
    routes: {},
    currentRoute: null,

    /**
     * Register a route
     */
    register(path, handler) {
        this.routes[path] = handler;
    },

    /**
     * Navigate to a route
     */
    navigate(path) {
        window.location.hash = path;
    },

    /**
     * Get current route parameters
     */
    getParams() {
        const hash = window.location.hash.slice(1) || '/';
        const parts = hash.split('/').filter(p => p);
        const params = {};

        // Extract ID from routes like /patients/123 or /patients/123/edit
        if (parts.length >= 2 && !isNaN(parts[1])) {
            params.id = parseInt(parts[1]);
        }

        // Extract action from routes like /patients/add or /patients/123/edit
        if (parts.length === 2 && isNaN(parts[1])) {
            params.action = parts[1];
        } else if (parts.length === 3) {
            params.action = parts[2];
        }

        // Extract query parameters
        const queryString = window.location.hash.split('?')[1];
        if (queryString) {
            const urlParams = new URLSearchParams(queryString);
            urlParams.forEach((value, key) => {
                params[key] = value;
            });
        }

        return params;
    },

    /**
     * Get base route (without params)
     */
    getBaseRoute(hash) {
        const path = hash.slice(1) || '/';
        const parts = path.split('?')[0].split('/').filter(p => p);

        if (parts.length === 0) return '/';
        if (parts.length === 1) return `/${parts[0]}`;

        // Handle routes like /patients/add vs /patients/123
        if (parts.length === 2) {
            if (isNaN(parts[1])) {
                return `/${parts[0]}/${parts[1]}`;
            }
            return `/${parts[0]}/:id`;
        }

        // Handle routes like /patients/123/edit
        if (parts.length === 3) {
            return `/${parts[0]}/:id/${parts[2]}`;
        }

        return `/${parts[0]}`;
    },

    /**
     * Handle route change
     */
    async handleRoute() {
        const hash = window.location.hash || '#/';
        const baseRoute = this.getBaseRoute(hash);
        const params = this.getParams();

        // Check authentication
        if (!Auth.isAuthenticated()) {
            App.showLogin();
            return;
        }

        // Check access permission
        const mainRoute = '/' + (baseRoute.split('/')[1] || '');
        if (!Auth.canAccess(mainRoute) && mainRoute !== '/profile') {
            App.showToast('Access Denied', 'You do not have permission to access this page.', 'danger');
            Router.navigate('/');
            return;
        }

        // Find and execute handler
        const handler = this.routes[baseRoute];
        if (handler) {
            this.currentRoute = baseRoute;
            this.updateActiveMenu(mainRoute);
            await handler(params);
        } else {
            // Show 404
            document.getElementById('page-content').innerHTML = `
                <div class="empty-state">
                    <i class="bi bi-exclamation-triangle"></i>
                    <h5>Page Not Found</h5>
                    <p>The page you're looking for doesn't exist.</p>
                    <a href="#/" class="btn btn-primary">Go to Dashboard</a>
                </div>
            `;
        }
    },

    /**
     * Update active menu item
     */
    updateActiveMenu(route) {
        document.querySelectorAll('.sidebar-nav .nav-link').forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${route}`) {
                link.classList.add('active');
            }
        });
    },

    /**
     * Initialize router
     */
    init() {
        window.addEventListener('hashchange', () => this.handleRoute());
        this.handleRoute();
    }
};
