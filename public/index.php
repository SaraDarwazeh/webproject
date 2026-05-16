<?php
$page_title = 'Home';
include '../app/includes/header.php';
include '../app/includes/navbar.php';
?>

<!-- HERO SECTION -->
<section class="hero-section" id="hero-section">
    <div class="hero-backdrop" id="hero-backdrop"></div>
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <div class="hero-badge mb-3">
                    <i class="fas fa-fire"></i> Trending Now
                </div>
                <h1 class="hero-title" id="hero-title">Discover Movies & Series</h1>
                <p class="hero-description" id="hero-description">
                    Explore thousands of real movies and TV series. Build your watchlist, rate your favorites, and get AI-powered recommendations.
                </p>
                <div class="hero-meta" id="hero-meta"></div>
                <div class="hero-buttons mt-4">
                    <a href="search.php" class="btn btn-primary btn-lg px-5">
                        <i class="fas fa-search me-2"></i>Explore Now
                    </a>
                    <a href="mylist.php" class="btn btn-outline-light btn-lg px-4">
                        <i class="fas fa-bookmark me-2"></i>My List
                    </a>
                </div>
            </div>
            <div class="col-lg-5 text-center d-none d-lg-block">
                <div class="hero-poster-wrapper" id="hero-poster"></div>
            </div>
        </div>
    </div>
</section>

<!-- CONTENT ROWS -->
<main class="movies-section">

    <!-- Proof of Concept: Available to Watch -->
    <section class="movie-row-section poc-section" id="poc-section">
        <div class="container-fluid px-4">
            <div class="section-header">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <h2 class="section-title mb-0">
                        <i class="fas fa-play-circle me-2" style="color: #e74c3c;"></i>Available to Watch
                    </h2>
                    <span class="poc-badge">PROOF OF CONCEPT</span>
                </div>
                <p class="section-subtitle">Stream these titles directly — powered by Jellyfin</p>
            </div>
            <div class="movie-scroll-container" id="poc-row">
                <div class="loading-skeleton"><div class="spinner-border text-primary"></div></div>
            </div>
        </div>
    </section>

    <!-- Trending Movies -->
    <section class="movie-row-section">
        <div class="container-fluid px-4">
            <div class="section-header">
                <h2 class="section-title"><i class="fas fa-fire text-danger me-2"></i>Trending Movies</h2>
                <p class="section-subtitle">Movies gaining momentum right now</p>
            </div>
            <div class="movie-scroll-container" id="trending-row">
                <div class="loading-skeleton"><div class="spinner-border text-primary"></div></div>
            </div>
        </div>
    </section>

    <!-- Trending Series -->
    <section class="movie-row-section">
        <div class="container-fluid px-4">
            <div class="section-header">
                <h2 class="section-title"><i class="fas fa-tv text-info me-2"></i>Trending Series</h2>
                <p class="section-subtitle">TV shows everyone is watching</p>
            </div>
            <div class="movie-scroll-container" id="trending-tv-row">
                <div class="loading-skeleton"><div class="spinner-border text-primary"></div></div>
            </div>
        </div>
    </section>

    <!-- Now Playing -->
    <section class="movie-row-section">
        <div class="container-fluid px-4">
            <div class="section-header">
                <h2 class="section-title"><i class="fas fa-play-circle text-primary me-2"></i>Now Playing</h2>
                <p class="section-subtitle">Currently in theaters</p>
            </div>
            <div class="movie-scroll-container" id="now-playing-row">
                <div class="loading-skeleton"><div class="spinner-border text-primary"></div></div>
            </div>
        </div>
    </section>

    <!-- Top Rated Movies -->
    <section class="movie-row-section">
        <div class="container-fluid px-4">
            <div class="section-header">
                <h2 class="section-title"><i class="fas fa-star text-warning me-2"></i>Top Rated Movies</h2>
                <p class="section-subtitle">Highest rated of all time</p>
            </div>
            <div class="movie-scroll-container" id="top-rated-row">
                <div class="loading-skeleton"><div class="spinner-border text-primary"></div></div>
            </div>
        </div>
    </section>

    <!-- Popular Series -->
    <section class="movie-row-section">
        <div class="container-fluid px-4">
            <div class="section-header">
                <h2 class="section-title"><i class="fas fa-tv text-secondary me-2"></i>Popular Series</h2>
                <p class="section-subtitle">Most popular TV shows</p>
            </div>
            <div class="movie-scroll-container" id="popular-tv-row">
                <div class="loading-skeleton"><div class="spinner-border text-primary"></div></div>
            </div>
        </div>
    </section>

    <!-- Popular Movies -->
    <section class="movie-row-section">
        <div class="container-fluid px-4">
            <div class="section-header">
                <h2 class="section-title"><i class="fas fa-heart text-danger me-2"></i>Popular Movies</h2>
                <p class="section-subtitle">Most popular movies</p>
            </div>
            <div class="movie-scroll-container" id="popular-row">
                <div class="loading-skeleton"><div class="spinner-border text-primary"></div></div>
            </div>
        </div>
    </section>
</main>

<script>
/**
 * Proof-of-concept Jellyfin titles (TMDB IDs + types).
 * These are the only titles available for actual streaming.
 */
const POC_TITLES = [
    { tmdb_id: 1396,   type: 'tv'    },  // Breaking Bad
    { tmdb_id: 60059,  type: 'tv'    },  // Better Call Saul
    { tmdb_id: 361743, type: 'movie' },  // Top Gun: Maverick
    { tmdb_id: 592834, type: 'movie' },  // El Camino
    { tmdb_id: 807,    type: 'movie' },  // Se7en
];

/**
 * Load the PoC row by fetching TMDB metadata for each title.
 */
async function loadPocRow() {
    const container = document.getElementById('poc-row');
    if (!container) return;

    try {
        const fetches = POC_TITLES.map(t => {
            const action = t.type === 'tv' ? 'tv' : 'movie';
            return fetchTMDB(action, { id: t.tmdb_id }).then(data => ({ ...data, _pocType: t.type }));
        });

        const results = await Promise.all(fetches);

        let html = '';
        results.forEach(item => {
            if (item.error || item.status_code === 34) return;

            const title = item.title || item.name || 'Unknown';
            const poster = item.poster_path ? `${IMG_BASE}w300${item.poster_path}` : '';
            const year = (item.release_date || item.first_air_date || '').split('-')[0];
            const rating = item.vote_average ? item.vote_average.toFixed(1) : '';
            const isTV = item._pocType === 'tv';
            const link = isTV
                ? `movie.php?id=${item.id}&type=tv`
                : `movie.php?id=${item.id}`;
            const typeBadge = isTV
                ? '<span class="media-type-badge tv-badge">TV</span>'
                : '';

            html += `
                <div class="movie-card poc-card">
                    <a href="${link}" class="movie-poster">
                        ${poster
                            ? `<img src="${poster}" alt="${title}" loading="lazy">`
                            : '<div class="poster-placeholder"><i class="fas fa-film"></i></div>'}
                        <div class="movie-card-overlay">
                            <div class="movie-card-rating"><i class="fas fa-star"></i> ${rating}</div>
                            ${typeBadge}
                            <span class="movie-card-view">View Details</span>
                        </div>
                        <div class="poc-stream-badge">
                            <i class="fas fa-play"></i> WATCH NOW
                        </div>
                    </a>
                    <p class="movie-card-title">${title}</p>
                    <p class="movie-card-meta">${year} · <i class="fas fa-server" style="font-size:0.7rem;"></i> Jellyfin</p>
                </div>`;
        });

        container.innerHTML = html || '<p class="text-muted">No streamable content configured.</p>';
    } catch(e) {
        container.innerHTML = '<p class="text-muted">Failed to load streamable content.</p>';
    }
}

document.addEventListener('DOMContentLoaded', async function() {
    // Load PoC row first (most prominent)
    loadPocRow();

    // Load hero from trending
    try {
        const trending = await fetchTMDB('trending');
        if (trending.results && trending.results.length > 0) {
            const hero = trending.results[0];
            const backdrop = document.getElementById('hero-backdrop');
            const title = document.getElementById('hero-title');
            const desc = document.getElementById('hero-description');
            const meta = document.getElementById('hero-meta');
            const poster = document.getElementById('hero-poster');

            if (hero.backdrop_path) {
                backdrop.style.backgroundImage = `url(https://image.tmdb.org/t/p/original${hero.backdrop_path})`;
            }
            title.textContent = hero.title || hero.name;
            desc.textContent = hero.overview ? hero.overview.substring(0, 200) + '...' : '';
            const heroYear = (hero.release_date || hero.first_air_date || '').split('-')[0];
            meta.innerHTML = `
                <span class="hero-meta-item"><i class="fas fa-star text-warning"></i> ${hero.vote_average?.toFixed(1)}/10</span>
                <span class="hero-meta-item"><i class="fas fa-calendar"></i> ${heroYear}</span>
            `;
            if (hero.poster_path) {
                poster.innerHTML = `<a href="movie.php?id=${hero.id}"><img src="https://image.tmdb.org/t/p/w500${hero.poster_path}" alt="${hero.title || hero.name}" class="hero-poster-img"></a>`;
            }

            // Load trending movie row
            renderMovieRow('trending-row', trending.results);
        }
    } catch(e) { console.error('Hero load error:', e); }

    // Load other rows
    loadMovieRow('trending-tv-row', 'trending_tv', 'tv');
    loadMovieRow('now-playing-row', 'now_playing');
    loadMovieRow('top-rated-row', 'top_rated');
    loadMovieRow('popular-tv-row', 'popular_tv', 'tv');
    loadMovieRow('popular-row', 'popular');
});
</script>

<?php include '../app/includes/footer.php'; ?>

