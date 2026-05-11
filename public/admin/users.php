<?php
$page_title = 'Manage Users';
include '../../app/includes/header.php';
include '../../app/includes/navbar.php';

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: /streamhive/public/index.php');
    exit;
}

require_once '../../app/db/db.php';
$db = Database::getInstance();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $userId = intval($_POST['user_id']);
    switch ($_POST['action']) {
        case 'suspend':
            $db->execute("UPDATE users SET status = 'suspended' WHERE id = ?", [$userId], 'i');
            break;
        case 'activate':
            $db->execute("UPDATE users SET status = 'active' WHERE id = ?", [$userId], 'i');
            break;
        case 'delete':
            $db->execute("DELETE FROM users WHERE id = ? AND is_admin = 0", [$userId], 'i');
            break;
    }
    header('Location: users.php');
    exit;
}

$users = $db->fetchAll("SELECT u.*, 
    (SELECT COUNT(*) FROM my_list WHERE user_id = u.id) as list_count,
    (SELECT COUNT(*) FROM ratings WHERE user_id = u.id) as rating_count
    FROM users u ORDER BY u.created_at DESC");
?>

<main class="container-fluid py-5">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1><i class="fas fa-users me-2"></i>User Management</h1>
                <p class="text-muted"><?php echo count($users); ?> registered users</p>
            </div>
            <a href="index.php" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-2"></i>Dashboard</a>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-darker">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Joined</th>
                            <th>Status</th>
                            <th>Watchlist</th>
                            <th>Ratings</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td>#<?php echo $u['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($u['username']); ?></strong>
                                <?php if ($u['is_admin']): ?><span class="badge badge-soft-warning ms-1">Admin</span><?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><small class="text-muted"><?php echo date('Y-m-d', strtotime($u['created_at'])); ?></small></td>
                            <td>
                                <?php
                                $statusClass = $u['status'] === 'active' ? 'success' : ($u['status'] === 'suspended' ? 'danger' : 'secondary');
                                ?>
                                <span class="badge badge-soft-<?php echo $statusClass; ?>"><?php echo ucfirst($u['status']); ?></span>
                            </td>
                            <td><?php echo $u['list_count']; ?></td>
                            <td><?php echo $u['rating_count']; ?></td>
                            <td>
                                <?php if (!$u['is_admin']): ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <?php if ($u['status'] === 'active'): ?>
                                    <button name="action" value="suspend" class="btn btn-sm btn-outline-secondary">Suspend</button>
                                    <?php else: ?>
                                    <button name="action" value="activate" class="btn btn-sm btn-outline-primary">Activate</button>
                                    <?php endif; ?>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include '../../app/includes/footer.php'; ?>
