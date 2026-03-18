<?php
$menuItems = getMenuItems();
$currentPage = $_GET['page'] ?? 'dashboard';
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="index.php" class="sidebar-brand">
            <i class="bi bi-hospital text-primary me-2"></i>
            <span><?= APP_NAME ?></span>
        </a>
    </div>

    <div class="sidebar-body">
        <ul class="sidebar-nav">
            <?php foreach ($menuItems as $key => $menu): ?>
            <li class="nav-item">
                <a href="index.php?page=<?= $key ?>" class="nav-link <?= $currentPage === $key ? 'active' : '' ?>">
                    <i class="bi <?= $menu['icon'] ?>"></i>
                    <span><?= $menu['label'] ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="sidebar-footer">
        <div class="small text-muted text-center py-2">
            <?= APP_NAME ?><br>
            Version <?= APP_VERSION ?>
        </div>
    </div>
</aside>
