<?php
/**
 * Content Filter Configuration
 * 
 * Filters out movies/TV shows with mature content (nudity, etc.)
 * while preserving specific whitelisted titles.
 */

// Master toggle — set to false to disable all filtering
define('CONTENT_FILTER_ENABLED', true);

/**
 * Whitelisted TMDB IDs — these are ALWAYS shown regardless of certification.
 * Format: array of integer TMDB IDs.
 */
$WHITELISTED_MOVIES = [
    559969, // El Camino: A Breaking Bad Movie
    807,    // Se7en
    361743, // Top Gun: Maverick
];

$WHITELISTED_TV = [
    1396,  // Breaking Bad
    60059, // Better Call Saul
];

/**
 * Allowed certifications (US ratings system).
 * Any title with a certification NOT in this list will be filtered out,
 * unless it is whitelisted above.
 */
$ALLOWED_MOVIE_CERTS = ['G', 'PG', 'PG-13', 'NR', ''];
$ALLOWED_TV_CERTS    = ['TV-Y', 'TV-Y7', 'TV-G', 'TV-PG', 'TV-14', 'NR', ''];

/**
 * Check if a TMDB ID is whitelisted.
 */
function isWhitelisted($tmdbId, $mediaType = 'movie') {
    global $WHITELISTED_MOVIES, $WHITELISTED_TV;
    if ($mediaType === 'tv') {
        return in_array((int)$tmdbId, $WHITELISTED_TV, true);
    }
    return in_array((int)$tmdbId, $WHITELISTED_MOVIES, true);
}

/**
 * Check if a certification is allowed.
 */
function isCertAllowed($cert, $mediaType = 'movie') {
    global $ALLOWED_MOVIE_CERTS, $ALLOWED_TV_CERTS;
    $allowed = ($mediaType === 'tv') ? $ALLOWED_TV_CERTS : $ALLOWED_MOVIE_CERTS;
    return in_array($cert, $allowed, true);
}
?>
