<?php
$page_title = 'Movie Details';
include '../app/includes/header.php';
include '../app/includes/navbar.php';
?>

<main>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const movieId = new URLSearchParams(window.location.search).get('id') || 1;
            const movie = getMovieById(movieId);

            if (!movie) {
                document.querySelector('main').innerHTML = `
                    <div class="container" style="padding: 60px 20px;">
                        <div style="background: #1a1a1a; padding: 40px; border-radius: 8px; text-align: center; border-left: 4px solid #e74c3c;">
                            <h3 style="color: #e74c3c; margin-bottom: 15px;">Movie not found</h3>
                            <p style="color: #999; margin-bottom: 20px;">The movie you are looking for does not exist.</p>
                            <a href="index.php" style="display: inline-block; padding: 12px 24px; background: #20c997; color: #000; text-decoration: none; border-radius: 4px; font-weight: 600;">Back to Home</a>
                        </div>
                    </div>
                `;
                return;
            }

            const inList = isInMyList(movie.id);
            const similar = getSimilarMovies(movie.genre, movie.id, 4);

            let html = `
                <!-- TWO-COLUMN LAYOUT -->
                <section style="background: linear-gradient(135deg, rgba(32, 201, 151, 0.08), rgba(142, 68, 173, 0.08)); padding: 60px 20px;">
                    <div class="container">
                        <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 50px; align-items: start;">
                            <!-- LEFT COLUMN: POSTER -->
                            <div>
                                <img src="${movie.poster}" alt="${movie.title}" onerror="this.style.backgroundColor='#1a1a1a'" style="width: 100%; border-radius: 12px; box-shadow: 0 10px 40px rgba(32, 201, 151, 0.2);">
                            </div>

                            <!-- RIGHT COLUMN: DETAILS -->
                            <div>
                                <h1 style="font-size: 2.5rem; margin-bottom: 15px; line-height: 1.2;">${movie.title}</h1>

                                <div style="display: flex; gap: 15px; margin-bottom: 30px; align-items: center;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="font-size: 1.5rem; color: #20c997;">*</span>
                                        <span style="font-size: 1.5rem; font-weight: 700;">${movie.rating}/10</span>
                                    </div>
                                    <span style="color: #666;">|</span>
                                    <span style="background: rgba(32, 201, 151, 0.2); padding: 6px 12px; border-radius: 4px; font-size: 0.9rem;">${movie.genre}</span>
                                    <span style="background: rgba(142, 68, 173, 0.2); padding: 6px 12px; border-radius: 4px; font-size: 0.9rem;">${movie.year}</span>
                                    <span style="background: rgba(100, 100, 100, 0.2); padding: 6px 12px; border-radius: 4px; font-size: 0.9rem;">${movie.duration} min</span>
                                </div>

                                <p style="font-size: 1.1rem; color: #ccc; line-height: 1.8; margin-bottom: 30px; max-width: 600px;">${movie.description}</p>

                                <div style="display: flex; gap: 15px; margin-bottom: 40px;">
                                    <button style="padding: 14px 32px; background: #20c997; color: #000; border: none; border-radius: 6px; font-weight: 700; font-size: 1rem; cursor: pointer;">Watch Now</button>
                                    <button onclick="toggleMyList(${movie.id})" style="padding: 14px 32px; background: #8e44ad; color: white; border: none; border-radius: 6px; font-weight: 700; font-size: 1rem; cursor: pointer;">${inList ? 'Remove from List' : 'Add to List'}</button>
                                </div>

                                <div style="border-top: 1px solid #333; border-bottom: 1px solid #333; padding: 25px 0; margin-bottom: 30px;">
                                    <h3 style="font-size: 1.2rem; margin-bottom: 20px;">Rate This Movie</h3>
                                    <p style="color: #999; font-size: 0.95rem; margin-bottom: 15px;">Click to rate (1-5 stars)</p>
                                    <div id="rating-stars" style="display: flex; gap: 10px; font-size: 2rem;">
                                    </div>
                                </div>

                                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; font-size: 0.95rem;">
                                    <div>
                                        <p style="color: #999; margin-bottom: 8px;">Genre</p>
                                        <p style="font-weight: 600;">${movie.genre}</p>
                                    </div>
                                    <div>
                                        <p style="color: #999; margin-bottom: 8px;">Year Released</p>
                                        <p style="font-weight: 600;">${movie.year}</p>
                                    </div>
                                    <div>
                                        <p style="color: #999; margin-bottom: 8px;">Duration</p>
                                        <p style="font-weight: 600;">${movie.duration} minutes</p>
                                    </div>
                                    <div>
                                        <p style="color: #999; margin-bottom: 8px;">User Rating</p>
                                        <p style="font-weight: 600;">${movie.rating} / 10</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- MORE LIKE THIS SECTION -->
                <section style="padding: 60px 20px; background-color: #0f0f0f;">
                    <div class="container">
                        <h2 style="font-size: 2rem; margin-bottom: 10px; border-left: 4px solid #20c997; padding-left: 15px;">More Like This</h2>
                        <p style="color: #999; margin-bottom: 40px; padding-left: 15px;">Similar ${movie.genre} movies</p>
            `;

            if (similar.length === 0) {
                html += '<p style="color: #666; text-align: center; padding: 40px;">No similar movies found</p>';
            } else {
                html += '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px;">';
                similar.forEach(sim => {
                    const simInList = isInMyList(sim.id);
                    html += `
                        <div style="background: #1a1a1a; border-radius: 8px; overflow: hidden; border: 1px solid #333; transition: transform 0.3s;">
                            <div style="position: relative; aspect-ratio: 2/3; overflow: hidden;">
                                <img src="${sim.poster}" alt="${sim.title}" onerror="this.style.backgroundColor='#1a1a1a'" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            <div style="padding: 15px;">
                                <h5 style="margin: 0 0 8px 0; font-size: 0.95rem;">${sim.title}</h5>
                                <p style="margin: 0 0 12px 0; color: #999; font-size: 0.85rem;">${sim.year}</p>
                                <div style="display: flex; gap: 8px;">
                                    <a href="movie.php?id=${sim.id}" style="flex: 1; padding: 8px; background: #20c997; color: #000; text-decoration: none; border-radius: 4px; font-size: 0.85rem; text-align: center; font-weight: 600; border: none; cursor: pointer;">View</a>
                                    <button onclick="toggleMyList(${sim.id})" style="padding: 8px 12px; background: #8e44ad; color: white; border-radius: 4px; border: none; cursor: pointer; font-weight: 600; font-size: 0.85rem;">${simInList ? 'X' : '+'}</button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
            }

            html += `
                    </div>
                </section>
            `;

            document.querySelector('main').innerHTML = html;
            setupRatingStars();
        });
    </script>
</main>

<?php include '../app/includes/footer.php'; ?>
