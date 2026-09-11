<?php
/**
 * Router script for PHP's built-in development server only, e.g.:
 *   php -S 127.0.0.1:8000 -t public public/router.php
 *
 * PHP's built-in server ignores .htaccess, so this script reproduces
 * the same behavior as public/.htaccess for local testing without a
 * full Apache/XAMPP stack: real files (CSS, images) are served as-is,
 * and every other request is handed to the front controller with the
 * requested path available as ?route=.
 *
 * This file is never used by Apache/XAMPP - it exists purely so
 * "php -S" can be used for a quick local smoke test.
 */

$path = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if ($path !== '/' && file_exists(__DIR__ . $path) && is_file(__DIR__ . $path)) {
    return false; // let the built-in server serve the static file directly
}

if ($path !== '/' && $path !== '/index.php' && !isset($_GET['route'])) {
    $_GET['route'] = trim($path, '/');
}

require __DIR__ . '/index.php';
