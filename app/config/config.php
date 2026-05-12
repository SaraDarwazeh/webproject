<?php
/**
 * StreamHive Configuration
 */

session_start();

// Database
define('DB_HOST', 'streamhive-web-streamhive.h.aivencloud.com');
define('DB_USER', 'avnadmin');
define('DB_PASS', 'REDACTED_DB_PASSWORD'); // You must paste your real password here!
define('DB_NAME', 'defaultdb');
define('DB_PORT', 25234);

// App
define('APP_NAME', 'StreamHive');
define('APP_URL', '/streamhive/public');
define('ASSETS_URL', '/streamhive/public/assets');

// TMDB API
define('TMDB_API_KEY', 'REDACTED_TMDB_API_KEY');
define('TMDB_ACCESS_TOKEN', 'REDACTED_TMDB_ACCESS_TOKEN');
define('TMDB_IMG_BASE', 'https://image.tmdb.org/t/p/');

// Gemini AI
define('GEMINI_API_KEY', 'REDACTED_GEMINI_API_KEY');
?>