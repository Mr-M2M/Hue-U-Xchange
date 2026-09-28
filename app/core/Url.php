<?php
namespace App\Core;

/**
 * Builds every internal link, form action, and redirect target so the
 * query-string routing convention (index.php?route=...) lives in one
 * place. Adds the session id only when the browser is not sending the
 * session cookie (see Session::carryId()).
 */
class Url
{
    public static function to($route, array $params = [])
    {
        $query = array_merge(['route' => $route], $params);
        $carry = Session::carryId();
        if ($carry !== null) {
            $query[Session::URL_PARAM] = $carry;
        }
        return 'index.php?' . http_build_query($query);
    }
}
