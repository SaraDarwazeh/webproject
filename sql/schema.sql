-- StreamHive Database Schema
-- Drop existing tables if needed (for fresh setup)
DROP TABLE IF EXISTS ratings;
DROP TABLE IF EXISTS my_list;
DROP TABLE IF EXISTS movies;
DROP TABLE IF EXISTS users;

-- Users Table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    avatar_url VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_admin BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active'
);

-- Movies Table (stores TMDB movie/TV IDs for watchlist/ratings reference)
CREATE TABLE movies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tmdb_id INT NOT NULL,
    media_type VARCHAR(10) DEFAULT 'movie' COMMENT 'movie or tv',
    title VARCHAR(255) NOT NULL,
    genre VARCHAR(100) DEFAULT NULL,
    year INT DEFAULT NULL,
    duration INT DEFAULT NULL COMMENT 'in minutes',
    rating FLOAT DEFAULT NULL,
    description TEXT,
    poster_path VARCHAR(255) DEFAULT NULL,
    backdrop_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_tmdb_type (tmdb_id, media_type)
);

-- Watchlist Table
CREATE TABLE my_list (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    tmdb_id INT NOT NULL,
    media_type VARCHAR(10) DEFAULT 'movie' COMMENT 'movie or tv',
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_media (user_id, tmdb_id, media_type)
);

-- Ratings Table
CREATE TABLE ratings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    tmdb_id INT NOT NULL,
    media_type VARCHAR(10) DEFAULT 'movie' COMMENT 'movie or tv',
    rating INT NOT NULL COMMENT '1-5 stars',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_media_rating (user_id, tmdb_id, media_type)
);

-- Indexes
CREATE INDEX idx_my_list_user ON my_list(user_id);
CREATE INDEX idx_my_list_tmdb ON my_list(tmdb_id);
CREATE INDEX idx_ratings_user ON ratings(user_id);
CREATE INDEX idx_ratings_tmdb ON ratings(tmdb_id);
CREATE INDEX idx_movies_tmdb ON movies(tmdb_id);

-- Default admin user (password: admin123)
INSERT INTO users (username, email, password_hash, is_admin) VALUES
('admin', 'admin@streamhive.com', '$2y$10$JUO797LLVaDNpUJB28FyVuBUuSLL/JX9eQfn96vJD34Yv3rIG4Vay', TRUE);
