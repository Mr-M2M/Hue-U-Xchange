<?php
/**
 * Hue U Xchange - route map.
 *
 * Every browser-facing route the application serves, mapped to the
 * controller class and method that handles it. The front controller
 * (public/index.php) is the only file that reads this map and
 * dispatches to a controller - individual controllers never know their
 * own route string.
 *
 * Route strings are matched against $_GET['route'] (query-string
 * routing, e.g. index.php?route=products) which works unconditionally
 * under XAMPP/Apache and PHP's built-in server alike. public/.htaccess
 * additionally rewrites pretty paths like /products into that same
 * query string when Apache mod_rewrite is available - see
 * docs/mvc-architecture.md.
 */

return array(
    ''                => array('App\\Controllers\\HomeController', 'index'),
    'home'            => array('App\\Controllers\\HomeController', 'index'),
    'about'           => array('App\\Controllers\\AboutController', 'index'),

    'offerings'       => array('App\\Controllers\\ProductController', 'offerings'),
    'products'        => array('App\\Controllers\\ProductController', 'manage'),
    'products/create' => array('App\\Controllers\\ProductController', 'create'),
    'products/edit'   => array('App\\Controllers\\ProductController', 'edit'),
    'products/delete' => array('App\\Controllers\\ProductController', 'delete'),

    'cart'            => array('App\\Controllers\\CartController', 'index'),
    'cart/add'        => array('App\\Controllers\\CartController', 'add'),
    'cart/update'     => array('App\\Controllers\\CartController', 'update'),
    'cart/remove'     => array('App\\Controllers\\CartController', 'remove'),

    'checkout'        => array('App\\Controllers\\CheckoutController', 'index'),
    'checkout/submit' => array('App\\Controllers\\CheckoutController', 'submit'),
    'confirm'         => array('App\\Controllers\\CheckoutController', 'confirmation'),
);
