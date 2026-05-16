<?php
$page_title = 'Details';
include '../app/includes/header.php';
include '../app/includes/navbar.php';

$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
$currentUserId = $isLoggedIn ? $_SESSION['user_id'] : 0;
?>

<main id="movie-content">
    <div class="loading-page">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="text-muted mt-3">Loading details...</p>
    </div>
</main>

<script>
// Pass PHP session info to JS
const IS_LOGGED_IN = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
const IS_ADMIN = <?php echo $isAdmin ? 'true' : 'false'; ?>;
const CURRENT_USER_ID = <?php echo $currentUserId; ?>;
const CURRENT_USERNAME = '<?php echo $isLoggedIn ? htmlspecialchars($_SESSION['username'], ENT_QUOTES) : ''; ?>';

document.addEventListener('DOMContentLoaded', async function() {
    const params = new URLSearchParams(window.location.search);
    const itemId = params.get('id');
    const mediaType = params.get('type') || 'movie';

    if (!itemId) {
        document.getElementById('movie-content').innerHTML = errorPage('No ID provided');
        return;
    }

    try {
        const action = mediaType === 'tv' ? 'tv' : 'movie';
        const item = await fetchTMDB(action, { id: itemId });
        if (item.error || item.status_code === 34) {
            document.getElementById('movie-content').innerHTML = errorPage('Not found');
            return;
        }

        await renderDetails(item, mediaType);

        // Check Jellyfin availability for this title (non-blocking)
        checkJellyfinAvailability(item.id, mediaType);
    } catch(e) {
        document.getElementById('movie-content').innerHTML = errorPage('Failed to load details');
    }
});

function errorPage(msg) {
    return `
        <div class="container py-5 text-center">
            <div style="background: var(--card-bg); padding: 60px; border-radius: 16px; border: 1px solid var(--border-color); max-width: 500px; margin: 60px auto;">
                <i class="fas fa-film" style="font-size: 3rem; color: var(--primary); opacity: 0.5; margin-bottom: 20px;"></i>
                <h3 style="color: #e74c3c;">${msg}</h3>
                <p class="text-muted mt-3">The content you are looking for could not be found.</p>
                <a href="index.php" class="btn btn-primary mt-3"><i class="fas fa-home me-2"></i>Back to Home</a>
            </div>
        </div>`;
}

async function renderDetails(item, mediaType) {
    const isTV = mediaType === 'tv';
    const title = isTV ? (item.name || 'Unknown') : (item.title || 'Unknown');
    const posterUrl = item.poster_path ? `https://image.tmdb.org/t/p/w500${item.poster_path}` : '/streamhive/public/assets/img/posters/placeholder.jpg';
    const backdropUrl = item.backdrop_path ? `https://image.tmdb.org/t/p/original${item.backdrop_path}` : '';
    const date = isTV ? item.first_air_date : item.release_date;
    const year = date ? date.split('-')[0] : 'N/A';
    const genres = item.genres ? item.genres.map(g => `<span class="genre-tag">${g.name}</span>`).join('') : '';
    const mediaTypeLabel = isTV ? 'tv' : 'movie';

    // Runtime / Season info
    let durationInfo = '';
    if (isTV) {
        const seasons = item.number_of_seasons || 0;
        const episodes = item.number_of_episodes || 0;
        durationInfo = `${seasons} Season${seasons !== 1 ? 's' : ''} · ${episodes} Episode${episodes !== 1 ? 's' : ''}`;
    } else {
        durationInfo = item.runtime ? `${Math.floor(item.runtime/60)}h ${item.runtime%60}m` : 'N/A';
    }

    // Status badge for TV
    const statusBadge = isTV && item.status ? `<span class="meta-badge">${item.status}</span>` : '';

    // Get trailer
    let trailerKey = '';
    if (item.videos && item.videos.results) {
        const trailer = item.videos.results.find(v => v.type === 'Trailer' && v.site === 'YouTube');
        if (trailer) trailerKey = trailer.key;
    }

    // Get cast (top 8)
    let castHTML = '';
    if (item.credits && item.credits.cast) {
        const topCast = item.credits.cast.slice(0, 8);
        castHTML = topCast.map(actor => {
            const actorImg = actor.profile_path
                ? `https://image.tmdb.org/t/p/w185${actor.profile_path}`
                : '';
            return `
                <div class="cast-card">
                    <div class="cast-photo" ${actorImg ? `style="background-image: url(${actorImg})"` : ''}>
                        ${!actorImg ? '<i class="fas fa-user"></i>' : ''}
                    </div>
                    <p class="cast-name">${actor.name}</p>
                    <p class="cast-character">${actor.character}</p>
                </div>`;
        }).join('');
    }

    // Check access (purchase/subscription status)
    let accessData = { logged_in: false, has_access: false, is_subscribed: false, is_purchased: false, is_admin: false, balance: 0 };
    if (IS_LOGGED_IN) {
        try {
            const accessResp = await fetch(`/streamhive/app/api/purchase.php?action=check_access&tmdb_id=${item.id}&media_type=${mediaTypeLabel}`);
            accessData = await accessResp.json();
        } catch(e) {}
    }

    const hasAccess = accessData.has_access || IS_ADMIN;

    // Get user rating (only if has access)
    let userRating = 0;
    if (hasAccess) {
        try {
            const ratingData = await fetch('/streamhive/app/api/search.php?action=get_rating&tmdb_id=' + item.id);
            const rd = await ratingData.json();
            if (rd.logged_in) userRating = rd.rating;
        } catch(e) {}
    }

    // Check watchlist
    let inList = false;
    try {
        const listData = await fetch('/streamhive/app/api/search.php?action=check_list');
        const ld = await listData.json();
        if (ld.logged_in) inList = ld.list.some(el => el.tmdb_id == item.id && el.media_type === mediaTypeLabel);
    } catch(e) {}

    // Similar items
    let similarHTML = '';
    if (item.similar && item.similar.results && item.similar.results.length > 0) {
        const similar = item.similar.results.slice(0, 6);
        similarHTML = similar.map(s => {
            const sPoster = s.poster_path ? `https://image.tmdb.org/t/p/w300${s.poster_path}` : '';
            const sTitle = isTV ? (s.name || 'Unknown') : (s.title || 'Unknown');
            const sDate = isTV ? s.first_air_date : s.release_date;
            const sYear = sDate ? sDate.split('-')[0] : '';
            const sLink = isTV ? `movie.php?id=${s.id}&type=tv` : `movie.php?id=${s.id}`;
            return `
                <div class="movie-card-sm">
                    <a href="${sLink}">
                        <div class="movie-poster-sm" ${sPoster ? `style="background-image: url(${sPoster})"` : ''}>
                            ${!sPoster ? '<i class="fas fa-film"></i>' : ''}
                        </div>
                    </a>
                    <p class="movie-card-title">${sTitle}</p>
                    <p class="movie-card-meta">${sYear} · <i class="fas fa-star text-warning"></i> ${s.vote_average?.toFixed(1)}</p>
                </div>`;
        }).join('');
    }

    // Build the purchase/access banner for the movie
    let purchaseBannerHTML = '';
    if (IS_LOGGED_IN && !hasAccess) {
        purchaseBannerHTML = `
        <div class="purchase-banner">
            <div class="purchase-banner-content">
                <i class="fas fa-lock"></i>
                <div>
                    <strong>Purchase to unlock ratings & comments</strong>
                    <p class="mb-0 small text-muted">This ${isTV ? 'show' : 'movie'} costs <strong>${isTV ? '5 pts/episode' : '20 points'}</strong> · Your balance: <strong>${accessData.balance} pts</strong></p>
                </div>
            </div>
            <div class="purchase-banner-actions">
                ${!isTV ? `<button class="btn btn-primary" onclick="purchaseContent(${item.id}, '${mediaTypeLabel}')"><i class="fas fa-shopping-cart me-2"></i>Purchase (20 pts)</button>` : ''}
                <a href="/streamhive/public/subscribe.php" class="btn btn-outline-warning"><i class="fas fa-crown me-2"></i>Subscribe</a>
                <a href="/streamhive/public/buy_points.php" class="btn btn-outline-primary btn-sm"><i class="fas fa-gem me-1"></i>Buy Points</a>
            </div>
        </div>`;
    }

    // Rating section — show interactive stars only if has access
    let ratingHTML = '';
    if (hasAccess) {
        ratingHTML = `
        <div class="user-rating-section">
            <h4><i class="fas fa-star me-2"></i>Rate This ${isTV ? 'Show' : 'Movie'}</h4>
            <div class="star-rating" id="star-rating">
                ${[1,2,3,4,5].map(i => `
                    <span class="star-input ${i <= userRating ? 'active' : ''}" onclick="rateMovie(${item.id}, ${i}, '${mediaTypeLabel}')" title="${i} star${i>1?'s':''}">★</span>
                `).join('')}
            </div>
        </div>`;
    } else if (IS_LOGGED_IN) {
        ratingHTML = `
        <div class="user-rating-section locked-section">
            <h4><i class="fas fa-lock me-2"></i>Rating Locked</h4>
            <p class="text-muted small mb-0">Purchase this content or subscribe to rate it</p>
        </div>`;
    }

    // Seasons section for TV
    let seasonsHTML = '';
    if (isTV && item.seasons && item.seasons.length > 0) {
        const validSeasons = item.seasons.filter(s => s.season_number > 0);
        const seasonTabs = validSeasons.map((s, idx) => {
            return `<button class="season-tab ${idx === 0 ? 'active' : ''}" onclick="loadSeasonEpisodes(${item.id}, ${s.season_number}, this)">${s.name || 'Season ' + s.season_number}<span class="season-ep-count">${s.episode_count} eps</span></button>`;
        }).join('');

        seasonsHTML = `
        <section class="cast-section seasons-section">
            <div class="container">
                <h2 class="section-title"><i class="fas fa-tv me-2"></i>Seasons & Episodes</h2>
                <div class="season-tabs">${seasonTabs}</div>
                <div id="episodes-container" class="episodes-container">
                    <div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div> <span class="text-muted ms-2">Select a season to view episodes</span></div>
                </div>
            </div>
        </section>`;
    }

    // Store show ID for season loading
    window.currentShowId = item.id;
    window.currentMediaType = mediaTypeLabel;
    window.currentHasAccess = hasAccess;

    document.getElementById('movie-content').innerHTML = `
        <!-- Backdrop -->
        <div class="movie-backdrop" ${backdropUrl ? `style="background-image: url(${backdropUrl})"` : ''}></div>

        <!-- Details -->
        <section class="movie-details-section">
            <div class="container">
                ${purchaseBannerHTML}
                <div class="movie-details-grid">
                    <!-- Poster -->
                    <div class="movie-poster-col">
                        <img src="${posterUrl}" alt="${title}" class="movie-poster-large">
                    </div>

                    <!-- Info -->
                    <div class="movie-info-col">
                        <h1 class="movie-detail-title">${title}</h1>
                        ${item.tagline ? `<p class="movie-tagline">"${item.tagline}"</p>` : ''}

                        <div class="movie-meta-row">
                            <span class="meta-badge rating-badge"><i class="fas fa-star"></i> ${item.vote_average?.toFixed(1)}/10</span>
                            <span class="meta-badge">${year}</span>
                            <span class="meta-badge">${durationInfo}</span>
                            ${statusBadge}
                            <span class="meta-badge media-type-badge ${isTV ? 'tv-badge' : 'movie-badge'}">${isTV ? 'TV Series' : 'Movie'}</span>
                            ${hasAccess && IS_LOGGED_IN ? '<span class="meta-badge access-badge"><i class="fas fa-unlock me-1"></i>Owned</span>' : ''}
                        </div>

                        <div class="genre-tags">${genres}</div>

                        <p class="movie-overview">${item.overview || 'No overview available.'}</p>

                        <div class="movie-actions">
                            <span id="jellyfin-watch-btn-slot"></span>
                            ${trailerKey ? `<a href="https://www.youtube.com/watch?v=${trailerKey}" target="_blank" class="btn btn-primary btn-lg"><i class="fas fa-play me-2"></i>Watch Trailer</a>` : ''}
                            <button onclick="toggleWatchlist(${item.id}, '${mediaTypeLabel}')" class="btn btn-outline-primary btn-lg" id="watchlist-btn">
                                <i class="fas fa-${inList ? 'check' : 'plus'} me-2"></i><span>${inList ? 'In Watchlist' : 'Add to List'}</span>
                            </button>
                        </div>

                        ${ratingHTML}
                    </div>
                </div>
            </div>
        </section>

        <!-- Cast -->
        ${castHTML ? `
        <section class="cast-section">
            <div class="container">
                <h2 class="section-title"><i class="fas fa-users me-2"></i>Cast</h2>
                <div class="cast-grid">${castHTML}</div>
            </div>
        </section>
        ` : ''}

        <!-- Seasons (TV only) -->
        ${seasonsHTML}

        <!-- Comments Section -->
        <section class="comments-section">
            <div class="container">
                <h2 class="section-title"><i class="fas fa-comments me-2"></i>Comments</h2>
                ${IS_LOGGED_IN && hasAccess ? `
                <div class="comment-form-card">
                    <div class="d-flex gap-3">
                        <div class="user-avatar-sm comment-avatar">${CURRENT_USERNAME.charAt(0).toUpperCase()}</div>
                        <div class="flex-grow-1">
                            <textarea class="form-control" id="comment-input" placeholder="Write a comment..." rows="3" maxlength="2000"></textarea>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small class="text-muted"><span id="comment-char-count">0</span>/2000</small>
                                <button class="btn btn-primary" onclick="postComment(${item.id}, '${mediaTypeLabel}')"><i class="fas fa-paper-plane me-2"></i>Post Comment</button>
                            </div>
                        </div>
                    </div>
                </div>
                ` : IS_LOGGED_IN ? `
                <div class="comment-locked-notice">
                    <i class="fas fa-lock me-2"></i>
                    <span>Purchase this content or <a href="/streamhive/public/subscribe.php">subscribe</a> to post comments</span>
                </div>
                ` : `
                <div class="comment-locked-notice">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    <span><a href="/streamhive/public/login.php">Sign in</a> to post comments</span>
                </div>
                `}
                <div id="comments-list">
                    <div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>
                </div>
            </div>
        </section>

        <!-- Similar -->
        ${similarHTML ? `
        <section class="similar-section">
            <div class="container">
                <h2 class="section-title"><i class="fas fa-film me-2"></i>More Like This</h2>
                <div class="similar-grid">${similarHTML}</div>
            </div>
        </section>
        ` : ''}
    `;

    document.title = title + ' - StreamHive';

    // Character count for comment input
    const commentInput = document.getElementById('comment-input');
    if (commentInput) {
        commentInput.addEventListener('input', () => {
            document.getElementById('comment-char-count').textContent = commentInput.value.length;
        });
    }

    // Load comments
    loadComments(item.id, mediaTypeLabel);

    // Auto-load first season for TV shows
    if (isTV && item.seasons && item.seasons.filter(s => s.season_number > 0).length > 0) {
        const firstSeason = item.seasons.filter(s => s.season_number > 0)[0].season_number;
        loadSeasonEpisodes(item.id, firstSeason);
    }
}

// Load episodes for a specific season
async function loadSeasonEpisodes(showId, seasonNumber, clickedTab) {
    // Update active tab
    if (clickedTab) {
        document.querySelectorAll('.season-tab').forEach(t => t.classList.remove('active'));
        clickedTab.classList.add('active');
    }

    const container = document.getElementById('episodes-container');
    container.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div> <span class="text-muted ms-2">Loading episodes...</span></div>';

    try {
        const data = await fetchTMDB('tv_season', { id: showId, season: seasonNumber });

        if (!data.episodes || data.episodes.length === 0) {
            container.innerHTML = '<p class="text-muted text-center py-4">No episodes found for this season.</p>';
            return;
        }

        // Fetch purchased episodes for this season
        let purchasedEps = [];
        let isSub = false;
        let isAdm = IS_ADMIN;
        if (IS_LOGGED_IN) {
            try {
                const pepResp = await fetch(`/streamhive/app/api/purchase.php?action=get_purchased_episodes&tmdb_id=${showId}&season=${seasonNumber}`);
                const pepData = await pepResp.json();
                purchasedEps = pepData.episodes || [];
                isSub = pepData.is_subscribed || false;
                isAdm = pepData.is_admin || IS_ADMIN;
            } catch(e) {}
        }

        let html = '';
        data.episodes.forEach(ep => {
            const still = ep.still_path
                ? `https://image.tmdb.org/t/p/w300${ep.still_path}`
                : '';
            const airDate = ep.air_date || '';
            const rating = ep.vote_average ? ep.vote_average.toFixed(1) : '';
            const overview = ep.overview || 'No description available.';

            const epOwned = isAdm || isSub || purchasedEps.includes(ep.episode_number);
            const playBtn = epOwned && IS_LOGGED_IN && window.jellyfinAvailable
                ? `<a href="/streamhive/public/watch.php?tmdb_id=${showId}&type=tv&season=${seasonNumber}&episode=${ep.episode_number}" class="episode-play-btn"><i class="fas fa-play"></i>Play</a>`
                : '';
            const purchaseBtn = IS_LOGGED_IN && !epOwned
                ? `<button class="btn btn-sm btn-outline-primary episode-purchase-btn" onclick="purchaseEpisode(${showId}, ${seasonNumber}, ${ep.episode_number})"><i class="fas fa-shopping-cart me-1"></i>5 pts</button>`
                : epOwned && IS_LOGGED_IN
                ? `<span class="episode-owned-badge"><i class="fas fa-unlock me-1"></i>Owned</span>`
                : '';

            html += `
                <div class="episode-card">
                    <div class="episode-still" ${still ? `style="background-image: url(${still})"` : ''}>
                        ${!still ? '<i class="fas fa-play-circle"></i>' : ''}
                        <span class="episode-number">E${ep.episode_number}</span>
                    </div>
                    <div class="episode-info">
                        <div class="d-flex justify-content-between align-items-start" style="gap: 8px;">
                            <h5 class="episode-title">${ep.episode_number}. ${ep.name || 'Episode ' + ep.episode_number}</h5>
                            <div class="d-flex gap-2 align-items-center">${playBtn}${purchaseBtn}</div>
                        </div>
                        <div class="episode-meta">
                            ${airDate ? `<span><i class="fas fa-calendar-alt me-1"></i>${airDate}</span>` : ''}
                            ${ep.runtime ? `<span><i class="fas fa-clock me-1"></i>${ep.runtime}m</span>` : ''}
                            ${rating ? `<span><i class="fas fa-star text-warning me-1"></i>${rating}</span>` : ''}
                        </div>
                        <p class="episode-overview">${overview}</p>
                    </div>
                </div>`;
        });

        container.innerHTML = html;
    } catch (e) {
        container.innerHTML = '<p class="text-danger text-center py-4">Failed to load episodes. Please try again.</p>';
    }
}

// Purchase content (movie or show)
async function purchaseContent(tmdbId, mediaType) {
    try {
        const response = await fetch('/streamhive/app/api/purchase.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'purchase_movie', tmdb_id: tmdbId, media_type: mediaType })
        });
        const data = await response.json();

        if (data.success) {
            showToast(data.message, 'success');
            // Update navbar balance
            const navBal = document.getElementById('nav-points-balance');
            if (navBal) navBal.textContent = data.balance;
            // Reload page to reflect new access
            setTimeout(() => window.location.reload(), 1000);
        } else if (data.need_points) {
            showToast(data.message, 'warning');
            setTimeout(() => window.location.href = '/streamhive/public/buy_points.php', 1500);
        } else {
            showToast(data.message, 'danger');
        }
    } catch(e) {
        showToast('Purchase failed', 'danger');
    }
}

// Purchase single episode
async function purchaseEpisode(tmdbId, season, episode) {
    try {
        const response = await fetch('/streamhive/app/api/purchase.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'purchase_episode', tmdb_id: tmdbId, season: season, episode: episode })
        });
        const data = await response.json();

        if (data.success) {
            showToast(data.message, 'success');
            const navBal = document.getElementById('nav-points-balance');
            if (navBal) navBal.textContent = data.balance;
            // Reload season to show updated state
            loadSeasonEpisodes(tmdbId, season);
        } else if (data.need_points) {
            showToast(data.message, 'warning');
            setTimeout(() => window.location.href = '/streamhive/public/buy_points.php', 1500);
        } else {
            showToast(data.message, 'danger');
        }
    } catch(e) {
        showToast('Purchase failed', 'danger');
    }
}

// Load comments for this content
async function loadComments(tmdbId, mediaType) {
    const container = document.getElementById('comments-list');
    try {
        const resp = await fetch(`/streamhive/app/api/comments.php?tmdb_id=${tmdbId}&media_type=${mediaType}`);
        const data = await resp.json();
        const comments = data.comments || [];

        if (comments.length === 0) {
            container.innerHTML = '<div class="no-comments"><i class="fas fa-comment-slash"></i><p class="text-muted">No comments yet. Be the first to share your thoughts!</p></div>';
            return;
        }

        container.innerHTML = comments.map(c => {
            const initial = c.username ? c.username.charAt(0).toUpperCase() : '?';
            const date = new Date(c.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            const canDelete = (c.user_id == CURRENT_USER_ID) || IS_ADMIN;
            const adminBadge = c.is_admin ? '<span class="comment-admin-badge"><i class="fas fa-shield-alt"></i> Admin</span>' : '';

            return `
            <div class="comment-card" id="comment-${c.id}">
                <div class="comment-header">
                    <div class="user-avatar-sm comment-avatar">${initial}</div>
                    <div>
                        <strong>${c.username}</strong> ${adminBadge}
                        <span class="comment-date">${date}</span>
                    </div>
                    ${canDelete ? `<button class="comment-delete-btn" onclick="deleteComment(${c.id})" title="Delete comment"><i class="fas fa-trash-alt"></i></button>` : ''}
                </div>
                <div class="comment-body">${escapeHtml(c.content)}</div>
            </div>`;
        }).join('');
    } catch(e) {
        container.innerHTML = '<p class="text-muted text-center">Failed to load comments</p>';
    }
}

// Post a comment
async function postComment(tmdbId, mediaType) {
    const input = document.getElementById('comment-input');
    const content = input.value.trim();
    if (!content) {
        showToast('Comment cannot be empty', 'warning');
        return;
    }

    try {
        const resp = await fetch('/streamhive/app/api/comments.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tmdb_id: tmdbId, media_type: mediaType, content: content })
        });
        const data = await resp.json();

        if (data.success) {
            showToast('Comment posted!', 'success');
            input.value = '';
            document.getElementById('comment-char-count').textContent = '0';
            loadComments(tmdbId, mediaType);
        } else {
            showToast(data.message, 'danger');
        }
    } catch(e) {
        showToast('Failed to post comment', 'danger');
    }
}

// Delete a comment
async function deleteComment(commentId) {
    if (!confirm('Delete this comment?')) return;

    try {
        const resp = await fetch(`/streamhive/app/api/comments.php?id=${commentId}`, { method: 'DELETE' });
        const data = await resp.json();

        if (data.success) {
            showToast('Comment deleted', 'success');
            const el = document.getElementById('comment-' + commentId);
            if (el) el.remove();
        } else {
            showToast(data.message, 'danger');
        }
    } catch(e) {
        showToast('Failed to delete comment', 'danger');
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// --- Jellyfin Streaming Integration ---
window.jellyfinAvailable = false;

async function checkJellyfinAvailability(tmdbId, mediaType) {
    console.log('[Jellyfin] Checking availability for TMDB ID:', tmdbId, 'type:', mediaType);
    try {
        const resp = await fetch(`/streamhive/app/api/jellyfin.php?action=check&tmdb_id=${tmdbId}`);
        const data = await resp.json();
        console.log('[Jellyfin] API response:', data);

        if (!data.available) {
            console.log('[Jellyfin] Not available, skipping');
            return;
        }

        window.jellyfinAvailable = true;
        const mediaTypeLabel = mediaType === 'tv' ? 'tv' : 'movie';
        console.log('[Jellyfin] mediaTypeLabel:', mediaTypeLabel);

        // Inject the Watch Now button
        const slot = document.getElementById('jellyfin-watch-btn-slot');
        console.log('[Jellyfin] Slot element:', slot);
        console.log('[Jellyfin] hasAccess:', window.currentHasAccess, 'IS_LOGGED_IN:', IS_LOGGED_IN);

        if (slot) {
            const hasAccess = window.currentHasAccess;

            if (mediaTypeLabel === 'movie') {
                if (hasAccess) {
                    slot.innerHTML = `
                        <a href="/streamhive/public/watch.php?tmdb_id=${tmdbId}&type=movie" class="watch-now-btn btn-lg">
                            <span class="btn-pulse"></span>
                            <i class="fas fa-play"></i>Watch Now
                        </a>`;
                    console.log('[Jellyfin] Injected Watch Now (movie, has access)');
                } else if (IS_LOGGED_IN) {
                    slot.innerHTML = `
                        <span class="watch-now-btn btn-lg disabled" title="Purchase or subscribe to watch">
                            <i class="fas fa-lock"></i>Watch Now
                        </span>`;
                    console.log('[Jellyfin] Injected locked Watch Now (movie, no access)');
                } else {
                    console.log('[Jellyfin] Not logged in, skipping movie button');
                }
            } else if (mediaTypeLabel === 'tv') {
                if (hasAccess) {
                    slot.innerHTML = `
                        <a href="/streamhive/public/watch.php?tmdb_id=${tmdbId}&type=tv&season=1&episode=1" class="watch-now-btn btn-lg">
                            <span class="btn-pulse"></span>
                            <i class="fas fa-play"></i>Start Watching — S01E01
                        </a>`;
                    console.log('[Jellyfin] Injected Start Watching (tv, has access)');
                } else if (IS_LOGGED_IN) {
                    slot.innerHTML = `
                        <span class="watch-now-btn btn-lg disabled" title="Purchase episodes or subscribe to watch">
                            <i class="fas fa-lock"></i>Start Watching
                        </span>`;
                    console.log('[Jellyfin] Injected locked Start Watching (tv, no access)');
                } else {
                    console.log('[Jellyfin] Not logged in, skipping tv button');
                }
            }
        } else {
            console.log('[Jellyfin] ERROR: slot element not found in DOM!');
        }

        // For TV: if episodes are already loaded, re-trigger the active season
        // to refresh play buttons (they check window.jellyfinAvailable)
        if (mediaTypeLabel === 'tv') {
            const activeTab = document.querySelector('.season-tab.active');
            if (activeTab) activeTab.click();
        }
    } catch(e) {
        console.error('[Jellyfin] Error in checkJellyfinAvailability:', e);
    }
}
</script>

<?php include '../app/includes/footer.php'; ?>
