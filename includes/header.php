<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? APP_NAME ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php if (isLoggedIn()): ?>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php include INCLUDES_PATH . 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-4">
                <button class="btn btn-link text-dark" id="sidebarToggle">
                    <i class="bi bi-list fs-4"></i>
                </button>

                <div class="ms-auto d-flex align-items-center">
                    <!-- Notifications -->
                    <div class="dropdown me-3">
                        <button class="btn btn-link text-dark position-relative" data-bs-toggle="dropdown">
                            <i class="bi bi-bell fs-5"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationCount" style="display: none;">0</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" style="width: 300px;">
                            <li class="dropdown-header">Notifications</li>
                            <li><hr class="dropdown-divider"></li>
                            <li class="px-3 py-2 text-muted text-center" id="noNotifications">No new notifications</li>
                        </ul>
                    </div>

                    <!-- User Menu -->
                    <div class="dropdown">
                        <button class="btn btn-link text-dark d-flex align-items-center" data-bs-toggle="dropdown">
                            <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                <?= strtoupper(substr($_SESSION['user']['name'], 0, 1)) ?>
                            </div>
                            <span class="d-none d-md-inline"><?= htmlspecialchars($_SESSION['user']['name']) ?></span>
                            <i class="bi bi-chevron-down ms-1"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="px-3 py-2">
                                <strong><?= htmlspecialchars($_SESSION['user']['name']) ?></strong><br>
                                <small class="text-muted"><?= getRoleDisplayName($_SESSION['user']['role']) ?></small>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="index.php?page=profile"><i class="bi bi-person me-2"></i>My Profile</a></li>
                            <li><a class="dropdown-item" href="index.php?page=change-password"><i class="bi bi-key me-2"></i>Change Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="index.php?action=logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <div class="content-wrapper p-4">
                <?php displayFlashMessage(); ?>
    <?php else: ?>
    <div class="auth-wrapper">
    <?php endif; ?>
