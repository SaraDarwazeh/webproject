<?php
// Quick test script to verify jellyfin API works
$_GET['action'] = 'check';
$_GET['tmdb_id'] = '807';

require_once __DIR__ . '/app/api/jellyfin.php';
