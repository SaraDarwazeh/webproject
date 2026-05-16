<?php
$page_title = 'Watch';
include '../app/includes/header.php';
include '../app/includes/navbar.php';

$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
?>

<main id="watch-page-content">
    <div class="loading-page">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="text-muted mt-3">Preparing stream...</p>
    </div>
</main>

<script>
const IS_LOGGED_IN = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
const IS_ADMIN = <?php echo $isAdmin ? 'true' : 'false'; ?>;

document.addEventListener('DOMContentLoaded', async function() {
    const params = new URLSearchParams(window.location.search);
    const tmdbId = params.get('tmdb_id');
    const mediaType = params.get('type') || 'movie';
    const season = params.get('season');
    const episode = params.get('episode');

    if (!IS_LOGGED_IN) {
        renderError('Please log in to watch content', true);
        return;
    }

    if (!tmdbId) {
        renderError('No content specified');
        return;
    }

    try {
        if (mediaType === 'tv' && season && episode) {
            await loadEpisodeStream(tmdbId, season, episode);
        } else if (mediaType === 'movie') {
            await loadMovieStream(tmdbId);
        } else if (mediaType === 'tv') {
            // TV series without specific episode — load episode picker
            await loadSeriesPicker(tmdbId);
        } else {
            renderError('Invalid content type');
        }
    } catch(e) {
        renderError('Failed to load stream: ' + e.message);
    }
});

async function loadMovieStream(tmdbId) {
    // Get TMDB metadata for title/backdrop
    const meta = await fetchTMDB('movie', { id: tmdbId });
    const title = meta.title || 'Unknown';
    const backdrop = meta.backdrop_path
        ? `https://image.tmdb.org/t/p/original${meta.backdrop_path}`
        : '';

    // Get stream URL
    const resp = await fetch(`/streamhive/app/api/jellyfin.php?action=stream_url&tmdb_id=${tmdbId}`);
    const data = await resp.json();

    if (!resp.ok || data.error) {
        renderError(data.error || 'Cannot load stream');
        return;
    }

    renderPlayer({
        title: title,
        subtitle: '',
        streamUrl: data.stream_url,
        backdrop: backdrop,
        backUrl: `/streamhive/public/movie.php?id=${tmdbId}`,
    });

    document.title = `${title} - StreamHive`;
}

async function loadEpisodeStream(tmdbId, season, episode) {
    // Get TMDB metadata
    const mediaAction = 'tv';
    const meta = await fetchTMDB(mediaAction, { id: tmdbId });
    const showTitle = meta.name || 'Unknown';

    // Get episode metadata from TMDB
    let epTitle = `S${season.toString().padStart(2,'0')}E${episode.toString().padStart(2,'0')}`;
    try {
        const seasonData = await fetchTMDB('tv_season', { id: tmdbId, season: season });
        if (seasonData.episodes) {
            const epMeta = seasonData.episodes.find(e => e.episode_number == episode);
            if (epMeta) {
                epTitle = `S${season.toString().padStart(2,'0')}E${episode.toString().padStart(2,'0')} — ${epMeta.name}`;
            }
        }
    } catch(e) { /* use default title */ }

    // Get stream URL
    const resp = await fetch(
        `/streamhive/app/api/jellyfin.php?action=episode_stream&tmdb_id=${tmdbId}&season=${season}&episode=${episode}`
    );
    const data = await resp.json();

    if (!resp.ok || data.error) {
        renderError(data.error || 'Cannot load episode stream');
        return;
    }

    // Find prev/next episodes for navigation
    let prevEp = null, nextEp = null;
    try {
        const jfEpisodes = await fetch(`/streamhive/app/api/jellyfin.php?action=episodes&tmdb_id=${tmdbId}&season=${season}`);
        const jfData = await jfEpisodes.json();
        const eps = jfData.episodes || [];
        const currentIdx = eps.findIndex(e => e.episode_number == episode);
        if (currentIdx > 0) prevEp = eps[currentIdx - 1];
        if (currentIdx < eps.length - 1) nextEp = eps[currentIdx + 1];
    } catch(e) {}

    const navHTML = `
        <div class="episode-nav">
            ${prevEp
                ? `<a href="watch.php?tmdb_id=${tmdbId}&type=tv&season=${season}&episode=${prevEp.episode_number}" class="episode-nav-btn">
                       <i class="fas fa-chevron-left me-2"></i>Previous Episode
                   </a>`
                : '<span></span>'
            }
            ${nextEp
                ? `<a href="watch.php?tmdb_id=${tmdbId}&type=tv&season=${season}&episode=${nextEp.episode_number}" class="episode-nav-btn">
                       Next Episode<i class="fas fa-chevron-right ms-2"></i>
                   </a>`
                : '<span></span>'
            }
        </div>`;

    renderPlayer({
        title: showTitle,
        subtitle: epTitle,
        streamUrl: data.stream_url,
        backdrop: '',
        backUrl: `/streamhive/public/movie.php?id=${tmdbId}&type=tv`,
        extraHTML: navHTML,
    });

    document.title = `${showTitle} ${epTitle} - StreamHive`;
}

async function loadSeriesPicker(tmdbId) {
    const meta = await fetchTMDB('tv', { id: tmdbId });
    const showTitle = meta.name || 'Unknown';

    renderError(`Please select an episode to watch from the <a href="/streamhive/public/movie.php?id=${tmdbId}&type=tv">${showTitle} details page</a>.`);
}

function renderPlayer({ title, subtitle, streamUrl, backdrop, backUrl, extraHTML = '' }) {
    document.getElementById('watch-page-content').innerHTML = `
        <div class="watch-page">
            <div class="player-header">
                <a href="${backUrl}" class="player-back-btn">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
                <div class="player-title-info">
                    <h2 class="player-title">${title}</h2>
                    ${subtitle ? `<span class="player-subtitle">${subtitle}</span>` : ''}
                </div>
                <div class="player-header-spacer"></div>
            </div>

            <div class="video-player-container">
                <video
                    id="main-video-player"
                    class="video-player"
                    controls
                    autoplay
                    preload="metadata"
                    src="${streamUrl}"
                >
                    Your browser does not support HTML5 video.
                </video>
                <div class="video-error-overlay" id="video-error" style="display:none;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Stream unavailable. Make sure Jellyfin is running.</p>
                    <button class="btn btn-primary btn-sm" onclick="document.getElementById('main-video-player').load()">
                        <i class="fas fa-redo me-2"></i>Retry
                    </button>
                </div>
            </div>

            ${extraHTML}
        </div>
    `;

    // Handle video errors
    const video = document.getElementById('main-video-player');
    video.addEventListener('error', () => {
        document.getElementById('video-error').style.display = 'flex';
    });
}

function renderError(msg, showLogin = false) {
    document.getElementById('watch-page-content').innerHTML = `
        <div class="container py-5 text-center">
            <div style="background: var(--card-bg); padding: 60px; border-radius: 16px; border: 1px solid var(--border-color); max-width: 500px; margin: 80px auto;">
                <i class="fas fa-play-circle" style="font-size: 3rem; color: var(--primary); opacity: 0.4; margin-bottom: 20px;"></i>
                <h3 style="color: var(--text-secondary); margin-bottom: 12px;">${msg}</h3>
                ${showLogin
                    ? '<a href="/streamhive/public/login.php" class="btn btn-primary mt-3"><i class="fas fa-sign-in-alt me-2"></i>Login</a>'
                    : '<a href="/streamhive/public/index.php" class="btn btn-outline-primary mt-3"><i class="fas fa-home me-2"></i>Home</a>'
                }
            </div>
        </div>`;
}
</script>

<?php include '../app/includes/footer.php'; ?>
