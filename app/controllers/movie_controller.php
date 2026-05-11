<?php
/**
 * Movie Controller
 * TODO: Implement movie management logic
 */

class MovieController {
    // TODO: Implement get all movies
    public function getAllMovies() {
        // Connect to database
        // Fetch all movies
        // Return as array
    }

    // TODO: Implement get movie by ID
    public function getMovieById($id) {
        // Connect to database
        // Query movie by ID
        // Return movie data
    }

    // TODO: Implement get movies by genre
    public function getByGenre($genre) {
        // Connect to database
        // Query by genre
        // Return results
    }

    // TODO: Implement search movies
    public function search($query) {
        // Connect to database
        // Search in title, description, genre
        // Return results
    }

    // TODO: Implement add movie
    public function add($title, $genre, $year, $duration, $rating, $description, $poster) {
        // Validate input
        // Connect to database
        // Insert movie
    }

    // TODO: Implement update movie
    public function update($id, $data) {
        // Validate input
        // Connect to database
        // Update movie
    }

    // TODO: Implement delete movie
    public function delete($id) {
        // Connect to database
        // Delete movie
    }
}
?>
