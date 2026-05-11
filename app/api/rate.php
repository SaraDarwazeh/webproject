<?php
/**
 * Movie Rating API Endpoint
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
$rating = isset($input['rating']) ? intval($input['rating']) : 0;
$mediaType = isset($input['media_type']) ? $input['media_type'] : 'movie';

if ($tmdbId <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Valid TMDB ID is required']);
    exit;
}

if ($rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Rating must be between 1 and 5']);
    exit;
}

try {
    $listController = new ListController();
    $result = $listController->rateMovie($_SESSION['user_id'], $tmdbId, $rating);

    echo json_encode([
        'status' => $result['success'] ? 'success' : 'error',
        'message' => $result['message']
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
