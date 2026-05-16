<?php
/**
 * Comments API Endpoint
 * GET: fetch comments for content
 * POST: add a comment (requires access)
 * DELETE: remove a comment (own or admin)
 */

require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/controllers/comment_controller.php';

header('Content-Type: application/json');

$commentCtrl = new CommentController();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $tmdbId = intval($_GET['tmdb_id'] ?? 0);
        $mediaType = $_GET['media_type'] ?? 'movie';
        $season = isset($_GET['season']) && $_GET['season'] !== '' ? intval($_GET['season']) : null;
        $episode = isset($_GET['episode']) && $_GET['episode'] !== '' ? intval($_GET['episode']) : null;

        if ($tmdbId <= 0) {
            echo json_encode(['comments' => [], 'error' => 'Invalid TMDB ID']);
            break;
        }

        $comments = $commentCtrl->getComments($tmdbId, $mediaType, $season, $episode);
        echo json_encode(['comments' => $comments]);
        break;

    case 'POST':
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Login required']);
            break;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $tmdbId = intval($input['tmdb_id'] ?? 0);
        $mediaType = $input['media_type'] ?? 'movie';
        $content = $input['content'] ?? '';
        $season = isset($input['season']) ? intval($input['season']) : null;
        $episode = isset($input['episode']) ? intval($input['episode']) : null;

        if ($tmdbId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid TMDB ID']);
            break;
        }

        $result = $commentCtrl->addComment($_SESSION['user_id'], $tmdbId, $mediaType, $content, $season, $episode);
        echo json_encode($result);
        break;

    case 'DELETE':
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Login required']);
            break;
        }

        $commentId = intval($_GET['id'] ?? 0);
        if ($commentId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid comment ID']);
            break;
        }

        $isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
        $result = $commentCtrl->deleteComment($commentId, $_SESSION['user_id'], $isAdmin);
        echo json_encode($result);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>
