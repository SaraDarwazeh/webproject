<?php
$page_title = 'My Profile';
include '../app/includes/header.php';
include '../app/includes/navbar.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    echo '<main class="container py-5">
        <div class="page-empty-state mt-5">
            <i class="fas fa-lock text-primary"></i>
            <h3>Login Required</h3>
            <p>You need to be logged in to view your profile.</p>
            <a href="login.php" class="btn btn-primary mt-3"><i class="fas fa-sign-in-alt me-2"></i>Sign In</a>
        </div>
    </main>';
    include '../app/includes/footer.php';
    exit;
}

require_once '../app/controllers/list_controller.php';
$listController = new ListController();
$listCount = $listController->getListCount($_SESSION['user_id']);
$ratingStats = $listController->getRatingStats($_SESSION['user_id']);

// Get user details from DB
require_once '../app/db/db.php';
$db = Database::getInstance();
$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']], 'i');
$memberSince = $user ? date('F Y', strtotime($user['created_at'])) : 'Unknown';
?>

<main class="profile-page">
    <div class="container">
        <!-- Profile Card -->
        <div class="profile-card">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
            </div>
            <h2 class="profile-name"><?php echo htmlspecialchars($_SESSION['username']); ?></h2>
            <p class="profile-email"><?php echo htmlspecialchars($_SESSION['email']); ?></p>
            <p class="profile-member-since"><i class="fas fa-calendar me-1"></i>Member since <?php echo $memberSince; ?></p>
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
            <span class="badge badge-soft-warning mt-2"><i class="fas fa-shield-alt me-1"></i>Admin</span>
            <?php endif; ?>
        </div>

        <!-- Stats -->
        <h2 class="section-title mt-5"><i class="fas fa-chart-bar me-2"></i>Your Activity</h2>
        <div class="stats-grid">
            <div class="data-stat-card stat-success">
                <p class="stat-label">Watchlist</p>
                <div class="stat-value"><?php echo $listCount; ?></div>
                <p class="stat-description">Movies saved</p>
            </div>
            <div class="data-stat-card stat-blue">
                <p class="stat-label">Rated</p>
                <div class="stat-value"><?php echo $ratingStats['count']; ?></div>
                <p class="stat-description">Movies rated</p>
            </div>
            <div class="data-stat-card stat-purple">
                <p class="stat-label">Avg Rating</p>
                <div class="stat-value"><?php echo $ratingStats['count'] > 0 ? number_format($ratingStats['avg_rating'], 1) : '—'; ?></div>
                <p class="stat-description">Out of 5 stars</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="profile-actions mt-4">
            <a href="mylist.php" class="btn btn-outline-primary btn-lg"><i class="fas fa-bookmark me-2"></i>View Watchlist</a>
            <a href="search.php" class="btn btn-outline-primary btn-lg"><i class="fas fa-search me-2"></i>Discover Movies</a>
            <a href="logout.php" class="btn btn-outline-danger btn-lg"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
        </div>
    </div>
</main>

<?php include '../app/includes/footer.php'; ?>
