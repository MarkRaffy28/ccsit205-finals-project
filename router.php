<?php
// router.php

// Get the requested path only (remove query string)
$request = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$file = __DIR__ . $request;

// If the requested file exists, serve it
if ($request !== '/' && file_exists($file)) {
    return false; // Let PHP’s built-in server handle it (like normal)
}

// Otherwise, show custom 404
http_response_code(404);
include __DIR__ . '/404.php';
?>