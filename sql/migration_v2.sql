-- StreamHive Migration v2: Purchasing, Subscriptions & Comments
-- Run this AFTER the original schema.sql has been applied.
-- This migration is non-destructive — it only ADDs columns and tables.

-- Add points balance to users (plain ALTER — safe to skip if already run)
ALTER TABLE users ADD COLUMN points_balance INT DEFAULT 0;

-- Subscriptions Table
CREATE TABLE IF NOT EXISTS subscriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    plan_type ENUM('day', 'week', 'month', 'year') NOT NULL,
    price_paid DECIMAL(10, 2) NOT NULL,
    starts_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    auto_renew BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Purchases Table (individual movie/episode purchases)
CREATE TABLE IF NOT EXISTS purchases (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    tmdb_id INT NOT NULL,
    media_type VARCHAR(10) DEFAULT 'movie' COMMENT 'movie or tv',
    season_number INT DEFAULT NULL COMMENT 'NULL for movies',
    episode_number INT DEFAULT NULL COMMENT 'NULL for movies',
    points_spent INT NOT NULL,
    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Point Transactions Ledger
CREATE TABLE IF NOT EXISTS point_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    amount INT NOT NULL COMMENT 'Positive for credit, negative for debit',
    type ENUM('credit', 'debit') NOT NULL,
    description VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Comments Table
CREATE TABLE IF NOT EXISTS comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    tmdb_id INT NOT NULL,
    media_type VARCHAR(10) DEFAULT 'movie' COMMENT 'movie or tv',
    season_number INT DEFAULT NULL COMMENT 'NULL for movie-level comments',
    episode_number INT DEFAULT NULL COMMENT 'NULL for movie-level comments',
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Indexes
CREATE INDEX idx_subscriptions_user ON subscriptions(user_id);
CREATE INDEX idx_subscriptions_expires ON subscriptions(expires_at);
CREATE INDEX idx_purchases_user ON purchases(user_id);
CREATE INDEX idx_purchases_tmdb ON purchases(tmdb_id);
CREATE INDEX idx_purchases_user_tmdb ON purchases(user_id, tmdb_id);
CREATE INDEX idx_point_transactions_user ON point_transactions(user_id);
CREATE INDEX idx_comments_tmdb ON comments(tmdb_id);
CREATE INDEX idx_comments_user ON comments(user_id);
