<?php
$page_title = 'Search';
include '../app/includes/header.php';
include '../app/includes/navbar.php';
?>

<main class="search-page">
    <div class="container">
        <!-- Search Header -->
        <div class="search-header">
            <h1><i class="fas fa-search me-2"></i>Find Movies & Series</h1>
            <p class="text-muted">Search thousands of real movies and TV shows from TMDB</p>
        </div>

        <!-- Search Input -->
        <div class="search-input-wrapper">
            <div class="search-input-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="search-input" placeholder="Search movies & series..." autocomplete="off">
                <div class="search-loading" id="search-loading" style="display:none;">
                    <div class="spinner-border spinner-border-sm text-primary"></div>
                </div>
            </div>
        </div>

        <!-- Media Type Filter -->
        <div class="media-type-filter" style="display: flex; gap: 8px; justify-content: center; margin-bottom: 16px;">
            <button class="genre-chip active" data-media="all" onclick="filterMediaType(this, 'all')">All</button>
            <button class="genre-chip" data-media="movie" onclick="filterMediaType(this, 'movie')"><i class="fas fa-film me-1"></i>Movies</button>
            <button class="genre-chip" data-media="tv" onclick="filterMediaType(this, 'tv')"><i class="fas fa-tv me-1"></i>TV Series</button>
        </div>

        <!-- Genre Filters -->
        <div class="genre-filter-row" id="genre-filters">
            <button class="genre-chip active" data-genre="all">All Genres</button>
        </div>

        <!-- Results -->
        <div id="search-results">
            <div class="search-empty-state">
                <i class="fas fa-film"></i>
                <h3>Start searching</h3>
                <p>Enter a title to discover movies and TV series</p>
            </div>
        </div>
    </div>
</main>

<script>
let searchTimeout = null;
let currentGenre = 'all';
let currentMediaType = 'all';

document.addEventListener('DOMContentLoaded', async function() {
    // Load genres (movie genres by default)
    loadGenres();

    // Load popular content on page load
    try {
        const popular = await fetchTMDB('popular');
        if (popular.results) renderSearchResults(popular.results, 'movie');
    } catch(e) {}

    // Search input
    document.getElementById('search-input').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length === 0) {
            loadDefaultContent();
            return;
        }

        document.getElementById('search-loading').style.display = 'block';
        searchTimeout = setTimeout(async () => {
            try {
                const data = await fetchTMDB('search', { q: query });
                let results = data.results || [];
                // Filter by selected media type
                if (currentMediaType !== 'all') {
                    results = results.filter(r => r.media_type === currentMediaType);
                }
                renderSearchResults(results);
            } catch(e) {
                renderSearchResults([]);
            }
            document.getElementById('search-loading').style.display = 'none';
        }, 400);
    });
});

async function loadGenres() {
    try {
        const endpoint = currentMediaType === 'tv' ? 'tv_genres' : 'genres';
        const genreData = await fetchTMDB(endpoint);
        if (genreData.genres) {
            const container = document.getElementById('genre-filters');
            let html = '<button class="genre-chip active" data-genre="all" onclick="filterGenre(this, \'all\')">All Genres</button>';
            genreData.genres.forEach(g => {
                html += `<button class="genre-chip" data-genre="${g.id}" onclick="filterGenre(this, ${g.id})">${g.name}</button>`;
            });
            container.innerHTML = html;
        }
    } catch(e) {}
}

async function loadDefaultContent() {
    try {
        let data;
        if (currentMediaType === 'tv') {
            data = await fetchTMDB('popular_tv');
        } else {
            data = await fetchTMDB('popular');
        }
        if (data.results) renderSearchResults(data.results, currentMediaType !== 'all' ? currentMediaType : null);
    } catch(e) {}
}

function filterMediaType(btn, type) {
    document.querySelectorAll('.media-type-filter .genre-chip').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    currentMediaType = type;

    // Reload genres for the selected type
    loadGenres();

    const query = document.getElementById('search-input').value.trim();
    if (query) {
        // Re-search with type filter
        fetchTMDB('search', { q: query }).then(data => {
            let results = data.results || [];
            if (type !== 'all') {
                results = results.filter(r => r.media_type === type);
            }
            renderSearchResults(results);
        });
    } else {
        loadDefaultContent();
    }
}

function filterGenre(btn, genreId) {
    document.querySelectorAll('#genre-filters .genre-chip').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    currentGenre = genreId;

    const query = document.getElementById('search-input').value.trim();
    if (query) {
        // Re-search with genre filter
        fetchTMDB('search', { q: query }).then(data => {
            let results = data.results || [];
            if (currentMediaType !== 'all') {
                results = results.filter(r => r.media_type === currentMediaType);
            }
            if (genreId !== 'all') {
                results = results.filter(m => m.genre_ids && m.genre_ids.includes(genreId));
            }
            renderSearchResults(results);
        });
    } else {
        // Discover by genre
        if (genreId === 'all') {
            loadDefaultContent();
        } else {
            const discoverAction = currentMediaType === 'tv' ? 'discover_tv' : 'discover';
            fetchTMDB(discoverAction, { genre: genreId }).then(data => {
                renderSearchResults(data.results || [], currentMediaType !== 'all' ? currentMediaType : null);
            });
        }
    }
}

function renderSearchResults(items, forceType = null) {
    const container = document.getElementById('search-results');

    if (items.length === 0) {
        container.innerHTML = `
            <div class="search-empty-state">
                <i class="fas fa-search"></i>
                <h3>No results found</h3>
                <p>Try different keywords or filters</p>
            </div>`;
        return;
    }

    let html = '<div class="search-results-grid">';
    items.forEach(item => {
        const poster = item.poster_path
            ? `https://image.tmdb.org/t/p/w300${item.poster_path}`
            : '';
        const title = item.title || item.name || 'Unknown';
        const date = item.release_date || item.first_air_date || '';
        const year = date ? date.split('-')[0] : '';
        const rating = item.vote_average ? item.vote_average.toFixed(1) : 'N/A';
        const type = forceType || item.media_type || 'movie';
        const link = type === 'tv'
            ? `movie.php?id=${item.id}&type=tv`
            : `movie.php?id=${item.id}`;
        const typeBadge = type === 'tv'
            ? '<span class="media-type-badge tv-badge">TV</span>'
            : '<span class="media-type-badge movie-badge">Movie</span>';

        html += `
            <a href="${link}" class="search-result-card">
                <div class="search-poster" ${poster ? `style="background-image: url(${poster})"` : ''}>
                    ${!poster ? '<i class="fas fa-film"></i>' : ''}
                    <div class="search-poster-overlay">
                        <span class="search-rating"><i class="fas fa-star"></i> ${rating}</span>
                    </div>
                    ${typeBadge}
                </div>
                <div class="search-card-info">
                    <h5 class="search-card-title">${title}</h5>
                    <p class="search-card-meta">${year}</p>
                </div>
            </a>`;
    });
    html += '</div>';
    container.innerHTML = html;
}
</script>

<?php include '../app/includes/footer.php'; ?>

