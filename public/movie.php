<?php
$page_title = 'Details';
include '../app/includes/header.php';
include '../app/includes/navbar.php';
?>

<main id="movie-content">
    <div class="loading-page">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="text-muted mt-3">Loading details...</p>
    </div>
</main>

<script>
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

        renderDetails(item, mediaType);
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

    // Get user rating
    let userRating = 0;
    try {
        const ratingData = await fetch('/streamhive/app/api/search.php?action=get_rating&tmdb_id=' + item.id);
        const rd = await ratingData.json();
        if (rd.logged_in) userRating = rd.rating;
    } catch(e) {}

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

    // Seasons section for TV — clickable tabs with episode details
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

    const mediaTypeLabel = isTV ? 'tv' : 'movie';

    document.getElementById('movie-content').innerHTML = `
        <!-- Backdrop -->
        <div class="movie-backdrop" ${backdropUrl ? `style="background-image: url(${backdropUrl})"` : ''}></div>

        <!-- Details -->
        <section class="movie-details-section">
            <div class="container">
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
                        </div>

                        <div class="genre-tags">${genres}</div>

                        <p class="movie-overview">${item.overview || 'No overview available.'}</p>

                        <div class="movie-actions">
                            ${trailerKey ? `<a href="https://www.youtube.com/watch?v=${trailerKey}" target="_blank" class="btn btn-primary btn-lg"><i class="fas fa-play me-2"></i>Watch Trailer</a>` : ''}
                            <button onclick="toggleWatchlist(${item.id}, '${mediaTypeLabel}')" class="btn btn-outline-primary btn-lg" id="watchlist-btn">
                                <i class="fas fa-${inList ? 'check' : 'plus'} me-2"></i><span>${inList ? 'In Watchlist' : 'Add to List'}</span>
                            </button>
                        </div>

                        <!-- Rating -->
                        <div class="user-rating-section">
                            <h4><i class="fas fa-star me-2"></i>Rate This ${isTV ? 'Show' : 'Movie'}</h4>
                            <div class="star-rating" id="star-rating">
                                ${[1,2,3,4,5].map(i => `
                                    <span class="star-input ${i <= userRating ? 'active' : ''}" onclick="rateMovie(${item.id}, ${i}, '${mediaTypeLabel}')" title="${i} star${i>1?'s':''}">★</span>
                                `).join('')}
                            </div>
                        </div>
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

        let html = '';
        data.episodes.forEach(ep => {
            const still = ep.still_path 
                ? `https://image.tmdb.org/t/p/w300${ep.still_path}` 
                : '';
            const airDate = ep.air_date || '';
            const rating = ep.vote_average ? ep.vote_average.toFixed(1) : '';
            const overview = ep.overview || 'No description available.';
            
            html += `
                <div class="episode-card">
                    <div class="episode-still" ${still ? `style="background-image: url(${still})"` : ''}>
                        ${!still ? '<i class="fas fa-play-circle"></i>' : ''}
                        <span class="episode-number">E${ep.episode_number}</span>
                    </div>
                    <div class="episode-info">
                        <h5 class="episode-title">${ep.episode_number}. ${ep.name || 'Episode ' + ep.episode_number}</h5>
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
</script>

<?php include '../app/includes/footer.php'; ?>

