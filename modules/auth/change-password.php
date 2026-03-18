<?php
/**
 * Change Password Page
 */
requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        setFlashMessage('error', 'All fields are required');
    } elseif ($newPassword !== $confirmPassword) {
        setFlashMessage('error', 'New passwords do not match');
    } elseif (strlen($newPassword) < 6) {
        setFlashMessage('error', 'Password must be at least 6 characters');
    } else {
        $result = changePassword(getCurrentUserId(), $currentPassword, $newPassword);
        if ($result['success']) {
            setFlashMessage('success', $result['message']);
            redirect('index.php?page=profile');
        } else {
            setFlashMessage('error', $result['message']);
        }
    }
}
?>

<div class="page-header">
    <h1><i class="bi bi-key me-2"></i>Change Password</h1>
    <div>
        <a href="index.php?page=profile" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Profile
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="" class="needs-validation" novalidate>
                    <?= csrfField() ?>

                    <div class="mb-3">
                        <label class="form-label">Current Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="current_password" class="form-control" required>
                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="[name='current_password']">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="new_password" class="form-control" minlength="6" required>
                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="[name='new_password']">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <small class="text-muted">Minimum 6 characters</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="confirm_password" class="form-control" minlength="6" required>
                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="[name='confirm_password']">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i>Change Password
                        </button>
                        <a href="index.php?page=profile" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">Password Requirements</div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Minimum 6 characters</li>
                    <li>Mix of letters and numbers recommended</li>
                    <li>Avoid using common passwords</li>
                    <li>Do not share your password with others</li>
                </ul>
            </div>
        </div>
    </div>
</div>
