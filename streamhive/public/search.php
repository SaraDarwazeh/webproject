<?php
$page_title = 'Search';
include '../app/includes/header.php';
include '../app/includes/navbar.php';
?>

<main class="container-fluid" style="background-color: #0f0f0f; min-height: 100vh; padding: 60px 0;">
    <div class="container">
        <!-- CENTERED SEARCH SECTION -->
        <div style="max-width: 600px; margin: 0 auto 80px auto; text-align: center;">
            <h1 style="font-size: 2.5rem; margin-bottom: 10px;">Find Your Next Movie</h1>
            <p style="color: #999; margin-bottom: 40px; font-size: 1.1rem;">Search by title, genre, or keywords</p>

            <!-- Search Input -->
            <input type="text" id="search-input" placeholder="Search..." style="width: 100%; padding: 18px 20px; font-size: 1.1rem; background-color: #1a1a1a; border: 2px solid #333; border-radius: 8px; color: white; margin-bottom: 20px;">

            <!-- Genre Filter -->
            <select id="genre-filter" style="width: 100%; padding: 12px 15px; font-size: 1rem; background-color: #1a1a1a; border: 2px solid #333; border-radius: 8px; color: white; margin-bottom: 10px;">
                <option value="all">All Genres</option>
            </select>
            <p style="color: #666; font-size: 0.9rem; margin-top: 15px;">Results update as you type</p>
        </div>

        <!-- RESULTS SECTION - VERTICAL LIST -->
        <div style="background-color: #1a1a1a; border-radius: 12px; padding: 40px; border: 1px solid #333; max-width: 900px; margin: 0 auto;">
            <div id="search-results">
                <div style="text-align: center; padding: 60px 20px; color: #666;">
                    <div style="font-size: 3rem; margin-bottom: 20px;">[ ]</div>
                    <h3 style="color: #aaa; margin-bottom: 10px;">Start searching</h3>
                    <p>Enter a movie title, genre, or keywords to discover films</p>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        populateGenreDropdown();

        const searchInput = document.getElementById('search-input');
        const genreFilter = document.getElementById('genre-filter');

        searchInput.addEventListener('keyup', performSearch);
        genreFilter.addEventListener('change', performSearch);
    });

    function populateGenreDropdown() {
        const dropdown = document.getElementById('genre-filter');
        const genres = getGenres();
        let html = '<option value="all">All Genres</option>';
        genres.forEach(genre => {
            html += `<option value="${genre}">${genre}</option>`;
        });
        dropdown.innerHTML = html;
    }

    function performSearch() {
        const query = document.getElementById('search-input').value;
        const genre = document.getElementById('genre-filter').value;
        const resultsContainer = document.getElementById('search-results');

        resultsContainer.innerHTML = '<div style="text-align: center; padding: 40px;"><div class="spinner-border text-primary"></div></div>';

        setTimeout(() => {
            let results = mockMovies;

            if (query.trim()) {
                results = results.filter(movie =>
                    movie.title.toLowerCase().includes(query.toLowerCase()) ||
                    movie.genre.toLowerCase().includes(query.toLowerCase())
                );
            }

            if (genre && genre !== 'all') {
                results = results.filter(movie => movie.genre === genre);
            }

            if (results.length === 0) {
                resultsContainer.innerHTML = `
                    <div style="text-align: center; padding: 60px 20px; color: #666;">
                        <div style="font-size: 3rem; margin-bottom: 20px;">[ ]</div>
                        <h3 style="color: #aaa; margin-bottom: 10px;">No results found</h3>
                        <p>Try different keywords or filters</p>
                    </div>
                `;
                return;
            }

            let html = '';
            results.forEach(movie => {
                const inList = isInMyList(movie.id);
                html += `
                    <div style="display: flex; gap: 20px; margin-bottom: 25px; padding-bottom: 25px; border-bottom: 1px solid #333;">
                        <img src="${movie.poster}" alt="${movie.title}" onerror="this.style.backgroundColor='#1a1a1a'" style="width: 100px; height: 150px; object-fit: cover; border-radius: 6px; flex-shrink: 0;">
                        <div style="flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <h4 style="margin: 0 0 8px 0;">${movie.title}</h4>
                                <p style="margin: 0 0 12px 0; color: #999; font-size: 0.95rem;">${movie.genre} | ${movie.year} | ${movie.duration} min</p>
                                <p style="margin: 0 0 12px 0; color: #bbb; line-height: 1.5; font-size: 0.95rem;">${movie.description}</p>
                            </div>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <span style="background: #20c997; color: #000; padding: 6px 12px; border-radius: 4px; font-weight: 600; font-size: 0.9rem;">Rating: ${movie.rating}/10</span>
                                <a href="movie.php?id=${movie.id}" style="padding: 8px 16px; background: #20c997; color: #000; text-decoration: none; border-radius: 4px; font-weight: 600; border: none; cursor: pointer;">View Details</a>
                                <button onclick="toggleMyList(${movie.id})" style="padding: 8px 12px; background: #8e44ad; color: white; border-radius: 4px; border: none; cursor: pointer; font-weight: 600;">${inList ? 'Remove' : 'Add List'}</button>
                            </div>
                        </div>
                    </div>
                `;
            });

            resultsContainer.innerHTML = html;
        }, 300);
    }
</script>

<?php include '../app/includes/footer.php'; ?>
