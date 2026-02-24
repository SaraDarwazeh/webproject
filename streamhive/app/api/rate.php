<?php
/**
 * Movie Rating API Endpoint
 * TODO: Implement database queries when ready
 */

header('Content-Type: application/json');

// TODO: Connect to database and save rating

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed. Use POST.'
    ]);
    exit;
}

$movieId = isset($_POST['movieId']) ? intval($_POST['movieId']) : null;
$rating = isset($_POST['rating']) ? floatval($_POST['rating']) : null;

if (!$movieId || !$rating) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Movie ID and rating are required'
    ]);
    exit;
}

if ($rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Rating must be between 1 and 5'
    ]);
    exit;
}

// Placeholder response
$response = [
    'status' => 'success',
    'movieId' => $movieId,
    'rating' => $rating,
    'message' => 'Rating saved (placeholder)'
];

echo json_encode($response);
?>
