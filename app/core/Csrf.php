<?php
namespace App\Core;

/**
 * Per-session form token.
 *
 * Every POST form in the application includes the current token in a
 * hidden "csrf_token" field, and every state-changing controller action
 * checks it before doing anything. This blocks forged cross-site POSTs
 * and, because the token is rotated after a successful checkout, also
 * stops the same checkout form from being processed twice.
 */
class Csrf
{
    const FIELD = 'csrf_token';

    public static function token()
    {
        Session::start();
        if (empty($_SESSION['_csrf']) || !is_string($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function isValid($submitted)
    {
        Session::start();
        return is_string($submitted)
            && isset($_SESSION['_csrf'])
            && is_string($_SESSION['_csrf'])
            && hash_equals($_SESSION['_csrf'], $submitted);
    }

    public static function rotate()
    {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    /** Hidden input markup used by views: <?= \App\Core\Csrf::field() ?> */
    public static function field()
    {
        return '<input type="hidden" name="' . self::FIELD . '" value="'
            . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}
