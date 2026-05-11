<?php
/**
 * StreamHive Configuration
 * 
 * Copy this file to config.php and fill in your values:
 *   cp config.example.php config.php
 */

session_start();

// Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'streamhive');

// App
define('APP_NAME', 'StreamHive');
define('APP_URL', '/streamhive/public');
define('ASSETS_URL', '/streamhive/public/assets');

// TMDB API — Get yours at https://www.themoviedb.org/settings/api
define('TMDB_API_KEY', 'YOUR_TMDB_API_KEY');
define('TMDB_ACCESS_TOKEN', 'YOUR_TMDB_ACCESS_TOKEN');
define('TMDB_IMG_BASE', 'https://image.tmdb.org/t/p/');

// Gemini AI — Get yours at https://aistudio.google.com/apikey
define('GEMINI_API_KEY', 'YOUR_GEMINI_API_KEY');
?>
