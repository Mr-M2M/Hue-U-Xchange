<?php
/**
 * Shared site navigation. Every link is built through App\Core\Url so
 * the routing convention exists in exactly one place. Highlights the
 * current page from the ?route= value.
 */
$hueCurrentRoute = isset($_GET['route']) ? trim($_GET['route'], '/') : '';

function hue_nav_class($route, $current)
{
    return $route === $current ? ' class="active"' : '';
}
?>
<header>
  <div class="brand">
    <a href="<?= \App\Core\Url::to('') ?>" class="brand-link">Hue U Xchange</a>
  </div>
  <nav>
    <ul>
      <li<?= hue_nav_class('', $hueCurrentRoute) ?>><a href="<?= \App\Core\Url::to('') ?>">Home</a></li>
      <li<?= hue_nav_class('offerings', $hueCurrentRoute) ?>><a href="<?= \App\Core\Url::to('offerings') ?>">Offerings</a></li>
      <li<?= hue_nav_class('cart', $hueCurrentRoute) ?>><a href="<?= \App\Core\Url::to('cart') ?>">Cart</a></li>
      <li<?= hue_nav_class('about', $hueCurrentRoute) ?>><a href="<?= \App\Core\Url::to('about') ?>">About</a></li>
      <li<?= hue_nav_class('products', $hueCurrentRoute) ?>><a href="<?= \App\Core\Url::to('products') ?>">Manage Products</a></li>
    </ul>
  </nav>
</header>
