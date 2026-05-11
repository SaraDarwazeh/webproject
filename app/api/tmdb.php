<?php
/**
 * TMDB API Proxy
 * Server-side proxy to keep API keys hidden from the browser.
 * Includes content filtering to remove mature-rated titles.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/config/content_filter.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

$baseUrl = 'https://api.themoviedb.org/3';
$headers = [
    'http' => [
        'method' => 'GET',
        'header' => "Authorization: Bearer " . TMDB_ACCESS_TOKEN . "\r\n" .
                    "Accept: application/json\r\n",
        'timeout' => 10
    ]
];
$context = stream_context_create($headers);

/**
 * Fetch JSON from a TMDB URL.
 */
function tmdbFetch($url, $context) {
    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        return ['error' => 'Failed to fetch from TMDB'];
    }
    return json_decode($response, true);
}

// ─── Content Filtering Helpers ───────────────────────────────────────────────

/**
 * Get the US certification for a movie.
 * Uses session cache to avoid redundant API calls.
 */
function getMovieCertification($movieId, $context) {
    global $baseUrl;
    $cacheKey = 'cert_movie_' . $movieId;

    if (isset($_SESSION[$cacheKey])) {
        return $_SESSION[$cacheKey];
    }

    $data = tmdbFetch("$baseUrl/movie/$movieId/release_dates", $context);
    $cert = '';

    if (isset($data['results']) && is_array($data['results'])) {
        foreach ($data['results'] as $country) {
            if ($country['iso_3166_1'] === 'US') {
                foreach ($country['release_dates'] as $release) {
                    if (!empty($release['certification'])) {
                        $cert = $release['certification'];
                        break 2;
                    }
                }
            }
        }
    }

    $_SESSION[$cacheKey] = $cert;
    return $cert;
}

/**
 * Get the US content rating for a TV show.
 * Uses session cache to avoid redundant API calls.
 */
function getTvCertification($tvId, $context) {
    global $baseUrl;
    $cacheKey = 'cert_tv_' . $tvId;

    if (isset($_SESSION[$cacheKey])) {
        return $_SESSION[$cacheKey];
    }

    $data = tmdbFetch("$baseUrl/tv/$tvId/content_ratings", $context);
    $cert = '';

    if (isset($data['results']) && is_array($data['results'])) {
        foreach ($data['results'] as $rating) {
            if ($rating['iso_3166_1'] === 'US') {
                $cert = $rating['rating'];
                break;
            }
        }
    }

    $_SESSION[$cacheKey] = $cert;
    return $cert;
}

/**
 * Filter a list of TMDB results by certification.
 * Whitelisted items are always kept.
 *
 * @param array  $items     Array of TMDB result items
 * @param string $mediaType 'movie', 'tv', or 'mixed' (for search results)
 * @param resource $context  Stream context for API calls
 * @return array  Filtered items
 */
function filterResults($items, $mediaType, $context) {
    if (!CONTENT_FILTER_ENABLED || !is_array($items)) {
        return $items;
    }

    $filtered = [];
    foreach ($items as $item) {
        $id = isset($item['id']) ? (int)$item['id'] : 0;
        if ($id <= 0) continue;

        // Determine type for mixed results (search)
        $type = $mediaType;
        if ($mediaType === 'mixed') {
            $type = isset($item['media_type']) ? $item['media_type'] : 'movie';
        }

        // Always keep whitelisted titles
        if (isWhitelisted($id, $type)) {
            $filtered[] = $item;
            continue;
        }

        // Skip explicitly adult content
        if (!empty($item['adult'])) {
            continue;
        }

        // Look up certification
        if ($type === 'tv') {
            $cert = getTvCertification($id, $context);
        } else {
            $cert = getMovieCertification($id, $context);
        }

        // Keep only if certification is allowed
        if (isCertAllowed($cert, $type)) {
            $filtered[] = $item;
        }
    }

    return $filtered;
}

/**
 * Filter the "similar" sub-results embedded in a detail response.
 */
function filterDetailResponse($data, $mediaType, $context) {
    if (!CONTENT_FILTER_ENABLED) {
        return $data;
    }
    if (isset($data['similar']['results'])) {
        $data['similar']['results'] = filterResults(
            $data['similar']['results'],
            $mediaType,
            $context
        );
    }
    return $data;
}

// ─── Route Handling ──────────────────────────────────────────────────────────

switch ($action) {
    // === MOVIE ENDPOINTS ===
    case 'trending':
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $data = tmdbFetch("$baseUrl/trending/movie/week?language=en-US&page=$page", $context);
        if (isset($data['results'])) {
            $data['results'] = filterResults($data['results'], 'movie', $context);
        }
        echo json_encode($data);
        break;

    case 'now_playing':
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $data = tmdbFetch("$baseUrl/movie/now_playing?language=en-US&page=$page", $context);
        if (isset($data['results'])) {
            $data['results'] = filterResults($data['results'], 'movie', $context);
        }
        echo json_encode($data);
        break;

    case 'top_rated':
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $data = tmdbFetch("$baseUrl/movie/top_rated?language=en-US&page=$page", $context);
        if (isset($data['results'])) {
            $data['results'] = filterResults($data['results'], 'movie', $context);
        }
        echo json_encode($data);
        break;

    case 'popular':
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $data = tmdbFetch("$baseUrl/movie/popular?language=en-US&page=$page", $context);
        if (isset($data['results'])) {
            $data['results'] = filterResults($data['results'], 'movie', $context);
        }
        echo json_encode($data);
        break;

    case 'movie':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            echo json_encode(['error' => 'Invalid movie ID']);
            break;
        }
        $data = tmdbFetch("$baseUrl/movie/$id?language=en-US&append_to_response=credits,videos,similar", $context);
        $data = filterDetailResponse($data, 'movie', $context);
        echo json_encode($data);
        break;

    case 'genres':
        $data = tmdbFetch("$baseUrl/genre/movie/list?language=en-US", $context);
        echo json_encode($data);
        break;

    case 'discover':
        $genre = isset($_GET['genre']) ? intval($_GET['genre']) : '';
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'popularity.desc';
        $url = "$baseUrl/discover/movie?language=en-US&page=$page&sort_by=$sort";
        $url .= "&certification_country=US&certification.lte=PG-13&include_adult=false";
        if ($genre) {
            $url .= "&with_genres=$genre";
        }
        $data = tmdbFetch($url, $context);
        echo json_encode($data);
        break;

    // === TV SERIES ENDPOINTS ===
    case 'trending_tv':
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $data = tmdbFetch("$baseUrl/trending/tv/week?language=en-US&page=$page", $context);
        if (isset($data['results'])) {
            $data['results'] = filterResults($data['results'], 'tv', $context);
        }
        echo json_encode($data);
        break;

    case 'popular_tv':
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $data = tmdbFetch("$baseUrl/tv/popular?language=en-US&page=$page", $context);
        if (isset($data['results'])) {
            $data['results'] = filterResults($data['results'], 'tv', $context);
        }
        echo json_encode($data);
        break;

    case 'top_rated_tv':
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $data = tmdbFetch("$baseUrl/tv/top_rated?language=en-US&page=$page", $context);
        if (isset($data['results'])) {
            $data['results'] = filterResults($data['results'], 'tv', $context);
        }
        echo json_encode($data);
        break;

    case 'tv':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            echo json_encode(['error' => 'Invalid TV show ID']);
            break;
        }
        $data = tmdbFetch("$baseUrl/tv/$id?language=en-US&append_to_response=credits,videos,similar", $context);
        $data = filterDetailResponse($data, 'tv', $context);
        echo json_encode($data);
        break;

    case 'tv_genres':
        $data = tmdbFetch("$baseUrl/genre/tv/list?language=en-US", $context);
        echo json_encode($data);
        break;

    case 'discover_tv':
        $genre = isset($_GET['genre']) ? intval($_GET['genre']) : '';
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'popularity.desc';
        $url = "$baseUrl/discover/tv?language=en-US&page=$page&sort_by=$sort&include_adult=false";
        if ($genre) {
            $url .= "&with_genres=$genre";
        }
        $data = tmdbFetch($url, $context);
        if (isset($data['results'])) {
            $data['results'] = filterResults($data['results'], 'tv', $context);
        }
        echo json_encode($data);
        break;

    case 'tv_season':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $season = isset($_GET['season']) ? intval($_GET['season']) : 1;
        if ($id <= 0) {
            echo json_encode(['error' => 'Invalid TV show ID']);
            break;
        }
        $data = tmdbFetch("$baseUrl/tv/$id/season/$season?language=en-US", $context);
        echo json_encode($data);
        break;

    // === MULTI SEARCH (movies + TV) ===
    case 'search':
        $query = isset($_GET['q']) ? urlencode($_GET['q']) : '';
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        if (empty($query)) {
            echo json_encode(['results' => [], 'total_results' => 0]);
            break;
        }
        $data = tmdbFetch("$baseUrl/search/multi?query=$query&language=en-US&page=$page&include_adult=false", $context);
        // Filter to only movies and tv shows (exclude people etc.)
        if (isset($data['results'])) {
            $data['results'] = array_values(array_filter($data['results'], function($item) {
                return in_array($item['media_type'] ?? '', ['movie', 'tv']);
            }));
            $data['results'] = filterResults($data['results'], 'mixed', $context);
        }
        echo json_encode($data);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action. Use: trending, now_playing, top_rated, popular, search, movie, genres, discover, trending_tv, popular_tv, top_rated_tv, tv, tv_genres, discover_tv, tv_season']);
        break;
}
?>
