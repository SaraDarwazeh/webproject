<?php
$page_title = 'Manage Movies';
include '../../app/includes/header.php';
include '../../app/includes/navbar.php';

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: /streamhive/public/index.php');
    exit;
}

require_once '../../app/db/db.php';
$db = Database::getInstance();

$movies = $db->fetchAll("SELECT * FROM movies ORDER BY created_at DESC");
?>

<main class="container-fluid py-5">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1><i class="fas fa-film me-2"></i>Movie Management</h1>
                <p class="text-muted"><?php echo count($movies); ?> movies in local database</p>
            </div>
            <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-2"></i>Dashboard</a>
            </div>
        </div>

        <div class="alert alert-soft-primary mb-4">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Note:</strong> Movies are primarily served from TMDB API. This table shows locally cached movies used for watchlists and ratings.
        </div>

        <?php if (empty($movies)): ?>
        <div class="page-empty-state">
            <i class="fas fa-film"></i>
            <h3>No local movies yet</h3>
            <p>Movies will appear here as users interact with them via watchlists and ratings.</p>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-darker">
                        <tr>
                            <th>ID</th>
                            <th>TMDB ID</th>
                            <th>Title</th>
                            <th>Genre</th>
                            <th>Year</th>
                            <th>Rating</th>
                            <th>Added</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movies as $m): ?>
                        <tr>
                            <td><?php echo $m['id']; ?></td>
                            <td><a href="/streamhive/public/movie.php?id=<?php echo $m['tmdb_id']; ?>" class="text-primary">#<?php echo $m['tmdb_id']; ?></a></td>
                            <td><strong><?php echo htmlspecialchars($m['title']); ?></strong></td>
                            <td><span class="badge badge-soft-secondary"><?php echo htmlspecialchars($m['genre'] ?? 'N/A'); ?></span></td>
                            <td><?php echo $m['year'] ?? 'N/A'; ?></td>
                            <td><span class="text-primary"><i class="fas fa-star"></i> <?php echo $m['rating'] ?? 'N/A'; ?></span></td>
                            <td><small class="text-muted"><?php echo date('Y-m-d', strtotime($m['created_at'])); ?></small></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php include '../../app/includes/footer.php'; ?>
