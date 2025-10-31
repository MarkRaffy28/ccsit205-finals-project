<?php
// router.php

// If the requested file exists, serve it
if (file_exists(__DIR__ . $_SERVER["REQUEST_URI"])) {
    return false;
} else {
    // Otherwise show custom 404 page
    http_response_code(404);
    include __DIR__ . '/404.php';
}
?>