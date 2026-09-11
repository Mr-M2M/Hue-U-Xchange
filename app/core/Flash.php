<?php
namespace App\Core;

/**
 * One-time flash messages carried across a redirect (Post-Redirect-Get).
 * A controller calls Flash::set() right before redirecting; the next
 * request's render() call pulls and clears the queued messages so a
 * page refresh never repeats them.
 */
class Flash
{
    public static function set($type, $text)
    {
        Session::start();
        if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'][] = ['type' => $type, 'text' => $text];
    }

    /** Return queued messages and clear them (read-once). */
    public static function pull()
    {
        $messages = isset($_SESSION['flash']) ? $_SESSION['flash'] : [];
        $_SESSION['flash'] = [];
        return $messages;
    }
}
