<?php
/**
 * Jellyfin Integration Config (Proof of Concept)
 * Only the titles mapped below are streamable via Jellyfin.
 */

define('JELLYFIN_URL', 'http://localhost:8096');
define('JELLYFIN_API_KEY', 'REDACTED_JELLYFIN_API_KEY');

/**
 * TMDB ID => Jellyfin mapping.
 * Only content in this array will show a "Watch Now" button on the site.
 *
 * To find a Jellyfin Item ID: open the title in Jellyfin's web UI
 * and copy the "id=" value from the URL bar.
 */
const JELLYFIN_CONTENT_MAP = [
    // Movies
    361743 => ['jellyfin_id' => 'f1ef83df2253a8ab5920b9869c69ab01', 'type' => 'movie'],  // Top Gun: Maverick
    807 => ['jellyfin_id' => 'c12c681adcb8e81a381b368cc8e2b071', 'type' => 'movie'],  // Se7en
    592834 => ['jellyfin_id' => '45de80ba629c8a7f547e1c256fb23942', 'type' => 'movie'],  // El Camino (FIXME: wrong ID)

    // TV Series
    1396 => ['jellyfin_id' => '279d6e14c0c8bdc82aebbed486f10ab8', 'type' => 'tv'],     // Breaking Bad
    60059 => ['jellyfin_id' => '230ecd37ec34ceb3b84798ad225ce1f0', 'type' => 'tv'],     // Better Call Saul
];
?>