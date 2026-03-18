<?php
/**
 * User Profile Page
 */
requireAuth();

$storage = getStorage();
$user = $storage->getById('users', getCurrentUserId());

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');

    if (empty($name) || empty($email)) {
        setFlashMessage('error', 'Name and email are required');
    } else {
        $storage->update('users', $user['id'], [
            'name' => $name,
            'email' => $email,
            'phone' => $phone
        ]);

        // Update session
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['email'] = $email;

        logActivity('profile_update', 'Updated profile information');
        setFlashMessage('success', 'Profile updated successfully');
        redirect('index.php?page=profile');
    }
}
?>

<div class="page-header">
    <h1><i class="bi bi-person-circle me-2"></i>My Profile</h1>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 100px; height: 100px; font-size: 2.5rem;">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                <h4><?= htmlspecialchars($user['name']) ?></h4>
                <p class="text-muted"><?= getRoleDisplayName($user['role']) ?></p>
                <?= getStatusBadge($user['status']) ?>

                <hr>

                <div class="text-start">
                    <p class="mb-2">
                        <i class="bi bi-envelope me-2 text-muted"></i>
                        <?= htmlspecialchars($user['email']) ?>
                    </p>
                    <p class="mb-2">
                        <i class="bi bi-telephone me-2 text-muted"></i>
                        <?= htmlspecialchars($user['phone'] ?? 'Not provided') ?>
                    </p>
                    <p class="mb-2">
                        <i class="bi bi-calendar me-2 text-muted"></i>
                        Member since: <?= formatDate($user['created_at']) ?>
                    </p>
                    <?php if (!empty($user['last_login'])): ?>
                    <p class="mb-0">
                        <i class="bi bi-clock me-2 text-muted"></i>
                        Last login: <?= formatDateTime($user['last_login']) ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Quick Actions</div>
            <div class="card-body">
                <a href="index.php?page=change-password" class="btn btn-outline-primary w-100 mb-2">
                    <i class="bi bi-key me-2"></i>Change Password
                </a>
                <a href="index.php?action=logout" class="btn btn-outline-danger w-100">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Edit Profile</div>
            <div class="card-body">
                <form method="POST" action="">
                    <?= csrfField() ?>

                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                        <small class="text-muted">Username cannot be changed</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <input type="text" class="form-control" value="<?= getRoleDisplayName($user['role']) ?>" disabled>
                        <small class="text-muted">Contact administrator to change role</small>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>Update Profile
                    </button>
                </form>
            </div>
        </div>

        <!-- Activity Log -->
        <div class="card">
            <div class="card-header">Recent Activity</div>
            <div class="card-body">
                <?php
                $activities = array_filter($storage->getAll('activity_logs'), function($log) use ($user) {
                    return $log['user_id'] == $user['id'];
                });
                $activities = array_slice(array_reverse($activities), 0, 10);
                ?>

                <?php if (empty($activities)): ?>
                <p class="text-muted mb-0">No recent activity</p>
                <?php else: ?>
                <div class="timeline">
                    <?php foreach ($activities as $activity): ?>
                    <div class="timeline-item">
                        <div class="timeline-date"><?= formatDateTime($activity['created_at']) ?></div>
                        <div class="timeline-content">
                            <strong><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $activity['action']))) ?></strong>
                            <?php if (!empty($activity['details'])): ?>
                            <br><small class="text-muted"><?= htmlspecialchars($activity['details']) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
