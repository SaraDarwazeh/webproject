/**
 * AJAX Functions for StreamHive
 * Mock Data & Utility Functions
 */

// ===================== MOCK MOVIE DATABASE =====================
// Extended dataset with 12 movies for comprehensive filtering
const mockMovies = [
    // Trending & Recent (2024)
    {
        id: 1,
        title: 'Cyber Dawn',
        year: 2024,
        duration: 125,
        rating: 8.5,
        genre: 'Sci-Fi',
        description: 'A futuristic thriller about an AI gone rogue and the team trying to stop it from controlling human civilization.',
        poster: 'https://picsum.photos/300/450?random=101',
        isTrending: true
    },
    {
        id: 2,
        title: 'Neon City',
        year: 2024,
        duration: 110,
        rating: 7.5,
        genre: 'Sci-Fi',
        description: 'A cyberpunk adventure in a neon-lit metropolis where hackers rule and nothing is real.',
        poster: 'https://picsum.photos/300/450?random=102',
        isTrending: true
    },
    {
        id: 3,
        title: 'Silent Shadows',
        year: 2024,
        duration: 115,
        rating: 8.2,
        genre: 'Thriller',
        description: 'A psychological thriller that keeps you guessing until the final twist. Who is the real villain?',
        poster: 'https://picsum.photos/300/450?random=103',
        isTrending: true
    },
    // Top Rated (2023-2024)
    {
        id: 4,
        title: 'Aurora Rising',
        year: 2023,
        duration: 145,
        rating: 9.1,
        genre: 'Drama',
        description: 'An inspiring story of hope and redemption. A masterpiece that touches the human soul.',
        poster: 'https://picsum.photos/300/450?random=104',
        isTopRated: true
    },
    {
        id: 5,
        title: 'Forgotten Kingdom',
        year: 2023,
        duration: 150,
        rating: 8.7,
        genre: 'Fantasy',
        description: 'An epic fantasy saga of magic, destiny, and the heroes who must save the realm from darkness.',
        poster: 'https://picsum.photos/300/450?random=105',
        isTopRated: true
    },
    {
        id: 6,
        title: 'Ocean\'s Echo',
        year: 2023,
        duration: 138,
        rating: 8.3,
        genre: 'Adventure',
        description: 'An epic adventure across seven seas. Treasure, danger, and friendship await.',
        poster: 'https://picsum.photos/300/450?random=106',
        isTopRated: true
    },
    // More Classic Movies
    {
        id: 7,
        title: 'The Last Horizon',
        year: 2022,
        duration: 135,
        rating: 7.9,
        genre: 'Adventure',
        description: 'A journey to the edge of the world where legends come to life.',
        poster: 'https://picsum.photos/300/450?random=107'
    },
    {
        id: 8,
        title: 'Echoes of Tomorrow',
        year: 2022,
        duration: 128,
        rating: 7.4,
        genre: 'Sci-Fi',
        description: 'When time travel becomes possible, the past threatens to change everything.',
        poster: 'https://picsum.photos/300/450?random=108'
    },
    {
        id: 9,
        title: 'The Midnight Affair',
        year: 2023,
        duration: 118,
        rating: 7.6,
        genre: 'Thriller',
        description: 'A noir-inspired thriller set in the shadows of a bustling city.',
        poster: 'https://picsum.photos/300/450?random=109'
    },
    {
        id: 10,
        title: 'Heartbreak Highway',
        year: 2023,
        duration: 142,
        rating: 8.1,
        genre: 'Drama',
        description: 'A road trip that becomes a journey of self-discovery and love.',
        poster: 'https://picsum.photos/300/450?random=110'
    },
    {
        id: 11,
        title: 'Enchanted Forest',
        year: 2022,
        duration: 156,
        rating: 8.4,
        genre: 'Fantasy',
        description: 'A magical forest holds the key to saving an entire world from destruction.',
        poster: 'https://picsum.photos/300/450?random=111'
    },
    {
        id: 12,
        title: 'Deep Ocean Mystery',
        year: 2022,
        duration: 132,
        rating: 7.7,
        genre: 'Adventure',
        description: 'Explore the depths where ancient secrets and undiscovered life await.',
        poster: 'https://picsum.photos/300/450?random=112'
    }
];

let myList = JSON.parse(localStorage.getItem('myList')) || [];
let ratings = JSON.parse(localStorage.getItem('ratings')) || {};

/**
 * Setup image error handling - fallback to placeholder
 */
function setupImageFallback() {
    document.addEventListener('error', function(event) {
        if (event.target.tagName.toLowerCase() === 'img') {
            if (!event.target.src.includes('placeholder.jpg')) {
                event.target.src = 'assets/img/posters/placeholder.jpg';
            }
        }
    }, true);
}

// Initialize image fallback on document load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupImageFallback);
} else {
    setupImageFallback();
}

/**
 * Live Search - AJAX search with filtering
 * @param {string} query - Search query
 * @param {string} genre - Genre filter (optional)
 */
function liveSearch(query, genre = '') {
    const resultsContainer = document.getElementById('search-results');

    if (!resultsContainer) return;

    // Simulate API call delay
    resultsContainer.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Searching...</span></div></div>';

    // Simulate network delay
    setTimeout(() => {
        let results = mockMovies;

        // Filter by query
        if (query.trim()) {
            results = results.filter(movie =>
                movie.title.toLowerCase().includes(query.toLowerCase()) ||
                movie.genre.toLowerCase().includes(query.toLowerCase())
            );
        }

        // Filter by genre
        if (genre && genre !== 'all') {
            results = results.filter(movie => movie.genre === genre);
        }

        if (results.length === 0) {
            resultsContainer.innerHTML = `
                <div class="no-results">
                    <div class="no-results-icon">🔍</div>
                    <h4>No movies found</h4>
                    <p class="text-muted">Try another search or filter</p>
                </div>
            `;
            return;
        }

        // Render results
        let html = '<div class="row g-3">';
        results.forEach(movie => {
            const inList = myList.includes(movie.id);
            html += `
                <div class="col-md-4 col-lg-3">
                    <div class="card h-100">
                        <img src="${movie.poster}" class="card-img-top" alt="${movie.title}" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title">${movie.title}</h5>
                            <p class="card-text small text-muted">${movie.year} • ${movie.genre}</p>
                            <div class="d-flex gap-2">
                                <a href="movie.php?id=${movie.id}" class="btn btn-sm btn-primary flex-grow-1">Details</a>
                                <button onclick="toggleMyList(${movie.id})" class="btn btn-sm btn-outline-primary">
                                    <span class="list-icon">${inList ? '✓' : '+'}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        resultsContainer.innerHTML = html;
    }, 300);
}

/**
 * Toggle My List - Add or remove movie from watchlist
 * @param {number} movieId - Movie ID
 */
function toggleMyList(movieId) {
    const index = myList.indexOf(movieId);
    if (index > -1) {
        myList.splice(index, 1);
    } else {
        myList.push(movieId);
    }

    localStorage.setItem('myList', JSON.stringify(myList));

    // Update all UI elements for this movie
    const buttons = document.querySelectorAll(`[data-movie-id="${movieId}"]`);
    buttons.forEach(btn => {
        const icon = btn.querySelector('.list-icon') || btn;
        btn.classList.toggle('active');
        icon.textContent = myList.includes(movieId) ? '✓' : '+';
    });

    // Show toast
    showToast(
        myList.includes(movieId) ? `Added to My List` : `Removed from My List`,
        'success'
    );

    // Reload my list page if we're on it
    if (window.location.pathname.includes('mylist.php')) {
        location.reload();
    }
}

/**
 * Submit Rating - Update movie rating
 * @param {number} movieId - Movie ID
 * @param {number} rating - Rating value (1-5)
 */
function submitRating(movieId, rating) {
    ratings[movieId] = rating;
    localStorage.setItem('ratings', JSON.stringify(ratings));

    // Update star display
    const stars = document.querySelectorAll('.star-input');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.add('active');
        } else {
            star.classList.remove('active');
        }
    });

    showToast(`You rated this ${rating} out of 5 stars`, 'success');
}

/**
 * Show Bootstrap Toast notification
 * @param {string} message - Toast message
 * @param {string} type - Type: 'success', 'danger', 'warning', 'info'
 */
function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();

    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success' :
                   type === 'danger' ? 'bg-danger' :
                   type === 'warning' ? 'bg-warning' : 'bg-info';

    const toastHTML = `
        <div id="${toastId}" class="toast ${bgClass} text-white" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-body d-flex justify-content-between align-items-center">
                <span>${message}</span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

    toastContainer.insertAdjacentHTML('beforeend', toastHTML);

    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement);
    toast.show();

    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

/**
 * Create toast container if it doesn't exist
 */
function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.style.position = 'fixed';
    container.style.top = '20px';
    container.style.right = '20px';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

/**
 * Get movie by ID
 * @param {number} id - Movie ID
 */
function getMovieById(id) {
    return mockMovies.find(movie => movie.id === parseInt(id));
}

/**
 * Get all movies
 */
function getAllMovies() {
    return mockMovies;
}

/**
 * Get movies by genre
 * @param {string} genre - Genre name
 */
function getMoviesByGenre(genre) {
    if (genre === 'all' || !genre) return mockMovies;
    return mockMovies.filter(movie => movie.genre === genre);
}

/**
 * Get unique genres
 */
function getGenres() {
    return [...new Set(mockMovies.map(m => m.genre))];
}

/**
 * Get trending movies (2024 releases with good ratings)
 */
function getTrendingMovies() {
    return mockMovies
        .filter(m => m.year >= 2024)
        .sort((a, b) => b.rating - a.rating)
        .slice(0, 6);
}

/**
 * Get new releases (sorted by year, most recent first)
 */
function getNewReleases() {
    return [...mockMovies]
        .sort((a, b) => b.year - a.year)
        .slice(0, 6);
}

/**
 * Get top rated movies (rating > 8.0)
 */
function getTopRated() {
    return [...mockMovies]
        .filter(m => m.rating >= 8.0)
        .sort((a, b) => b.rating - a.rating)
        .slice(0, 6);
}

/**
 * Get similar movies by genre
 * @param {string} genre - Genre name
 * @param {number} excludeId - Movie ID to exclude
 * @param {number} limit - Number of results
 */
function getSimilarMovies(genre, excludeId = null, limit = 6) {
    return mockMovies
        .filter(m => m.genre === genre && m.id !== excludeId)
        .sort((a, b) => b.rating - a.rating)
        .slice(0, limit);
}

/**
 * Check if movie is in My List
 * @param {number} movieId - Movie ID
 */
function isInMyList(movieId) {
    return myList.includes(movieId);
}

/**
 * Get user's rating for movie
 * @param {number} movieId - Movie ID
 */
function getUserRating(movieId) {
    return ratings[movieId] || 0;
}
