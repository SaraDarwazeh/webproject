<?php
$page_title = 'My List';
include '../app/includes/header.php';
include '../app/includes/navbar.php';
?>

<main class="container-fluid" style="background-color: #0f0f0f; min-height: 100vh; padding: 40px 20px;">
    <div class="container">
        <h1 style="font-size: 2.2rem; margin-bottom: 30px;">My Watchlist</h1>

        <div id="mylist-container">
            <div style="text-align: center; padding: 80px 20px;">
                <div class="spinner-border text-primary"></div>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        renderMyList();
    });

    function renderMyList() {
        const container = document.getElementById('mylist-container');

        if (myList.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; padding: 80px 20px;">
                    <div style="font-size: 4rem; margin-bottom: 20px; opacity: 0.3;">[ ]</div>
                    <h2 style="color: #ccc; margin-bottom: 10px;">Your watchlist is empty</h2>
                    <p style="color: #999; margin-bottom: 30px; font-size: 1.1rem;">Start adding movies to your list</p>
                    <a href="search.php" style="display: inline-block; padding: 12px 30px; background: #20c997; color: #000; text-decoration: none; border-radius: 6px; font-weight: 600;">Browse Movies</a>
                </div>
            `;
            return;
        }

        const movies = myList
            .map(id => getMovieById(id))
            .filter(movie => movie !== undefined);

        let html = `<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 25px;">`;

        movies.forEach(movie => {
            const rating = getUserRating(movie.id);
            html += `
                <div style="background: #1a1a1a; border-radius: 10px; overflow: hidden; border: 1px solid #333; display: flex; flex-direction: column;">
                    <div style="position: relative; aspect-ratio: 2/3; overflow: hidden;">
                        <img src="${movie.poster}" alt="${movie.title}" onerror="this.style.backgroundColor='#1a1a1a'" style="width: 100%; height: 100%; object-fit: cover;">
                        <div style="position: absolute; top: 10px; right: 10px; background: rgba(32, 201, 151, 0.9); color: #000; padding: 8px 12px; border-radius: 6px; font-weight: 700;">${movie.rating}/10</div>
                    </div>
                    <div style="padding: 18px; display: flex; flex-direction: column; flex-grow: 1;">
                        <h3 style="margin: 0 0 10px 0; font-size: 1rem; line-height: 1.3;">${movie.title}</h3>
                        <p style="margin: 0 0 15px 0; color: #999; font-size: 0.9rem;">${movie.genre} | ${movie.year}</p>
                        <p style="margin: 0 0 20px 0; color: #bbb; font-size: 0.9rem; flex-grow: 1; line-height: 1.5;">${movie.description}</p>
                        <div style="display: flex; gap: 10px;">
                            <a href="movie.php?id=${movie.id}" style="flex: 1; padding: 10px; background: #20c997; color: #000; text-decoration: none; border-radius: 4px; font-weight: 600; text-align: center; font-size: 0.9rem; border: none; cursor: pointer;">Details</a>
                            <button onclick="toggleMyList(${movie.id})" style="padding: 10px 15px; background: #e74c3c; color: white; border-radius: 4px; border: none; cursor: pointer; font-weight: 600; font-size: 0.9rem;">Remove</button>
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        container.innerHTML = html;
    }
</script>

<?php include '../app/includes/footer.php'; ?>
