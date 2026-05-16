<?php
/**
 * Purchase & Subscription API Endpoint
 * Handles: buy_points, purchase_movie, purchase_episode, subscribe, check_access, get_balance
 */

require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/controllers/purchase_controller.php';

header('Content-Type: application/json');

$purchaseCtrl = new PurchaseController();

// GET actions
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = isset($_GET['action']) ? $_GET['action'] : '';

    switch ($action) {
        case 'check_access':
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['logged_in' => false, 'has_access' => false]);
                exit;
            }
            $tmdbId = intval($_GET['tmdb_id'] ?? 0);
            $mediaType = $_GET['media_type'] ?? 'movie';
            $season = isset($_GET['season']) ? intval($_GET['season']) : null;
            $episode = isset($_GET['episode']) ? intval($_GET['episode']) : null;

            $isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
            $isSubscribed = $purchaseCtrl->isSubscribed($_SESSION['user_id']);
            $hasPurchased = $purchaseCtrl->hasPurchased($_SESSION['user_id'], $tmdbId, $mediaType);
            $hasAccess = $purchaseCtrl->hasAccess($_SESSION['user_id'], $tmdbId, $mediaType, $season, $episode);
            $balance = $purchaseCtrl->getPointsBalance($_SESSION['user_id']);

            echo json_encode([
                'logged_in' => true,
                'has_access' => $hasAccess,
                'is_subscribed' => $isSubscribed,
                'is_purchased' => $hasPurchased,
                'is_admin' => $isAdmin,
                'balance' => $balance
            ]);
            exit;

        case 'get_balance':
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['logged_in' => false, 'balance' => 0]);
                exit;
            }
            $balance = $purchaseCtrl->getPointsBalance($_SESSION['user_id']);
            echo json_encode(['logged_in' => true, 'balance' => $balance]);
            exit;

        case 'get_purchased_episodes':
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['logged_in' => false, 'episodes' => []]);
                exit;
            }
            $tmdbId = intval($_GET['tmdb_id'] ?? 0);
            $season = intval($_GET['season'] ?? 0);
            $episodes = $purchaseCtrl->getPurchasedEpisodes($_SESSION['user_id'], $tmdbId, $season);
            $isSubscribed = $purchaseCtrl->isSubscribed($_SESSION['user_id']);
            $isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
            echo json_encode([
                'logged_in' => true,
                'episodes' => $episodes,
                'is_subscribed' => $isSubscribed,
                'is_admin' => $isAdmin
            ]);
            exit;

        case 'get_subscription':
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['logged_in' => false]);
                exit;
            }
            $sub = $purchaseCtrl->getActiveSubscription($_SESSION['user_id']);
            echo json_encode([
                'logged_in' => true,
                'subscription' => $sub
            ]);
            exit;

        default:
            echo json_encode(['error' => 'Unknown GET action']);
            exit;
    }
}

// POST actions
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
$action = $input['action'] ?? '';

switch ($action) {
    case 'buy_points':
        $dollarAmount = floatval($input['dollar_amount'] ?? 0);
        $cardData = [
            'card_number' => $input['card_number'] ?? '',
            'card_name'   => $input['card_name'] ?? '',
            'card_expiry' => $input['card_expiry'] ?? '',
            'card_cvv'    => $input['card_cvv'] ?? ''
        ];
        $result = $purchaseCtrl->buyPoints($_SESSION['user_id'], $dollarAmount, $cardData);
        echo json_encode($result);
        break;

    case 'purchase_movie':
        $tmdbId = intval($input['tmdb_id'] ?? 0);
        $mediaType = $input['media_type'] ?? 'movie';
        if ($tmdbId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid TMDB ID']);
            break;
        }
        $result = $purchaseCtrl->purchaseMovie($_SESSION['user_id'], $tmdbId, $mediaType);
        echo json_encode($result);
        break;

    case 'purchase_episode':
        $tmdbId = intval($input['tmdb_id'] ?? 0);
        $season = intval($input['season'] ?? 0);
        $episode = intval($input['episode'] ?? 0);
        if ($tmdbId <= 0 || $season <= 0 || $episode <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            break;
        }
        $result = $purchaseCtrl->purchaseEpisode($_SESSION['user_id'], $tmdbId, $season, $episode);
        echo json_encode($result);
        break;

    case 'subscribe':
        $planType = $input['plan_type'] ?? '';
        $cardData = [
            'card_number' => $input['card_number'] ?? '',
            'card_name'   => $input['card_name'] ?? '',
            'card_expiry' => $input['card_expiry'] ?? '',
            'card_cvv'    => $input['card_cvv'] ?? ''
        ];
        $result = $purchaseCtrl->subscribe($_SESSION['user_id'], $planType, $cardData);
        echo json_encode($result);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
        break;
}
?>
