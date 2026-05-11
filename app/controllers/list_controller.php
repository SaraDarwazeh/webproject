<?php
/**
 * List Controller
 * Manages watchlist and ratings with real database operations
 */

require_once __DIR__ . '/../db/db.php';

class ListController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get user's watchlist (TMDB IDs)
     */
    public function getUserList($userId) {
        $results = $this->db->fetchAll(
            "SELECT tmdb_id, media_type FROM my_list WHERE user_id = ? ORDER BY added_at DESC",
            [$userId],
            'i'
        );
        return $results;
    }

    /**
     * Check if movie is in user's list
     */
    public function isInList($userId, $tmdbId, $mediaType = 'movie') {
        $result = $this->db->fetchOne(
            "SELECT id FROM my_list WHERE user_id = ? AND tmdb_id = ? AND media_type = ?",
            [$userId, $tmdbId, $mediaType],
            'iis'
        );
        return $result !== null;
    }

    /**
     * Toggle movie in watchlist (add or remove)
     * @return array ['inList' => bool, 'message' => string]
     */
    public function toggleList($userId, $tmdbId, $mediaType = 'movie') {
        if ($this->isInList($userId, $tmdbId, $mediaType)) {
            $this->db->execute(
                "DELETE FROM my_list WHERE user_id = ? AND tmdb_id = ? AND media_type = ?",
                [$userId, $tmdbId, $mediaType],
                'iis'
            );
            return ['inList' => false, 'message' => 'Removed from watchlist'];
        } else {
            $this->db->execute(
                "INSERT INTO my_list (user_id, tmdb_id, media_type) VALUES (?, ?, ?)",
                [$userId, $tmdbId, $mediaType],
                'iis'
            );
            return ['inList' => true, 'message' => 'Added to watchlist'];
        }
    }

    /**
     * Get watchlist count for user
     */
    public function getListCount($userId) {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM my_list WHERE user_id = ?",
            [$userId],
            'i'
        );
        return $result ? $result['count'] : 0;
    }

    /**
     * Rate a movie (insert or update)
     */
    public function rateMovie($userId, $tmdbId, $rating) {
        if ($rating < 1 || $rating > 5) {
            return ['success' => false, 'message' => 'Rating must be between 1 and 5'];
        }

        $existing = $this->db->fetchOne(
            "SELECT id FROM ratings WHERE user_id = ? AND tmdb_id = ?",
            [$userId, $tmdbId],
            'ii'
        );

        if ($existing) {
            $this->db->execute(
                "UPDATE ratings SET rating = ? WHERE user_id = ? AND tmdb_id = ?",
                [$rating, $userId, $tmdbId],
                'iii'
            );
        } else {
            $this->db->execute(
                "INSERT INTO ratings (user_id, tmdb_id, rating) VALUES (?, ?, ?)",
                [$userId, $tmdbId, $rating],
                'iii'
            );
        }

        return ['success' => true, 'message' => "Rated $rating stars"];
    }

    /**
     * Get user's rating for a specific movie
     */
    public function getUserRating($userId, $tmdbId) {
        $result = $this->db->fetchOne(
            "SELECT rating FROM ratings WHERE user_id = ? AND tmdb_id = ?",
            [$userId, $tmdbId],
            'ii'
        );
        return $result ? $result['rating'] : 0;
    }

    /**
     * Get rating stats for user
     */
    public function getRatingStats($userId) {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as count, COALESCE(AVG(rating), 0) as avg_rating FROM ratings WHERE user_id = ?",
            [$userId],
            'i'
        );
        return $result;
    }
}
?>
