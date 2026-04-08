<?php

if (php_sapi_name() === 'cli-server') {
    $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    // Serve real static files as-is
    if ($url !== '/' && file_exists(__DIR__ . '/public' . $url)) {
        return false;
    }
}

require __DIR__ . '/public/index.php';
