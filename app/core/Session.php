<?php
namespace App\Core;

/**
 * Session wrapper. start() is called exactly once per request by the
 * front controller (public/index.php); every other caller goes through
 * the same guard, so session_start() can never run twice.
 *
 * Cookie-less fallback: some hosting proxies (for example a preview
 * link that forwards requests to this app) strip cookies. When the
 * browser did not send the session cookie, the session id is carried in
 * an "hsid" query parameter that Url::to() adds to every internal link,
 * form action, and redirect. Strict mode means only ids of sessions this
 * server already created are accepted, so a made-up id cannot be forced
 * on a visitor. Under normal XAMPP use the cookie is present and no id
 * ever appears in a URL after the first page.
 */
class Session
{
    const URL_PARAM = 'hsid';

    /** @var string|null session id to carry in URLs when cookies are missing */
    private static $carryId = null;

    public static function start()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_trans_sid', '0');
        if (!headers_sent()) {
            session_set_cookie_params(array(
                'lifetime' => 0,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ));
        }

        $hasCookie = isset($_COOKIE[session_name()]) && $_COOKIE[session_name()] !== '';
        if (!$hasCookie) {
            $fromUrl = isset($_GET[self::URL_PARAM]) ? $_GET[self::URL_PARAM] : '';
            if (is_string($fromUrl) && preg_match('/^[A-Za-z0-9,-]{22,128}$/', $fromUrl)) {
                session_id($fromUrl);
            }
        }

        session_start();

        if (!$hasCookie) {
            self::$carryId = session_id();
        }
    }

    /** Session id to append to internal URLs, or null when cookies work. */
    public static function carryId()
    {
        return self::$carryId;
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
