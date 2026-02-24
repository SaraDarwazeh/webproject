-- StreamHive Database Schema
-- Academic Project

-- Create Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_admin BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active'
);

-- Create Movies Table
CREATE TABLE IF NOT EXISTS movies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    genre VARCHAR(50) NOT NULL,
    year INT NOT NULL,
    duration INT NOT NULL COMMENT 'in minutes',
    rating FLOAT NOT NULL,
    description TEXT,
    poster_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create My List Table (Watchlist)
CREATE TABLE IF NOT EXISTS my_list (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    movie_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_movie (user_id, movie_id)
);

-- Create Ratings Table
CREATE TABLE IF NOT EXISTS ratings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    movie_id INT NOT NULL,
    rating INT NOT NULL COMMENT '1-5 stars',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_movie_rating (user_id, movie_id)
);

-- Create Indexes for better performance
CREATE INDEX idx_movies_genre ON movies(genre);
CREATE INDEX idx_movies_year ON movies(year);
CREATE INDEX idx_my_list_user ON my_list(user_id);
CREATE INDEX idx_my_list_movie ON my_list(movie_id);
CREATE INDEX idx_ratings_user ON ratings(user_id);
CREATE INDEX idx_ratings_movie ON ratings(movie_id);

-- Sample Data (Optional)
INSERT INTO movies (title, genre, year, duration, rating, description, poster_url) VALUES
('Cyber Dawn', 'Sci-Fi', 2024, 125, 8.5, 'A futuristic thriller about AI and humanity.', '/streamhive/public/assets/img/posters/p1.jpg'),
('Ocean\'s Echo', 'Adventure', 2023, 138, 7.8, 'An epic adventure across the seven seas.', '/streamhive/public/assets/img/posters/p2.jpg'),
('Silent Shadows', 'Thriller', 2024, 115, 8.2, 'A psychological thriller that will keep you guessing.', '/streamhive/public/assets/img/posters/p3.jpg'),
('Aurora Rising', 'Drama', 2023, 145, 8.9, 'An inspiring story of hope and redemption.', '/streamhive/public/assets/img/posters/p4.jpg'),
('Neon City', 'Sci-Fi', 2024, 110, 7.5, 'A cyberpunk adventure in a neon-lit metropolis.', '/streamhive/public/assets/img/posters/p5.jpg'),
('Forgotten Kingdom', 'Fantasy', 2023, 150, 8.7, 'An epic fantasy saga of magic and destiny.', '/streamhive/public/assets/img/posters/placeholder.jpg');
