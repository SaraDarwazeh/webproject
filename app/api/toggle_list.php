<?php
/**
 * Toggle Watchlist API Endpoint
 */

require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/controllers/list_controller.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Login required']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$tmdbId = isset($input['tmdb_id']) ? intval($input['tmdb_id']) : 0;
$mediaType = isset($input['media_type']) ? $input['media_type'] : 'movie';

if ($tmdbId <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Valid TMDB ID is required']);
    exit;
}

$listController = new ListController();
$result = $listController->toggleList($_SESSION['user_id'], $tmdbId, $mediaType);

echo json_encode([
    'status' => 'success',
    'inList' => $result['inList'],
    'message' => $result['message']
]);
?>
