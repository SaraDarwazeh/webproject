<?php
$page_title = 'My List';
include '../app/includes/header.php';
include '../app/includes/navbar.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    echo '<main class="container py-5">
        <div class="page-empty-state mt-5">
            <i class="fas fa-lock text-primary"></i>
            <h3>Login Required</h3>
            <p>You need to be logged in to view your watchlist.</p>
            <a href="login.php" class="btn btn-primary mt-3"><i class="fas fa-sign-in-alt me-2"></i>Sign In</a>
        </div>
    </main>';
    include '../app/includes/footer.php';
    exit;
}

// Get user's watchlist
require_once '../app/controllers/list_controller.php';
$listController = new ListController();
$watchlist = $listController->getUserList($_SESSION['user_id']);
?>

<main class="mylist-page">
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-bookmark me-2"></i>My Watchlist</h1>
            <p class="text-muted"><?php echo count($watchlist); ?> item<?php echo count($watchlist) !== 1 ? 's' : ''; ?> saved</p>
        </div>

        <div id="mylist-container">
            <?php if (empty($watchlist)): ?>
            <div class="page-empty-state">
                <i class="fas fa-bookmark"></i>
                <h3>Your watchlist is empty</h3>
                <p>Start adding movies and series to keep track of what you want to watch</p>
                <a href="search.php" class="btn btn-primary mt-3"><i class="fas fa-search me-2"></i>Browse Content</a>
            </div>
            <?php else: ?>
            <div class="loading-skeleton"><div class="spinner-border text-primary"></div><p class="text-muted mt-2">Loading your movies...</p></div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php if (!empty($watchlist)): ?>
<script>
document.addEventListener('DOMContentLoaded', async function() {
    const listItems = <?php echo json_encode($watchlist); ?>;
    const container = document.getElementById('mylist-container');

    // Fetch details for each movie/show
    const promises = listItems.map(item => {
        const type = item.media_type === 'tv' ? 'tv' : 'movie';
        return fetchTMDB(type, { id: item.tmdb_id }).then(res => {
            if (!res.error) res.media_type_requested = type;
            return res;
        });
    });
    const movies = await Promise.all(promises);

    let html = '<div class="mylist-grid">';
    movies.forEach(movie => {
        if (movie.error || movie.status_code) return;
        const poster = movie.poster_path ? `https://image.tmdb.org/t/p/w300${movie.poster_path}` : '';
        const date = movie.release_date || movie.first_air_date || '';
        const year = date ? date.split('-')[0] : '';
        const rating = movie.vote_average ? movie.vote_average.toFixed(1) : 'N/A';
        const genres = movie.genres ? movie.genres.map(g => g.name).slice(0, 2).join(', ') : '';
        const mt = movie.media_type_requested;
        const typeUrl = mt === 'tv' ? '&type=tv' : '';
        const title = movie.title || movie.name || 'Unknown';

        html += `
            <div class="mylist-card" id="mylist-card-${movie.id}-${mt}">
                <a href="movie.php?id=${movie.id}${typeUrl}" class="mylist-poster" ${poster ? `style="background-image: url(${poster})"` : ''}>
                    ${!poster ? '<i class="fas fa-film"></i>' : ''}
                    <div class="mylist-poster-rating"><i class="fas fa-star"></i> ${rating}</div>
                </a>
                <div class="mylist-info">
                    <a href="movie.php?id=${movie.id}${typeUrl}" class="mylist-title">${title}</a>
                    <p class="mylist-meta">${year}${genres ? ' · ' + genres : ''}</p>
                    <p class="mylist-overview">${movie.overview ? movie.overview.substring(0, 100) + '...' : ''}</p>
                    <div class="mylist-actions">
                        <a href="movie.php?id=${movie.id}${typeUrl}" class="btn btn-outline-primary btn-sm"><i class="fas fa-info-circle me-1"></i>Details</a>
                        <button onclick="removeFromList(${movie.id}, '${mt}')" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash-alt me-1"></i>Remove</button>
                    </div>
                </div>
            </div>`;
    });
    html += '</div>';
    container.innerHTML = html;
});

async function removeFromList(tmdbId, mediaType = 'movie') {
    try {
        const response = await fetch('/streamhive/app/api/toggle_list.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tmdb_id: tmdbId, media_type: mediaType })
        });
        const data = await response.json();
        if (data.status === 'success' && !data.inList) {
            const card = document.getElementById('mylist-card-' + tmdbId + '-' + mediaType);
            if (card) {
                card.style.transition = 'all 0.3s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.9)';
                setTimeout(() => card.remove(), 300);
            }
            showToast('Removed from watchlist', 'success');
        }
    } catch(e) {
        showToast('Failed to remove', 'danger');
    }
}
</script>
<?php endif; ?>

<?php include '../app/includes/footer.php'; ?>
