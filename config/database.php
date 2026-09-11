<?php
/**
 * Hue U Xchange - centralized PDO database connection.
 *
 * Reads connection settings from environment variables when they are
 * available (recommended for any real deployment), and falls back to the
 * local defaults in config/config.local.php for local XAMPP development.
 *
 * config/config.local.php is intentionally NOT committed to GitHub
 * (see .gitignore). Copy config/config.local.example.php to
 * config/config.local.php and adjust it for your own machine — see
 * README.md for the full local setup steps.
 *
 * Usage from any page:
 *   require_once __DIR__ . '/../config/database.php';
 *   $pdo = get_db_connection();
 */

function hue_db_config(): array
{
    $localConfigFile = __DIR__ . '/config.local.php';
    $local = [];
    if (file_exists($localConfigFile)) {
        $local = require $localConfigFile;
    }

    return [
        'host'    => getenv('HUE_DB_HOST')    ?: ($local['host']    ?? '127.0.0.1'),
        'port'    => getenv('HUE_DB_PORT')    ?: ($local['port']    ?? '3306'),
        'name'    => getenv('HUE_DB_NAME')    ?: ($local['name']    ?? 'hue_u_xchange'),
        'user'    => getenv('HUE_DB_USER')    ?: ($local['user']    ?? 'root'),
        'pass'    => getenv('HUE_DB_PASS')    ?: ($local['pass']    ?? ''),
    ];
}

/**
 * Returns a shared PDO connection. Never echoes credentials or raw
 * exception details to the browser — any failure is logged to the PHP
 * error log and the caller receives a clean thrown exception that pages
 * are expected to catch and turn into a safe on-screen message.
 */
function get_db_connection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $cfg = hue_db_config();
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $cfg['host'],
        $cfg['port'],
        $cfg['name']
    );

    try {
        $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        error_log('Hue U Xchange DB connection failed: ' . $e->getMessage());
        throw new RuntimeException('Database connection unavailable.');
    }
}
