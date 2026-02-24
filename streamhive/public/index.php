<?php
$page_title = 'Home';
include '../app/includes/header.php';
include '../app/includes/navbar.php';
?>

<!-- FULL-WIDTH HERO SECTION -->
<section class="hero-section" style="background: linear-gradient(135deg, rgba(32, 201, 151, 0.15), rgba(142, 68, 173, 0.15)); height: 600px; display: flex; align-items: center;">
    <div class="container-fluid">
        <div class="row align-items-center h-100">
            <div class="col-lg-7">
                <h1 class="display-2 fw-bold mb-4" style="color: #20c997; line-height: 1.2;">StreamHive</h1>
                <p class="fs-5 text-light mb-4" style="max-width: 500px; line-height: 1.8;">Your destination for thousands of movies. Discover blockbusters, indie films, and everything in between. Start your journey today.</p>
                <div class="d-flex gap-3">
                    <a href="search.php" class="btn btn-primary btn-lg px-5">Search Movies</a>
                    <a href="mylist.php" class="btn btn-outline-primary btn-lg px-5">My List</a>
                </div>
            </div>
            <div class="col-lg-5 text-center">
                <img src="https://picsum.photos/400/600?random=11" alt="Featured Movie Poster" onerror="this.src='assets/img/posters/placeholder.jpg'" style="max-width: 100%; height: auto; border-radius: 12px; box-shadow: 0 10px 40px rgba(32, 201, 151, 0.3);">
            </div>
        </div>
    </div>
</section>

<main class="container-fluid" style="background-color: #0f0f0f; padding: 60px 0;">
    <!-- HORIZONTAL SCROLL ROW 1 -->
    <section style="margin-bottom: 80px;">
        <div class="container-fluid">
            <h2 class="mb-4" style="font-size: 2rem; font-weight: 700; border-left: 5px solid #20c997; padding-left: 20px;">Trending Now</h2>
            <p class="text-muted mb-4 ps-5">Latest releases gaining momentum</p>
            <div id="trending-row" style="overflow-x: auto; display: flex; gap: 20px; padding: 0 20px; scroll-behavior: smooth;">
                <div class="spinner-border text-primary mx-auto my-5"></div>
            </div>
        </div>
    </section>

    <!-- HORIZONTAL SCROLL ROW 2 -->
    <section style="margin-bottom: 80px;">
        <div class="container-fluid">
            <h2 class="mb-4" style="font-size: 2rem; font-weight: 700; border-left: 5px solid #20c997; padding-left: 20px;">New Releases</h2>
            <p class="text-muted mb-4 ps-5">Recently added to our library</p>
            <div id="new-row" style="overflow-x: auto; display: flex; gap: 20px; padding: 0 20px; scroll-behavior: smooth;">
                <div class="spinner-border text-primary mx-auto my-5"></div>
            </div>
        </div>
    </section>

    <!-- HORIZONTAL SCROLL ROW 3 -->
    <section style="margin-bottom: 80px;">
        <div class="container-fluid">
            <h2 class="mb-4" style="font-size: 2rem; font-weight: 700; border-left: 5px solid #20c997; padding-left: 20px;">Top Rated</h2>
            <p class="text-muted mb-4 ps-5">Highest rated by users</p>
            <div id="top-rated-row" style="overflow-x: auto; display: flex; gap: 20px; padding: 0 20px; scroll-behavior: smooth;">
                <div class="spinner-border text-primary mx-auto my-5"></div>
            </div>
        </div>
    </section>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        renderMovieRow('trending-row', getTrendingMovies());
        renderMovieRow('new-row', getNewReleases());
        renderMovieRow('top-rated-row', getTopRated());
    });

    function renderMovieRow(containerId, movies) {
        const container = document.getElementById(containerId);
        if (!container) return;

        let html = '';
        movies.forEach(movie => {
            const inList = isInMyList(movie.id);
            html += `
                <div style="flex: 0 0 200px; min-width: 200px;">
                    <div style="position: relative; cursor: pointer; border-radius: 8px; overflow: hidden; aspect-ratio: 2/3;">
                        <img src="${movie.poster}" alt="${movie.title}" onerror="this.style.backgroundColor='#1a1a1a'" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;">
                        <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.7); opacity: 0; transition: opacity 0.3s; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; padding: 15px; color: white; text-align: center;">
                            <h6 style="margin-bottom: 10px; margin-top: 0;">${movie.title}</h6>
                            <div style="display: flex; gap: 8px; width: 100%;">
                                <a href="movie.php?id=${movie.id}" style="flex: 1; padding: 8px; background: #20c997; color: #000; text-decoration: none; border-radius: 4px; font-size: 0.875rem; font-weight: 600; border: none; cursor: pointer;">View</a>
                                <button onclick="toggleMyList(${movie.id})" style="flex: 0 0 35px; padding: 8px; background: #8e44ad; color: white; border-radius: 4px; border: none; cursor: pointer; font-weight: 600;">${inList ? 'X' : '+'}</button>
                            </div>
                        </div>
                    </div>
                    <p style="margin: 10px 0 5px 0; font-weight: 500;">${movie.title}</p>
                    <p style="margin: 0; font-size: 0.875rem; color: #999;">${movie.year} | ${movie.genre}</p>
                </div>
            `;
        });

        container.innerHTML = html;
    }
</script>

<?php include '../app/includes/footer.php'; ?>
