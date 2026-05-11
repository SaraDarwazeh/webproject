/**
 * StreamHive Main JS
 * General UI interactions and page-level logic
 */

document.addEventListener('DOMContentLoaded', function() {
    // Navbar scroll effect
    const navbar = document.getElementById('main-navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 50);
        });
    }

    // Movie card hover effect (for touch devices too)
    document.addEventListener('mouseover', function(e) {
        const card = e.target.closest('.movie-poster');
        if (card) {
            const overlay = card.querySelector('.movie-card-overlay');
            if (overlay) overlay.style.opacity = '1';
        }
    });

    document.addEventListener('mouseout', function(e) {
        const card = e.target.closest('.movie-poster');
        if (card) {
            const overlay = card.querySelector('.movie-card-overlay');
            if (overlay) overlay.style.opacity = '0';
        }
    });
});
