<?php
/**
 * Toggle My List API Endpoint
 * TODO: Implement database queries when ready
 */

header('Content-Type: application/json');

// TODO: Connect to database and update user's list

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed. Use POST.'
    ]);
    exit;
}

$movieId = isset($_POST['movieId']) ? intval($_POST['movieId']) : null;

if (!$movieId) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Movie ID is required'
    ]);
    exit;
}

// Placeholder response
$response = [
    'status' => 'success',
    'movieId' => $movieId,
    'inList' => true,
    'message' => 'Movie list status updated (placeholder)'
];

echo json_encode($response);
?>
