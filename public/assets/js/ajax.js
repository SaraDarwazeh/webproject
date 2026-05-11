/**
 * StreamHive AJAX Functions
 * Real API calls to TMDB proxy + backend endpoints
 */

const TMDB_PROXY = '/streamhive/app/api/tmdb.php';
const API_BASE = '/streamhive/app/api';
const IMG_BASE = 'https://image.tmdb.org/t/p/';

/**
 * Fetch from TMDB proxy
 */
async function fetchTMDB(action, params = {}) {
    let url = `${TMDB_PROXY}?action=${action}`;
    Object.keys(params).forEach(key => {
        url += `&${key}=${encodeURIComponent(params[key])}`;
    });

    const response = await fetch(url);
    if (!response.ok) throw new Error('TMDB fetch failed');
    return await response.json();
}

/**
 * Get title/name for any TMDB item (movie or TV)
 */
function getMediaTitle(item) {
    return item.title || item.name || 'Unknown';
}

/**
 * Get release date/year for any TMDB item
 */
function getMediaYear(item) {
    const date = item.release_date || item.first_air_date || '';
    return date ? date.split('-')[0] : '';
}

/**
 * Get the media type string for URL
 */
function getMediaType(item) {
    // From search/multi results, media_type is explicit
    if (item.media_type) return item.media_type;
    // If it has 'name' but not 'title', it's TV
    if (item.name && !item.title) return 'tv';
    return 'movie';
}

/**
 * Build detail page link
 */
function getDetailLink(item) {
    const type = getMediaType(item);
    const base = `/streamhive/public/movie.php?id=${item.id}`;
    return type === 'tv' ? `${base}&type=tv` : base;
}

/**
 * Toggle watchlist
 */
async function toggleWatchlist(tmdbId, mediaType = 'movie') {
    try {
        const response = await fetch(`${API_BASE}/toggle_list.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tmdb_id: tmdbId, media_type: mediaType })
        });
        const data = await response.json();

        if (data.status === 'error' && response.status === 401) {
            showToast('Please login to use watchlist', 'warning');
            return;
        }

        // Update button state
        const btn = document.getElementById('watchlist-btn');
        if (btn) {
            const icon = btn.querySelector('i');
            const text = btn.querySelector('span');
            if (data.inList) {
                icon.className = 'fas fa-check me-2';
                text.textContent = 'In Watchlist';
                btn.classList.add('active');
            } else {
                icon.className = 'fas fa-plus me-2';
                text.textContent = 'Add to List';
                btn.classList.remove('active');
            }
        }

        showToast(data.message, 'success');
    } catch (e) {
        showToast('Failed to update watchlist', 'danger');
    }
}

/**
 * Rate a movie or show
 */
async function rateMovie(tmdbId, rating, mediaType = 'movie') {
    try {
        const response = await fetch(`${API_BASE}/rate.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tmdb_id: tmdbId, rating: rating, media_type: mediaType })
        });
        const data = await response.json();

        if (data.status === 'error' && response.status === 401) {
            showToast('Please login to rate', 'warning');
            return;
        }

        // Update stars
        const stars = document.querySelectorAll('.star-input');
        stars.forEach((star, index) => {
            star.classList.toggle('active', index < rating);
        });

        showToast(data.message || `Rated ${rating} stars`, 'success');
    } catch (e) {
        showToast('Failed to save rating', 'danger');
    }
}

/**
 * Render a horizontal movie/TV row
 */
function renderMovieRow(containerId, items, forceType = null) {
    const container = document.getElementById(containerId);
    if (!container || !items || items.length === 0) return;

    let html = '';
    items.forEach(item => {
        const poster = item.poster_path
            ? `${IMG_BASE}w300${item.poster_path}`
            : '';
        const title = getMediaTitle(item);
        const year = getMediaYear(item);
        const rating = item.vote_average ? item.vote_average.toFixed(1) : '';
        const type = forceType || getMediaType(item);
        const link = type === 'tv'
            ? `/streamhive/public/movie.php?id=${item.id}&type=tv`
            : `/streamhive/public/movie.php?id=${item.id}`;
        const badge = type === 'tv' ? '<span class="media-type-badge tv-badge">TV</span>' : '';

        html += `
            <div class="movie-card">
                <a href="${link}" class="movie-poster">
                    ${poster
                        ? `<img src="${poster}" alt="${title}" loading="lazy">`
                        : '<div class="poster-placeholder"><i class="fas fa-film"></i></div>'}
                    <div class="movie-card-overlay">
                        <div class="movie-card-rating"><i class="fas fa-star"></i> ${rating}</div>
                        ${badge}
                        <span class="movie-card-view">View Details</span>
                    </div>
                </a>
                <p class="movie-card-title">${title}</p>
                <p class="movie-card-meta">${year}</p>
            </div>`;
    });

    container.innerHTML = html;
}

/**
 * Load and render a movie/TV row from TMDB
 */
async function loadMovieRow(containerId, action, forceType = null) {
    try {
        const data = await fetchTMDB(action);
        if (data.results) {
            renderMovieRow(containerId, data.results, forceType);
        }
    } catch (e) {
        const container = document.getElementById(containerId);
        if (container) {
            container.innerHTML = '<p class="text-muted text-center py-4">Failed to load content</p>';
        }
    }
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container') || createToastContainer();

    const bgColor = type === 'success' ? 'rgba(32, 201, 151, 0.95)'
        : type === 'danger' ? 'rgba(231, 76, 60, 0.95)'
        : type === 'warning' ? 'rgba(243, 156, 18, 0.95)'
        : 'rgba(52, 152, 219, 0.95)';

    const icon = type === 'success' ? 'check-circle'
        : type === 'danger' ? 'exclamation-circle'
        : type === 'warning' ? 'exclamation-triangle'
        : 'info-circle';

    const toast = document.createElement('div');
    toast.className = 'custom-toast';
    toast.style.background = bgColor;
    toast.innerHTML = `
        <i class="fas fa-${icon} me-2"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" style="background:none;border:none;color:white;margin-left:12px;cursor:pointer;font-size:1.1rem;">&times;</button>
    `;

    container.appendChild(toast);

    // Animate in
    requestAnimationFrame(() => toast.classList.add('show'));

    // Auto remove after 3s
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);
    return container;
}

