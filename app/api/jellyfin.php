<?php
/**
 * Jellyfin API Bridge
 * Provides stream URLs and episode data for titles mapped in jellyfin_config.php.
 * Access control is enforced via the existing purchase/subscription system.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/jellyfin_config.php';
require_once __DIR__ . '/../controllers/purchase_controller.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

/**
 * Make a GET request to the local Jellyfin server.
 */
function jellyfinFetch($endpoint) {
    $url = JELLYFIN_URL . $endpoint;
    $separator = strpos($url, '?') !== false ? '&' : '?';
    $url .= $separator . 'api_key=' . JELLYFIN_API_KEY;

    $ctx = stream_context_create([
        'http' => [
            'method'  => 'GET',
            'header'  => "Accept: application/json\r\n",
            'timeout' => 8,
        ],
    ]);

    $response = @file_get_contents($url, false, $ctx);
    if ($response === false) {
        return null;
    }
    return json_decode($response, true);
}

/**
 * Check if Jellyfin is reachable.
 */
function isJellyfinOnline() {
    $info = jellyfinFetch('/System/Info/Public');
    return $info !== null && isset($info['ServerName']);
}

switch ($action) {

    /**
     * Check if a TMDB ID has a Jellyfin stream available.
     * Only checks the local config map — no server ping required.
     * GET ?action=check&tmdb_id=807
     */
    case 'check':
        $tmdbId = isset($_GET['tmdb_id']) ? intval($_GET['tmdb_id']) : 0;

        if ($tmdbId <= 0 || !isset(JELLYFIN_CONTENT_MAP[$tmdbId])) {
            echo json_encode(['available' => false]);
            break;
        }

        $entry = JELLYFIN_CONTENT_MAP[$tmdbId];

        echo json_encode([
            'available'   => true,
            'type'        => $entry['type'],
            'jellyfin_id' => $entry['jellyfin_id'],
        ]);
        break;

    /**
     * Get the direct stream URL for a movie.
     * GET ?action=stream_url&tmdb_id=807
     */
    case 'stream_url':
        $tmdbId = isset($_GET['tmdb_id']) ? intval($_GET['tmdb_id']) : 0;

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Login required']);
            break;
        }

        if ($tmdbId <= 0 || !isset(JELLYFIN_CONTENT_MAP[$tmdbId])) {
            http_response_code(404);
            echo json_encode(['error' => 'Content not available for streaming']);
            break;
        }

        $entry = JELLYFIN_CONTENT_MAP[$tmdbId];

        // Enforce access control
        $purchaseCtrl = new PurchaseController();
        if (!$purchaseCtrl->hasAccess($_SESSION['user_id'], $tmdbId, $entry['type'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Purchase or subscribe to watch this content']);
            break;
        }

        $jellyfinId = $entry['jellyfin_id'];
        $streamUrl = JELLYFIN_URL . "/Videos/{$jellyfinId}/stream.mp4?static=true&api_key=" . JELLYFIN_API_KEY;

        echo json_encode([
            'stream_url' => $streamUrl,
            'type'       => $entry['type'],
        ]);
        break;

    /**
     * Get seasons for a TV series from Jellyfin.
     * GET ?action=seasons&tmdb_id=1396
     */
    case 'seasons':
        $tmdbId = isset($_GET['tmdb_id']) ? intval($_GET['tmdb_id']) : 0;

        if ($tmdbId <= 0 || !isset(JELLYFIN_CONTENT_MAP[$tmdbId])) {
            echo json_encode(['seasons' => []]);
            break;
        }

        $entry = JELLYFIN_CONTENT_MAP[$tmdbId];
        if ($entry['type'] !== 'tv') {
            echo json_encode(['seasons' => []]);
            break;
        }

        $seriesId = $entry['jellyfin_id'];
        $data = jellyfinFetch("/Shows/{$seriesId}/Seasons");

        $seasons = [];
        if ($data && isset($data['Items'])) {
            foreach ($data['Items'] as $season) {
                $seasons[] = [
                    'id'            => $season['Id'],
                    'name'          => $season['Name'] ?? 'Unknown',
                    'season_number' => $season['IndexNumber'] ?? 0,
                ];
            }
        }

        echo json_encode(['seasons' => $seasons]);
        break;

    /**
     * Get episodes for a specific season from Jellyfin.
     * GET ?action=episodes&tmdb_id=1396&season=1
     */
    case 'episodes':
        $tmdbId  = isset($_GET['tmdb_id']) ? intval($_GET['tmdb_id']) : 0;
        $seasonNum = isset($_GET['season']) ? intval($_GET['season']) : 1;

        if ($tmdbId <= 0 || !isset(JELLYFIN_CONTENT_MAP[$tmdbId])) {
            echo json_encode(['episodes' => []]);
            break;
        }

        $entry = JELLYFIN_CONTENT_MAP[$tmdbId];
        if ($entry['type'] !== 'tv') {
            echo json_encode(['episodes' => []]);
            break;
        }

        $seriesId = $entry['jellyfin_id'];

        // First get seasons to find the Jellyfin season ID for this season number
        $seasonsData = jellyfinFetch("/Shows/{$seriesId}/Seasons");
        $seasonId = null;
        if ($seasonsData && isset($seasonsData['Items'])) {
            foreach ($seasonsData['Items'] as $s) {
                if (isset($s['IndexNumber']) && $s['IndexNumber'] == $seasonNum) {
                    $seasonId = $s['Id'];
                    break;
                }
            }
        }

        if (!$seasonId) {
            echo json_encode(['episodes' => []]);
            break;
        }

        $episodesData = jellyfinFetch("/Shows/{$seriesId}/Episodes?seasonId={$seasonId}");

        $episodes = [];
        if ($episodesData && isset($episodesData['Items'])) {
            foreach ($episodesData['Items'] as $ep) {
                $episodes[] = [
                    'jellyfin_id'    => $ep['Id'],
                    'episode_number' => $ep['IndexNumber'] ?? 0,
                    'season_number'  => $seasonNum,
                    'name'           => $ep['Name'] ?? 'Episode ' . ($ep['IndexNumber'] ?? '?'),
                ];
            }
        }

        echo json_encode(['episodes' => $episodes]);
        break;

    /**
     * Get stream URL for a specific TV episode.
     * GET ?action=episode_stream&tmdb_id=1396&season=1&episode=1
     */
    case 'episode_stream':
        $tmdbId     = isset($_GET['tmdb_id']) ? intval($_GET['tmdb_id']) : 0;
        $seasonNum  = isset($_GET['season']) ? intval($_GET['season']) : 0;
        $episodeNum = isset($_GET['episode']) ? intval($_GET['episode']) : 0;

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Login required']);
            break;
        }

        if ($tmdbId <= 0 || !isset(JELLYFIN_CONTENT_MAP[$tmdbId])) {
            http_response_code(404);
            echo json_encode(['error' => 'Content not available for streaming']);
            break;
        }

        $entry = JELLYFIN_CONTENT_MAP[$tmdbId];

        // Enforce access: user must own this specific episode (or be subscribed/admin)
        $purchaseCtrl = new PurchaseController();
        if (!$purchaseCtrl->hasAccess($_SESSION['user_id'], $tmdbId, 'tv', $seasonNum, $episodeNum)) {
            http_response_code(403);
            echo json_encode(['error' => 'Purchase this episode or subscribe to watch']);
            break;
        }

        // Find the Jellyfin episode item ID
        $seriesId = $entry['jellyfin_id'];
        $seasonsData = jellyfinFetch("/Shows/{$seriesId}/Seasons");
        $seasonId = null;
        if ($seasonsData && isset($seasonsData['Items'])) {
            foreach ($seasonsData['Items'] as $s) {
                if (isset($s['IndexNumber']) && $s['IndexNumber'] == $seasonNum) {
                    $seasonId = $s['Id'];
                    break;
                }
            }
        }

        if (!$seasonId) {
            http_response_code(404);
            echo json_encode(['error' => 'Season not found in Jellyfin']);
            break;
        }

        $episodesData = jellyfinFetch("/Shows/{$seriesId}/Episodes?seasonId={$seasonId}");
        $episodeJfId = null;
        if ($episodesData && isset($episodesData['Items'])) {
            foreach ($episodesData['Items'] as $ep) {
                if (isset($ep['IndexNumber']) && $ep['IndexNumber'] == $episodeNum) {
                    $episodeJfId = $ep['Id'];
                    break;
                }
            }
        }

        if (!$episodeJfId) {
            http_response_code(404);
            echo json_encode(['error' => 'Episode not found in Jellyfin']);
            break;
        }

        $streamUrl = JELLYFIN_URL . "/Videos/{$episodeJfId}/stream.mp4?static=true&api_key=" . JELLYFIN_API_KEY;

        echo json_encode([
            'stream_url'     => $streamUrl,
            'episode_number' => $episodeNum,
            'season_number'  => $seasonNum,
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action. Use: check, stream_url, seasons, episodes, episode_stream']);
        break;
}
?>
