<?php
/**
 * Shared site navigation. Highlights the current page based on the
 * requested script filename. Honors $baseUrl (see includes/header.php)
 * so pages in a subfolder (e.g. admin/) still link correctly.
 */
$hueCurrentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
$hueBaseUrl = $baseUrl ?? '';

function hue_nav_class(string $page, string $current): string
{
    return $page === $current ? ' class="active"' : '';
}
?>
<header>
  <div class="brand">
    <a href="<?= htmlspecialchars($hueBaseUrl, ENT_QUOTES, 'UTF-8') ?>index.php" class="brand-link">Hue U Xchange</a>
  </div>
  <nav>
    <ul>
      <li<?= hue_nav_class('index.php', $hueCurrentPage) ?>><a href="<?= htmlspecialchars($hueBaseUrl, ENT_QUOTES, 'UTF-8') ?>index.php">Home</a></li>
      <li<?= hue_nav_class('offerings.php', $hueCurrentPage) ?>><a href="<?= htmlspecialchars($hueBaseUrl, ENT_QUOTES, 'UTF-8') ?>offerings.php">Offerings</a></li>
      <li<?= hue_nav_class('cart.php', $hueCurrentPage) ?>><a href="<?= htmlspecialchars($hueBaseUrl, ENT_QUOTES, 'UTF-8') ?>cart.php">Cart</a></li>
      <li<?= hue_nav_class('about.php', $hueCurrentPage) ?>><a href="<?= htmlspecialchars($hueBaseUrl, ENT_QUOTES, 'UTF-8') ?>about.php">About</a></li>
      <li<?= hue_nav_class('products.php', $hueCurrentPage) ?>><a href="<?= htmlspecialchars($hueBaseUrl, ENT_QUOTES, 'UTF-8') ?>admin/products.php">Manage Products</a></li>
    </ul>
  </nav>
</header>
