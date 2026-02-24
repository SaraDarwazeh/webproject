/**
 * Main JavaScript for StreamHive
 */

document.addEventListener('DOMContentLoaded', function() {
    // Render movie rows on home page
    if (document.getElementById('trending-row')) {
        renderMovieRow('trending-row', getTrendingMovies(), 'Trending Now');
    }

    if (document.getElementById('new-row')) {
        renderMovieRow('new-row', getNewReleases(), 'New Releases');
    }

    if (document.getElementById('top-rated-row')) {
        renderMovieRow('top-rated-row', getTopRated(), 'Top Rated');
    }

    // Setup search input
    const searchInput = document.getElementById('search-input');
    const genreFilter = document.getElementById('genre-filter');

    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const query = this.value;
            const genre = genreFilter ? genreFilter.value : '';
            liveSearch(query, genre);
        });
    }

    if (genreFilter) {
        genreFilter.addEventListener('change', function() {
            const query = searchInput ? searchInput.value : '';
            const genre = this.value;
            liveSearch(query, genre);
        });
    }

    // Populate genre dropdown
    populateGenreDropdown();

    // Setup rating stars on movie details page
    setupRatingStars();

    // Render My List page
    if (document.getElementById('mylist-container')) {
        renderMyList();
    }

    // Render profile info
    if (document.getElementById('profile-info')) {
        renderProfileInfo();
    }
});

/**
 * Render movie row
 * @param {string} containerId - HTML element ID
 * @param {Array} movies - Array of movie objects
 * @param {string} title - Row title
 */
function renderMovieRow(containerId, movies, title) {
    const container = document.getElementById(containerId);
    if (!container) return;

    let html = `
        <div class="movie-scroll">
    `;

    movies.forEach(movie => {
        const inList = isInMyList(movie.id);
        html += `
            <div class="movie-card">
                <div class="movie-poster">
                    <img src="${movie.poster}" alt="${movie.title}">
                    <div class="movie-info">
                        <h6 class="text-white mb-1">${movie.title}</h6>
                        <div class="d-flex gap-1">
                            <a href="movie.php?id=${movie.id}" class="btn btn-sm btn-primary flex-grow-1">Details</a>
                            <button onclick="toggleMyList(${movie.id})" class="btn btn-sm btn-outline-primary" data-movie-id="${movie.id}" title="Add to list">
                                <span class="list-icon">${inList ? '✓' : '+'}</span>
                            </button>
                        </div>
                    </div>
                </div>
                <p class="mt-2 mb-0 small fw-500">${movie.title}</p>
                <p class="text-muted small">${movie.year} • ${movie.genre}</p>
            </div>
        `;
    });

    html += '</div>';
    container.innerHTML = html;
}

/**
 * Populate genre dropdown
 */
function populateGenreDropdown() {
    const dropdown = document.getElementById('genre-filter');
    if (!dropdown) return;

    const genres = getGenres();
    let html = '<option value="all">All Genres</option>';

    genres.forEach(genre => {
        html += `<option value="${genre}">${genre}</option>`;
    });

    dropdown.innerHTML = html;
}

/**
 * Setup rating stars
 */
function setupRatingStars() {
    const movieId = new URLSearchParams(window.location.search).get('id');
    if (!movieId) return;

    const starsContainer = document.getElementById('rating-stars');
    if (!starsContainer) return;

    const currentRating = getUserRating(movieId);

    let html = '';
    for (let i = 1; i <= 5; i++) {
        html += `
            <span class="star-input ${i <= currentRating ? 'active' : ''}"
                  onclick="submitRating(${movieId}, ${i})"
                  title="Rate ${i} stars">★</span>
        `;
    }

    starsContainer.innerHTML = html;
}

/**
 * Render My List page
 */
function renderMyList() {
    const container = document.getElementById('mylist-container');
    if (!container) return;

    if (myList.length === 0) {
        container.innerHTML = `
            <div class="no-results">
                <div class="no-results-icon">📽️</div>
                <h4>Your list is empty</h4>
                <p class="text-muted">Start adding movies to your list!</p>
                <a href="index.php" class="btn btn-primary mt-3">Browse Movies</a>
            </div>
        `;
        return;
    }

    const movies = myList
        .map(id => getMovieById(id))
        .filter(movie => movie !== undefined);

    let html = '<div class="row g-3">';

    movies.forEach(movie => {
        html += `
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="row g-0 h-100">
                        <div class="col-5">
                            <img src="${movie.poster}" class="img-fluid h-100" alt="${movie.title}" style="object-fit: cover;">
                        </div>
                        <div class="col-7">
                            <div class="card-body">
                                <h5 class="card-title">${movie.title}</h5>
                                <p class="card-text small text-muted">${movie.year}</p>
                                <div class="rating mb-2">
                                    <span class="text-primary">⭐ ${movie.rating}</span>
                                </div>
                                <div class="d-flex flex-column gap-2 small">
                                    <a href="movie.php?id=${movie.id}" class="btn btn-sm btn-primary">View</a>
                                    <button onclick="toggleMyList(${movie.id})" class="btn btn-sm btn-danger">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    html += '</div>';
    container.innerHTML = html;
}

/**
 * Render profile information
 */
function renderProfileInfo() {
    const container = document.getElementById('profile-info');
    if (!container) return;

    const html = `
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Profile Information</h5>
                <div class="mb-3">
                    <label class="text-muted small">Username</label>
                    <p>JohnDoe</p>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Email</label>
                    <p>john@example.com</p>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Member Since</label>
                    <p>January 2024</p>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Total Ratings</label>
                    <p>${Object.keys(ratings).length}</p>
                </div>
                <hr class="border-secondary">
                <button class="btn btn-primary w-100 mb-2">Edit Profile</button>
                <a href="index.php" class="btn btn-outline-danger w-100">Logout</a>
            </div>
        </div>
    `;

    container.innerHTML = html;
}

/**
 * Get URL parameter
 */
function getUrlParameter(name) {
    const params = new URLSearchParams(window.location.search);
    return params.get(name);
}
