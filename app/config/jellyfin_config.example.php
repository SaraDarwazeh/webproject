<?php
/**
 * Jellyfin Integration Config (Proof of Concept)
 * 
 * Copy this file to jellyfin_config.php and fill in your values:
 *   cp jellyfin_config.example.php jellyfin_config.php
 *
 * Only the titles mapped below are streamable via Jellyfin.
 */

define('JELLYFIN_URL', 'http://localhost:8096');
define('JELLYFIN_API_KEY', 'YOUR_JELLYFIN_API_KEY');

/**
 * TMDB ID => Jellyfin mapping.
 * Only content in this array will show a "Watch Now" button on the site.
 *
 * To find a Jellyfin Item ID: open the title in Jellyfin's web UI
 * and copy the "id=" value from the URL bar.
 */
const JELLYFIN_CONTENT_MAP = [
    // Movies
    // 361743 => ['jellyfin_id' => 'your_jellyfin_item_id', 'type' => 'movie'],

    // TV Series
    // 1396 => ['jellyfin_id' => 'your_jellyfin_item_id', 'type' => 'tv'],
];
?>
