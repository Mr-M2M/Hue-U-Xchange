<?php
namespace App\Core;

/**
 * Session wrapper. start() is called exactly once per request by the
 * front controller (public/index.php); every other caller goes through
 * the same guard, so session_start() can never run twice.
 */
class Session
{
    public static function start()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        if (!headers_sent()) {
            session_set_cookie_params(array(
                'lifetime' => 0,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ));
        }
        session_start();
    }

    public static function ensureCart()
    {
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public static function destroy()
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}
