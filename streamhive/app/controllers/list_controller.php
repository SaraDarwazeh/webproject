<?php
/**
 * My List Controller
 * TODO: Implement watchlist management logic
 */

class ListController {
    // TODO: Implement get user's list
    public function getUserList($userId) {
        // Connect to database
        // Query my_list table for user
        // Return array of movie IDs
    }

    // TODO: Implement add to list
    public function addToList($userId, $movieId) {
        // Validate user and movie exist
        // Connect to database
        // Insert into my_list table
    }

    // TODO: Implement remove from list
    public function removeFromList($userId, $movieId) {
        // Connect to database
        // Delete from my_list table
    }

    // TODO: Implement clear list
    public function clearList($userId) {
        // Connect to database
        // Delete all user's list items
    }

    // TODO: Implement rate movie
    public function rateMovie($userId, $movieId, $rating) {
        // Validate rating (1-5)
        // Connect to database
        // Insert/update rating in ratings table
    }

    // TODO: Implement get user rating
    public function getUserRating($userId, $movieId) {
        // Connect to database
        // Query ratings table
        // Return rating or null
    }
}
?>
