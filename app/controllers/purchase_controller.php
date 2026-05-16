<?php
/**
 * Purchase Controller
 * Manages points, purchases, subscriptions, and access control
 */

require_once __DIR__ . '/../db/db.php';

class PurchaseController {
    private $db;

    /** Cost constants */
    const MOVIE_COST = 20;
    const EPISODE_COST = 5;

    /** Subscription pricing (USD) */
    const PLAN_PRICES = [
        'day'   => 1.00,
        'week'  => 5.00,
        'month' => 10.00,
        'year'  => 70.00
    ];

    /** Points per dollar */
    const POINTS_PER_DOLLAR = 10;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get user's current points balance
     */
    public function getPointsBalance($userId) {
        $user = $this->db->fetchOne(
            "SELECT points_balance FROM users WHERE id = ?",
            [$userId], 'i'
        );
        return $user ? (int)$user['points_balance'] : 0;
    }

    /**
     * Buy points with fake credit card.
     * Card fields must all be non-empty (validation only, no real charge).
     * @return array ['success' => bool, 'message' => string, 'balance' => int]
     */
    public function buyPoints($userId, $dollarAmount, $cardData) {
        // Validate card fields
        $required = ['card_number', 'card_name', 'card_expiry', 'card_cvv'];
        foreach ($required as $field) {
            if (empty($cardData[$field])) {
                return ['success' => false, 'message' => "All card fields are required"];
            }
        }

        // Basic format validation
        $cardNum = preg_replace('/\s+/', '', $cardData['card_number']);
        if (!preg_match('/^\d{16}$/', $cardNum)) {
            return ['success' => false, 'message' => 'Card number must be 16 digits'];
        }
        if (!preg_match('/^\d{2}\/\d{2}$/', $cardData['card_expiry'])) {
            return ['success' => false, 'message' => 'Expiry must be MM/YY format'];
        }
        if (!preg_match('/^\d{3,4}$/', $cardData['card_cvv'])) {
            return ['success' => false, 'message' => 'CVV must be 3 or 4 digits'];
        }

        $dollarAmount = (float)$dollarAmount;
        if ($dollarAmount <= 0) {
            return ['success' => false, 'message' => 'Invalid amount'];
        }

        $points = (int)($dollarAmount * self::POINTS_PER_DOLLAR);

        // Credit points
        $this->db->execute(
            "UPDATE users SET points_balance = points_balance + ? WHERE id = ?",
            [$points, $userId], 'ii'
        );

        // Log transaction
        $this->db->execute(
            "INSERT INTO point_transactions (user_id, amount, type, description) VALUES (?, ?, 'credit', ?)",
            [$userId, $points, "Purchased {$points} points (\${$dollarAmount})"],
            'iis'
        );

        $newBalance = $this->getPointsBalance($userId);
        return ['success' => true, 'message' => "{$points} points added!", 'balance' => $newBalance];
    }

    /**
     * Purchase a movie (20 points)
     */
    public function purchaseMovie($userId, $tmdbId, $mediaType = 'movie') {
        // Check if already purchased
        if ($this->hasPurchased($userId, $tmdbId, $mediaType)) {
            return ['success' => false, 'message' => 'You already own this content'];
        }

        $balance = $this->getPointsBalance($userId);
        if ($balance < self::MOVIE_COST) {
            return ['success' => false, 'message' => 'Not enough points. You need ' . self::MOVIE_COST . ' points.', 'need_points' => true];
        }

        // Deduct points
        $this->db->execute(
            "UPDATE users SET points_balance = points_balance - ? WHERE id = ?",
            [self::MOVIE_COST, $userId], 'ii'
        );

        // Record purchase
        $this->db->execute(
            "INSERT INTO purchases (user_id, tmdb_id, media_type, points_spent) VALUES (?, ?, ?, ?)",
            [$userId, $tmdbId, $mediaType, self::MOVIE_COST],
            'iisi'
        );

        // Log transaction
        $this->db->execute(
            "INSERT INTO point_transactions (user_id, amount, type, description) VALUES (?, ?, 'debit', ?)",
            [$userId, -self::MOVIE_COST, "Purchased {$mediaType} (TMDB #{$tmdbId})"],
            'iis'
        );

        $newBalance = $this->getPointsBalance($userId);
        return ['success' => true, 'message' => 'Content purchased!', 'balance' => $newBalance];
    }

    /**
     * Purchase a single episode (5 points)
     */
    public function purchaseEpisode($userId, $tmdbId, $seasonNumber, $episodeNumber) {
        // Check if already purchased
        if ($this->hasEpisodePurchased($userId, $tmdbId, $seasonNumber, $episodeNumber)) {
            return ['success' => false, 'message' => 'You already own this episode'];
        }

        $balance = $this->getPointsBalance($userId);
        if ($balance < self::EPISODE_COST) {
            return ['success' => false, 'message' => 'Not enough points. You need ' . self::EPISODE_COST . ' points.', 'need_points' => true];
        }

        // Deduct points
        $this->db->execute(
            "UPDATE users SET points_balance = points_balance - ? WHERE id = ?",
            [self::EPISODE_COST, $userId], 'ii'
        );

        // Record purchase
        $this->db->execute(
            "INSERT INTO purchases (user_id, tmdb_id, media_type, season_number, episode_number, points_spent) VALUES (?, ?, 'tv', ?, ?, ?)",
            [$userId, $tmdbId, $seasonNumber, $episodeNumber, self::EPISODE_COST],
            'iiiii'
        );

        // Log transaction
        $this->db->execute(
            "INSERT INTO point_transactions (user_id, amount, type, description) VALUES (?, ?, 'debit', ?)",
            [$userId, -self::EPISODE_COST, "Purchased S{$seasonNumber}E{$episodeNumber} (TMDB #{$tmdbId})"],
            'iis'
        );

        $newBalance = $this->getPointsBalance($userId);
        return ['success' => true, 'message' => 'Episode purchased!', 'balance' => $newBalance];
    }

    /**
     * Subscribe to a plan
     */
    public function subscribe($userId, $planType, $cardData) {
        if (!isset(self::PLAN_PRICES[$planType])) {
            return ['success' => false, 'message' => 'Invalid plan type'];
        }

        // Validate card fields
        $required = ['card_number', 'card_name', 'card_expiry', 'card_cvv'];
        foreach ($required as $field) {
            if (empty($cardData[$field])) {
                return ['success' => false, 'message' => 'All card fields are required'];
            }
        }

        $cardNum = preg_replace('/\s+/', '', $cardData['card_number']);
        if (!preg_match('/^\d{16}$/', $cardNum)) {
            return ['success' => false, 'message' => 'Card number must be 16 digits'];
        }
        if (!preg_match('/^\d{2}\/\d{2}$/', $cardData['card_expiry'])) {
            return ['success' => false, 'message' => 'Expiry must be MM/YY format'];
        }
        if (!preg_match('/^\d{3,4}$/', $cardData['card_cvv'])) {
            return ['success' => false, 'message' => 'CVV must be 3 or 4 digits'];
        }

        $price = self::PLAN_PRICES[$planType];
        $now = date('Y-m-d H:i:s');

        // Calculate expiry from now (or from current sub's expiry if extending)
        $startFrom = $now;
        $activeSub = $this->getActiveSubscription($userId);
        if ($activeSub) {
            $startFrom = $activeSub['expires_at'];
        }

        $expiresAt = $this->calculateExpiry($startFrom, $planType);

        $this->db->execute(
            "INSERT INTO subscriptions (user_id, plan_type, price_paid, starts_at, expires_at, auto_renew) VALUES (?, ?, ?, ?, ?, 1)",
            [$userId, $planType, $price, $now, $expiresAt],
            'isdss'
        );

        // Log transaction
        $this->db->execute(
            "INSERT INTO point_transactions (user_id, amount, type, description) VALUES (?, 0, 'credit', ?)",
            [$userId, "Subscribed to {$planType} plan (\${$price})"],
            'is'
        );

        return ['success' => true, 'message' => ucfirst($planType) . ' subscription activated!', 'expires_at' => $expiresAt];
    }

    /**
     * Get active subscription for user (non-expired)
     */
    public function getActiveSubscription($userId) {
        return $this->db->fetchOne(
            "SELECT * FROM subscriptions WHERE user_id = ? AND expires_at > NOW() ORDER BY expires_at DESC LIMIT 1",
            [$userId], 'i'
        );
    }

    /**
     * Check if user has active subscription
     */
    public function isSubscribed($userId) {
        return $this->getActiveSubscription($userId) !== null;
    }

    /**
     * Check if user has purchased a specific movie/show
     */
    public function hasPurchased($userId, $tmdbId, $mediaType = 'movie') {
        $result = $this->db->fetchOne(
            "SELECT id FROM purchases WHERE user_id = ? AND tmdb_id = ? AND media_type = ? AND season_number IS NULL",
            [$userId, $tmdbId, $mediaType], 'iis'
        );
        return $result !== null;
    }

    /**
     * Check if user has purchased a specific episode
     */
    public function hasEpisodePurchased($userId, $tmdbId, $seasonNumber, $episodeNumber) {
        $result = $this->db->fetchOne(
            "SELECT id FROM purchases WHERE user_id = ? AND tmdb_id = ? AND season_number = ? AND episode_number = ?",
            [$userId, $tmdbId, $seasonNumber, $episodeNumber], 'iiii'
        );
        return $result !== null;
    }

    /**
     * Check if user has access to content (purchased OR subscribed OR admin)
     */
    public function hasAccess($userId, $tmdbId, $mediaType = 'movie', $seasonNumber = null, $episodeNumber = null) {
        // Admins always have access
        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
            return true;
        }

        // Active subscription grants access to everything
        if ($this->isSubscribed($userId)) {
            return true;
        }

        // Check individual purchase
        if ($mediaType === 'movie' || ($seasonNumber === null && $episodeNumber === null)) {
            return $this->hasPurchased($userId, $tmdbId, $mediaType);
        }

        // For episodes: check if they purchased this specific episode
        return $this->hasEpisodePurchased($userId, $tmdbId, $seasonNumber, $episodeNumber);
    }

    /**
     * Get all purchased episode numbers for a show+season
     */
    public function getPurchasedEpisodes($userId, $tmdbId, $seasonNumber) {
        $results = $this->db->fetchAll(
            "SELECT episode_number FROM purchases WHERE user_id = ? AND tmdb_id = ? AND season_number = ?",
            [$userId, $tmdbId, $seasonNumber], 'iii'
        );
        return array_column($results, 'episode_number');
    }

    /**
     * Get purchase history for user
     */
    public function getPurchaseHistory($userId, $limit = 20) {
        return $this->db->fetchAll(
            "SELECT * FROM purchases WHERE user_id = ? ORDER BY purchased_at DESC LIMIT ?",
            [$userId, $limit], 'ii'
        );
    }

    /**
     * Get point transaction history for user
     */
    public function getTransactionHistory($userId, $limit = 20) {
        return $this->db->fetchAll(
            "SELECT * FROM point_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit], 'ii'
        );
    }

    /**
     * Get all subscriptions for user (including expired)
     */
    public function getSubscriptionHistory($userId) {
        return $this->db->fetchAll(
            "SELECT * FROM subscriptions WHERE user_id = ? ORDER BY created_at DESC",
            [$userId], 'i'
        );
    }

    /**
     * Calculate subscription expiry date
     */
    private function calculateExpiry($fromDate, $planType) {
        $date = new DateTime($fromDate);
        switch ($planType) {
            case 'day':   $date->modify('+1 day'); break;
            case 'week':  $date->modify('+1 week'); break;
            case 'month': $date->modify('+1 month'); break;
            case 'year':  $date->modify('+1 year'); break;
        }
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Get platform-wide stats (for admin dashboard)
     */
    public function getAdminStats() {
        $revenue = $this->db->fetchOne("SELECT COALESCE(SUM(price_paid), 0) as total FROM subscriptions")['total'];
        $activeSubs = $this->db->fetchOne("SELECT COUNT(*) as c FROM subscriptions WHERE expires_at > NOW()")['c'];
        $totalComments = $this->db->fetchOne("SELECT COUNT(*) as c FROM comments")['c'];
        $totalPoints = $this->db->fetchOne("SELECT COALESCE(SUM(points_balance), 0) as total FROM users")['total'];

        return [
            'revenue' => $revenue,
            'active_subscriptions' => $activeSubs,
            'total_comments' => $totalComments,
            'points_in_circulation' => $totalPoints
        ];
    }
}
?>
