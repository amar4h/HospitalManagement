<?php
/**
 * Login Page
 */

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        setFlashMessage('error', 'Please enter both username and password');
    } else {
        $result = attemptLogin($username, $password);
        if ($result['success']) {
            setFlashMessage('success', 'Welcome back, ' . $_SESSION['user']['name'] . '!');
            redirect('index.php?page=dashboard');
        } else {
            setFlashMessage('error', $result['message']);
        }
    }
}
?>

<div class="auth-card">
    <div class="auth-logo">
        <i class="bi bi-hospital"></i>
        <h2><?= HOSPITAL_NAME ?></h2>
        <p class="text-muted">Hospital Management System</p>
    </div>

    <?php displayFlashMessage(); ?>

    <form method="POST" action="" class="needs-validation" novalidate>
        <div class="mb-3">
            <label class="form-label">Username</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input type="text" name="username" class="form-control" placeholder="Enter username" required autofocus>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="[name='password']">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label" for="remember">Remember me</label>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2">
            <i class="bi bi-box-arrow-in-right me-2"></i>Login
        </button>
    </form>

    <div class="mt-4 text-center">
        <small class="text-muted">
            <strong>Demo Credentials:</strong><br>
            Admin: admin / admin123<br>
            Doctor: doctor / doctor123<br>
            Receptionist: receptionist / reception123
        </small>
    </div>
</div>

<style>
.auth-wrapper {
    background: linear-gradient(135deg, #1e3a5f 0%, #0d2137 100%);
}
</style>
