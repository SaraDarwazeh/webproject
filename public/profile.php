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
require_once '../app/controllers/purchase_controller.php';

$listController = new ListController();
$purchaseCtrl = new PurchaseController();

$listCount = $listController->getListCount($_SESSION['user_id']);
$ratingStats = $listController->getRatingStats($_SESSION['user_id']);
$balance = $purchaseCtrl->getPointsBalance($_SESSION['user_id']);
$activeSub = $purchaseCtrl->getActiveSubscription($_SESSION['user_id']);
$purchases = $purchaseCtrl->getPurchaseHistory($_SESSION['user_id'], 10);
$transactions = $purchaseCtrl->getTransactionHistory($_SESSION['user_id'], 15);

// Get user details from DB
require_once '../app/db/db.php';
$db = Database::getInstance();
$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']], 'i');
$memberSince = $user ? date('F Y', strtotime($user['created_at'])) : 'Unknown';
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
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
            <?php if ($isAdmin): ?>
            <span class="badge badge-soft-warning mt-2"><i class="fas fa-shield-alt me-1"></i>Admin</span>
            <?php elseif ($activeSub): ?>
            <span class="badge badge-soft-success mt-2"><i class="fas fa-crown me-1"></i>Premium Subscriber</span>
            <?php endif; ?>
        </div>

        <!-- Stats -->
        <h2 class="section-title mt-5"><i class="fas fa-chart-bar me-2"></i>Your Activity</h2>
        <div class="stats-grid">
            <?php if (!$isAdmin): ?>
            <div class="data-stat-card stat-primary">
                <p class="stat-label">Points</p>
                <div class="stat-value"><?php echo $balance; ?></div>
                <p class="stat-description">Current balance</p>
            </div>
            <?php endif; ?>
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

        <?php if (!$isAdmin): ?>
        <!-- Subscription Status -->
        <h2 class="section-title mt-5"><i class="fas fa-crown me-2"></i>Subscription</h2>
        <div class="card mb-4">
            <div class="card-body">
                <?php if ($activeSub): ?>
                <div class="d-flex align-items-center gap-3">
                    <div class="sub-status-icon active"><i class="fas fa-check-circle"></i></div>
                    <div>
                        <h5 class="mb-1">Active — <?php echo ucfirst($activeSub['plan_type']); ?> Plan</h5>
                        <p class="text-muted mb-0">Expires: <?php echo date('M j, Y g:ia', strtotime($activeSub['expires_at'])); ?>
                        <?php if ($activeSub['auto_renew']): ?> · <span class="text-success"><i class="fas fa-sync-alt me-1"></i>Auto-renew ON</span><?php endif; ?>
                        </p>
                    </div>
                </div>
                <?php else: ?>
                <div class="d-flex align-items-center gap-3">
                    <div class="sub-status-icon inactive"><i class="fas fa-times-circle"></i></div>
                    <div>
                        <h5 class="mb-1">No Active Subscription</h5>
                        <p class="text-muted mb-0">Subscribe for unlimited access to all content</p>
                    </div>
                    <a href="subscribe.php" class="btn btn-primary ms-auto"><i class="fas fa-crown me-2"></i>Subscribe</a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Transaction History -->
        <h2 class="section-title mt-5"><i class="fas fa-receipt me-2"></i>Transaction History</h2>
        <?php if (!empty($transactions)): ?>
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-darker">
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Type</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $tx): ?>
                        <tr>
                            <td><small class="text-muted"><?php echo date('M j, Y g:ia', strtotime($tx['created_at'])); ?></small></td>
                            <td><?php echo htmlspecialchars($tx['description']); ?></td>
                            <td>
                                <span class="badge badge-soft-<?php echo $tx['type'] === 'credit' ? 'success' : 'danger'; ?>">
                                    <?php echo ucfirst($tx['type']); ?>
                                </span>
                            </td>
                            <td class="<?php echo $tx['amount'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <strong><?php echo $tx['amount'] >= 0 ? '+' : ''; ?><?php echo $tx['amount']; ?> pts</strong>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
        <div class="card"><div class="card-body text-center text-muted py-4"><i class="fas fa-receipt me-2"></i>No transactions yet</div></div>
        <?php endif; ?>

        <!-- Recent Purchases -->
        <?php if (!empty($purchases)): ?>
        <h2 class="section-title mt-5"><i class="fas fa-shopping-bag me-2"></i>Recent Purchases</h2>
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-darker">
                        <tr>
                            <th>Date</th>
                            <th>TMDB ID</th>
                            <th>Type</th>
                            <th>Details</th>
                            <th>Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($purchases as $p): ?>
                        <tr>
                            <td><small class="text-muted"><?php echo date('M j, Y', strtotime($p['purchased_at'])); ?></small></td>
                            <td>#<?php echo $p['tmdb_id']; ?></td>
                            <td><span class="badge badge-soft-<?php echo $p['media_type'] === 'movie' ? 'primary' : 'info'; ?>"><?php echo ucfirst($p['media_type']); ?></span></td>
                            <td>
                                <?php if ($p['season_number']): ?>
                                S<?php echo $p['season_number']; ?>E<?php echo $p['episode_number']; ?>
                                <?php else: ?>
                                Full <?php echo $p['media_type']; ?>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo $p['points_spent']; ?> pts</strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="profile-actions mt-4">
            <a href="mylist.php" class="btn btn-outline-primary btn-lg"><i class="fas fa-bookmark me-2"></i>View Watchlist</a>
            <a href="search.php" class="btn btn-outline-primary btn-lg"><i class="fas fa-search me-2"></i>Discover Movies</a>
            <?php if (!$isAdmin): ?>
            <a href="buy_points.php" class="btn btn-outline-primary btn-lg"><i class="fas fa-gem me-2"></i>Buy Points</a>
            <a href="subscribe.php" class="btn btn-outline-warning btn-lg"><i class="fas fa-crown me-2"></i>Subscribe</a>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-outline-danger btn-lg"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
        </div>
    </div>
</main>

<?php include '../app/includes/footer.php'; ?>
