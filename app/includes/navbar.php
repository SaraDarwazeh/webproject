<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$pointsBalance = isset($_SESSION['points_balance']) ? $_SESSION['points_balance'] : 0;

// Check subscription status (lightweight query, only if logged in)
$isSubscribed = false;
if ($isLoggedIn && !$isAdmin) {
    require_once __DIR__ . '/../controllers/purchase_controller.php';
    $navPurchaseCtrl = new PurchaseController();
    $isSubscribed = $navPurchaseCtrl->isSubscribed($_SESSION['user_id']);
    // Refresh balance from DB
    $pointsBalance = $navPurchaseCtrl->getPointsBalance($_SESSION['user_id']);
    $_SESSION['points_balance'] = $pointsBalance;
}
?>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top" id="main-navbar">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="/streamhive/public/index.php">
            <i class="fas fa-play-circle" style="color: var(--primary); font-size: 1.6rem;"></i>
            <span style="font-size: 1.3rem; letter-spacing: 0.5px;">Stream<span style="color: var(--primary);">Hive</span></span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto ms-4">
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>" href="/streamhive/public/index.php">
                        <i class="fas fa-home me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'search.php' ? 'active' : ''; ?>" href="/streamhive/public/search.php">
                        <i class="fas fa-search me-1"></i> Search
                    </a>
                </li>
                <?php if ($isLoggedIn): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage === 'mylist.php' ? 'active' : ''; ?>" href="/streamhive/public/mylist.php">
                        <i class="fas fa-bookmark me-1"></i> My List
                    </a>
                </li>
                <?php endif; ?>
                <?php if ($isAdmin): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'admin') !== false ? 'active' : ''; ?>" href="/streamhive/public/admin/index.php">
                        <i class="fas fa-shield-alt me-1"></i> Admin
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if ($isLoggedIn): ?>
                <?php if (!$isAdmin): ?>
                <li class="nav-item d-flex align-items-center me-3">
                    <a href="/streamhive/public/buy_points.php" class="nav-points-badge" title="Your Points Balance">
                        <i class="fas fa-gem"></i>
                        <span id="nav-points-balance"><?php echo $pointsBalance; ?></span> pts
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                        <div class="user-avatar-sm"><?php echo strtoupper(substr($username, 0, 1)); ?></div>
                        <span><?php echo htmlspecialchars($username); ?></span>
                        <?php if ($isSubscribed): ?>
                        <span class="nav-sub-badge" title="Premium Subscriber"><i class="fas fa-crown"></i> PRO</span>
                        <?php endif; ?>
                        <?php if ($isAdmin): ?>
                        <span class="nav-admin-badge"><i class="fas fa-shield-alt"></i></span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                        <li><a class="dropdown-item" href="/streamhive/public/profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="/streamhive/public/mylist.php"><i class="fas fa-bookmark me-2"></i>My List</a></li>
                        <?php if (!$isAdmin): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/streamhive/public/buy_points.php"><i class="fas fa-gem me-2"></i>Buy Points <span class="text-muted small ms-1">(<?php echo $pointsBalance; ?> pts)</span></a></li>
                        <li><a class="dropdown-item" href="/streamhive/public/subscribe.php"><i class="fas fa-crown me-2"></i>Subscribe <?php if ($isSubscribed): ?><span class="badge bg-success ms-1">Active</span><?php endif; ?></a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/streamhive/public/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="/streamhive/public/login.php">
                        <i class="fas fa-sign-in-alt me-1"></i> Login
                    </a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-primary btn-sm ms-2 px-3" href="/streamhive/public/register.php" style="margin-top: 5px;">
                        Sign Up
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

