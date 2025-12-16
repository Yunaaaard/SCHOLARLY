<?php

// Router for PHP built-in server
// Serves static files and routes everything else to Laravel

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$filePath = __DIR__ . $uri;

// Serve static files directly (CSS, JS, images, etc.)
if ($uri !== '/' && file_exists($filePath) && is_file($filePath)) {
    // Set correct content types
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject',
    ];
    
    if (isset($mimeTypes[$extension])) {
        header('Content-Type: ' . $mimeTypes[$extension]);
    }
    
    header('Cache-Control: public, max-age=3600');
    return false; // Serve the file
}

// Route everything else to Laravel
require_once __DIR__ . '/index.php';
