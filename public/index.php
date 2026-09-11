<?php
/**
 * Hue U Xchange - front controller.
 *
 * Every browser request enters the application through this single
 * file. It loads configuration, starts the session once, registers the
 * autoloader, resolves the requested route, and hands off to the
 * matching controller action. See docs/mvc-architecture.md for the full
 * request-flow explanation.
 */

define('APP_ROOT', dirname(__DIR__));

require APP_ROOT . '/app/core/Autoloader.php';
HueAutoloader::register();

require APP_ROOT . '/config/database.php';

use App\Core\Session;

Session::start();

$routes = require APP_ROOT . '/routes/routes.php';

// Route resolution: prefer the query string (?route=products), which
// works unconditionally under XAMPP and PHP's built-in server. When
// Apache mod_rewrite is active, public/.htaccess rewrites a pretty path
// like /products into that same query string before this file runs.
$route = isset($_GET['route']) ? trim($_GET['route'], '/') : '';

if (!isset($routes[$route])) {
    http_response_code(404);
    $flashMessages = array();
    $pageTitle = 'Not Found - Hue U Xchange';
    require APP_ROOT . '/app/views/layouts/header.php';
    require APP_ROOT . '/app/views/errors/not_found.php';
    require APP_ROOT . '/app/views/layouts/footer.php';
    exit;
}

list($controllerClass, $action) = $routes[$route];

try {
    if (!class_exists($controllerClass) || !method_exists($controllerClass, $action)) {
        throw new RuntimeException("Route \"$route\" has no valid controller action.");
    }

    $controller = new $controllerClass();
    $controller->$action();
} catch (\Throwable $e) {
    // Never expose a stack trace or credentials to the browser - log
    // the real error and show a safe, generic message.
    error_log('Hue U Xchange unhandled error on route "' . $route . '": ' . $e->getMessage());
    http_response_code(500);
    $flashMessages = array();
    $pageTitle = 'Something Went Wrong - Hue U Xchange';
    require APP_ROOT . '/app/views/layouts/header.php';
    require APP_ROOT . '/app/views/errors/error.php';
    require APP_ROOT . '/app/views/layouts/footer.php';
}
