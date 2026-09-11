<?php
namespace App\Core;

/**
 * Builds links back through the single front controller
 * (public/index.php) using the query-string route the router reads.
 * Every navigation link in every view goes through this helper so the
 * routing convention only exists in one place.
 */
class Url
{
    public static function to($route, array $params = [])
    {
        $query = array_merge(['route' => $route], $params);
        return 'index.php?' . http_build_query($query);
    }
}
