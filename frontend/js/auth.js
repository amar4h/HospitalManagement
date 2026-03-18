/**
 * Authentication Module
 */
const Auth = {
    TOKEN_KEY: 'hms_token',
    USER_KEY: 'hms_user',

    /**
     * Check if user is authenticated
     */
    isAuthenticated() {
        return !!this.getToken() && !!this.getUser();
    },

    /**
     * Get stored token
     */
    getToken() {
        return localStorage.getItem(this.TOKEN_KEY);
    },

    /**
     * Get stored user
     */
    getUser() {
        const user = localStorage.getItem(this.USER_KEY);
        return user ? JSON.parse(user) : null;
    },

    /**
     * Store authentication data
     */
    setAuth(token, user) {
        localStorage.setItem(this.TOKEN_KEY, token);
        localStorage.setItem(this.USER_KEY, JSON.stringify(user));
    },

    /**
     * Clear authentication data
     */
    clearAuth() {
        localStorage.removeItem(this.TOKEN_KEY);
        localStorage.removeItem(this.USER_KEY);
    },

    /**
     * Login user
     */
    async login(username, password) {
        const response = await API.auth.login({ username, password });

        if (response.success) {
            this.setAuth(response.token, response.user);
            return { success: true };
        }

        return { success: false, message: response.message || 'Invalid credentials' };
    },

    /**
     * Logout user
     */
    logout() {
        this.clearAuth();
        App.showLogin();
    },

    /**
     * Get user role
     */
    getRole() {
        const user = this.getUser();
        return user ? user.role : null;
    },

    /**
     * Check if user has specific role
     */
    hasRole(roles) {
        const userRole = this.getRole();
        if (!userRole) return false;

        if (Array.isArray(roles)) {
            return roles.includes(userRole);
        }
        return userRole === roles;
    },

    /**
     * Check if user can access a route
     */
    canAccess(route) {
        const role = this.getRole();
        if (!role) return false;

        const menuItems = Config.MENU_ITEMS[role] || [];

        // Admin can access everything
        if (role === 'admin') return true;

        // Check if route is in user's menu
        return menuItems.some(item => item.route === route);
    }
};
