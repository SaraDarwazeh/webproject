<?php
$page_title = 'Admin Dashboard';
include '../../app/includes/header.php';
include '../../app/includes/navbar.php';

// Admin check
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    echo '<main class="container py-5">
        <div class="page-empty-state mt-5">
            <i class="fas fa-shield-alt text-danger"></i>
            <h3>Access Denied</h3>
            <p>Admin privileges required.</p>
            <a href="/streamhive/public/index.php" class="btn btn-primary mt-3">Back to Home</a>
        </div>
    </main>';
    include '../../app/includes/footer.php';
    exit;
}

// Get real stats
require_once '../../app/db/db.php';
$db = Database::getInstance();
$movieCount = $db->fetchOne("SELECT COUNT(*) as c FROM movies")['c'] ?? 0;
$userCount = $db->fetchOne("SELECT COUNT(*) as c FROM users")['c'] ?? 0;
$ratingCount = $db->fetchOne("SELECT COUNT(*) as c FROM ratings")['c'] ?? 0;
$listCount = $db->fetchOne("SELECT COUNT(*) as c FROM my_list")['c'] ?? 0;
?>

<main class="container-fluid py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1><i class="fas fa-chart-bar me-2"></i>Admin Dashboard</h1>
                <p class="text-muted">System overview and statistics</p>
            </div>
            <a href="/streamhive/public/index.php" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-2"></i>Main Site</a>
        </div>

        <div class="row mb-5">
            <div class="col-md-3 mb-3">
                <div class="data-stat-card stat-primary">
                    <p class="stat-label">Total Movies</p>
                    <div class="stat-value"><?php echo $movieCount; ?></div>
                    <p class="stat-description">In database</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="data-stat-card stat-purple">
                    <p class="stat-label">Total Users</p>
                    <div class="stat-value"><?php echo $userCount; ?></div>
                    <p class="stat-description">Registered</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="data-stat-card stat-blue">
                    <p class="stat-label">Total Ratings</p>
                    <div class="stat-value"><?php echo $ratingCount; ?></div>
                    <p class="stat-description">Submitted</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="data-stat-card stat-success">
                    <p class="stat-label">Watchlist Items</p>
                    <div class="stat-value text-success"><?php echo $listCount; ?></div>
                    <p class="stat-description">Total saves</p>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5><i class="fas fa-film me-2 text-primary"></i>Movie Management</h5>
                        <p class="text-muted small">Manage the movie catalog</p>
                        <a href="movies.php" class="btn btn-primary"><i class="fas fa-arrow-right me-2"></i>Manage Movies</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5><i class="fas fa-users me-2 text-primary"></i>User Management</h5>
                        <p class="text-muted small">Manage user accounts</p>
                        <a href="users.php" class="btn btn-primary"><i class="fas fa-arrow-right me-2"></i>Manage Users</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../../app/includes/footer.php'; ?>
