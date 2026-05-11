<?php
/**
 * Search API Endpoint
 * Returns user's watchlist status for given TMDB IDs
 */

require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/controllers/list_controller.php';

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'check_list':
        // Check if movies are in user's watchlist
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['logged_in' => false, 'list' => []]);
            break;
        }
        $listController = new ListController();
        $list = $listController->getUserList($_SESSION['user_id']);
        echo json_encode(['logged_in' => true, 'list' => $list]);
        break;

    case 'get_rating':
        // Get user's rating for a specific movie
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['logged_in' => false, 'rating' => 0]);
            break;
        }
        $tmdbId = isset($_GET['tmdb_id']) ? intval($_GET['tmdb_id']) : 0;
        $listController = new ListController();
        $rating = $listController->getUserRating($_SESSION['user_id'], $tmdbId);
        echo json_encode(['logged_in' => true, 'rating' => $rating]);
        break;

    default:
        echo json_encode(['error' => 'Use action: check_list, get_rating']);
        break;
}
?>
