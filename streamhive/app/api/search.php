<?php
/**
 * Search API Endpoint
 * TODO: Implement database queries when ready
 */

header('Content-Type: application/json');

// TODO: Connect to database and search movies
// For now, this is a placeholder that accepts API calls

$query = isset($_GET['q']) ? $_GET['q'] : '';
$genre = isset($_GET['genre']) ? $_GET['genre'] : '';

// Placeholder response
$response = [
    'status' => 'success',
    'query' => $query,
    'genre' => $genre,
    'results' => [],
    'message' => 'Search functionality will be implemented with database integration'
];

echo json_encode($response);
?>
