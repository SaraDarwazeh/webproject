<?php
/**
 * Comment Controller
 * Manages comments on movies, shows, and episodes.
 * Only users who have purchased the content or have an active subscription can comment.
 */

require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/purchase_controller.php';

class CommentController {
    private $db;
    private $purchaseCtrl;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->purchaseCtrl = new PurchaseController();
    }

    /**
     * Add a comment (enforces access check)
     * @return array ['success' => bool, 'message' => string]
     */
    public function addComment($userId, $tmdbId, $mediaType, $content, $seasonNumber = null, $episodeNumber = null) {
        $content = trim($content);
        if (empty($content)) {
            return ['success' => false, 'message' => 'Comment cannot be empty'];
        }

        if (strlen($content) > 2000) {
            return ['success' => false, 'message' => 'Comment is too long (max 2000 characters)'];
        }

        // Access check: must own content or be subscribed or be admin
        if (!$this->purchaseCtrl->hasAccess($userId, $tmdbId, $mediaType, $seasonNumber, $episodeNumber)) {
            return ['success' => false, 'message' => 'You must purchase this content or subscribe to comment'];
        }

        $this->db->execute(
            "INSERT INTO comments (user_id, tmdb_id, media_type, season_number, episode_number, content) VALUES (?, ?, ?, ?, ?, ?)",
            [$userId, $tmdbId, $mediaType, $seasonNumber, $episodeNumber, $content],
            'iisiis'
        );

        return ['success' => true, 'message' => 'Comment posted'];
    }

    /**
     * Get all comments for a specific piece of content.
     * Public — anyone can read comments.
     */
    public function getComments($tmdbId, $mediaType = 'movie', $seasonNumber = null, $episodeNumber = null) {
        $sql = "SELECT c.*, u.username, u.is_admin
                FROM comments c
                JOIN users u ON c.user_id = u.id
                WHERE c.tmdb_id = ? AND c.media_type = ?";
        $params = [$tmdbId, $mediaType];
        $types = 'is';

        if ($seasonNumber !== null && $episodeNumber !== null) {
            $sql .= " AND c.season_number = ? AND c.episode_number = ?";
            $params[] = $seasonNumber;
            $params[] = $episodeNumber;
            $types .= 'ii';
        } else {
            $sql .= " AND c.season_number IS NULL AND c.episode_number IS NULL";
        }

        $sql .= " ORDER BY c.created_at DESC";
        return $this->db->fetchAll($sql, $params, $types);
    }

    /**
     * Delete a comment.
     * Users can delete their own comments. Admins can delete any comment.
     */
    public function deleteComment($commentId, $userId, $isAdmin = false) {
        $comment = $this->db->fetchOne(
            "SELECT * FROM comments WHERE id = ?",
            [$commentId], 'i'
        );

        if (!$comment) {
            return ['success' => false, 'message' => 'Comment not found'];
        }

        if ($comment['user_id'] != $userId && !$isAdmin) {
            return ['success' => false, 'message' => 'You can only delete your own comments'];
        }

        $this->db->execute("DELETE FROM comments WHERE id = ?", [$commentId], 'i');
        return ['success' => true, 'message' => 'Comment deleted'];
    }

    /**
     * Get comment count for content
     */
    public function getCommentCount($tmdbId, $mediaType = 'movie') {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as c FROM comments WHERE tmdb_id = ? AND media_type = ?",
            [$tmdbId, $mediaType], 'is'
        );
        return $result ? (int)$result['c'] : 0;
    }
}
?>
