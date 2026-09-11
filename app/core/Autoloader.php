<?php
/**
 * Hue U Xchange - minimal PSR-4-style autoloader.
 *
 * No Composer dependency is required for an application this size. This
 * autoloader maps the "App\" namespace prefix onto the app/ directory,
 * lower-casing the first namespace segment after "App\" so classes under
 * App\Models, App\Controllers, and App\Core resolve to the lower-case
 * folder names (app/models, app/controllers, app/core) used in this repo.
 *
 * This file itself is loaded with a plain require (it cannot autoload
 * itself), then App\Core\Autoloader::register() is called once from the
 * front controller before any App\ class is referenced.
 */

class HueAutoloader
{
    public static function register()
    {
        spl_autoload_register(function ($class) {
            $prefix = 'App\\';
            if (strpos($class, $prefix) !== 0) {
                return;
            }

            $relative = substr($class, strlen($prefix));
            $parts = explode('\\', $relative);
            $className = array_pop($parts);
            $dirParts = array_map('strtolower', $parts);

            $path = APP_ROOT . '/app/' . implode('/', $dirParts) . '/' . $className . '.php';
            if (is_file($path)) {
                require $path;
            }
        });
    }
}
