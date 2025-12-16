<?php

// Router for PHP built-in server
// Serves static files and routes everything else to Laravel

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve static files directly (CSS, JS, images, etc.)
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    // Set correct content type for CSS files
    if (pathinfo($uri, PATHINFO_EXTENSION) === 'css') {
        header('Content-Type: text/css');
    }
    return false; // Serve the file
}

// Route everything else to Laravel
require_once __DIR__ . '/index.php';
