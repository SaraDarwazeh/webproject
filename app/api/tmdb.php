<?php
/**
 * TMDB API Proxy
 * Server-side proxy to keep API keys hidden from the browser
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../app/config/config.php';

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

function tmdbFetch($url, $context) {
    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        return ['error' => 'Failed to fetch from TMDB'];
    }
    return json_decode($response, true);
}

switch ($action) {
    // === MOVIE ENDPOINTS ===
    case 'trending':
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $data = tmdbFetch("$baseUrl/trending/movie/week?language=en-US&page=$page", $context);
        echo json_encode($data);
        break;

    case 'now_playing':
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $data = tmdbFetch("$baseUrl/movie/now_playing?language=en-US&page=$page", $context);
        echo json_encode($data);
        break;

    case 'top_rated':
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $data = tmdbFetch("$baseUrl/movie/top_rated?language=en-US&page=$page", $context);
        echo json_encode($data);
        break;

    case 'popular':
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $data = tmdbFetch("$baseUrl/movie/popular?language=en-US&page=$page", $context);
        echo json_encode($data);
        break;

    case 'movie':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            echo json_encode(['error' => 'Invalid movie ID']);
            break;
        }
        $data = tmdbFetch("$baseUrl/movie/$id?language=en-US&append_to_response=credits,videos,similar", $context);
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
        echo json_encode($data);
        break;

    case 'popular_tv':
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $data = tmdbFetch("$baseUrl/tv/popular?language=en-US&page=$page", $context);
        echo json_encode($data);
        break;

    case 'top_rated_tv':
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $data = tmdbFetch("$baseUrl/tv/top_rated?language=en-US&page=$page", $context);
        echo json_encode($data);
        break;

    case 'tv':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            echo json_encode(['error' => 'Invalid TV show ID']);
            break;
        }
        $data = tmdbFetch("$baseUrl/tv/$id?language=en-US&append_to_response=credits,videos,similar", $context);
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
        $url = "$baseUrl/discover/tv?language=en-US&page=$page&sort_by=$sort";
        if ($genre) {
            $url .= "&with_genres=$genre";
        }
        $data = tmdbFetch($url, $context);
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
        $data = tmdbFetch("$baseUrl/search/multi?query=$query&language=en-US&page=$page", $context);
        // Filter to only movies and tv shows (exclude people etc.)
        if (isset($data['results'])) {
            $data['results'] = array_values(array_filter($data['results'], function($item) {
                return in_array($item['media_type'] ?? '', ['movie', 'tv']);
            }));
        }
        echo json_encode($data);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action. Use: trending, now_playing, top_rated, popular, search, movie, genres, discover, trending_tv, popular_tv, top_rated_tv, tv, tv_genres, discover_tv, tv_season']);
        break;
}
?>
