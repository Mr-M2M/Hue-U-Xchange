<?php
namespace App\Core;

/**
 * Session lifecycle helper. The front controller calls Session::start()
 * exactly once, before any output, instead of any page calling
 * session_start() directly. Guards against starting a second session.
 */
class Session
{
    public static function start()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /** Ensure the session-based cart array exists. */
    public static function ensureCart()
    {
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    /** Destroy the entire session (used after a completed checkout). */
    public static function destroy()
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}
