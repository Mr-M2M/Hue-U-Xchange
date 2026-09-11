<?php
/**
 * Shared site navigation. Highlights the current page based on the
 * requested script filename.
 */
$hueCurrentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');

function hue_nav_class(string $page, string $current): string
{
    return $page === $current ? ' class="active"' : '';
}
?>
<header>
  <div class="brand">
    <a href="index.php" class="brand-link">Hue U Xchange</a>
  </div>
  <nav>
    <ul>
      <li<?= hue_nav_class('index.php', $hueCurrentPage) ?>><a href="index.php">Home</a></li>
      <li<?= hue_nav_class('offerings.php', $hueCurrentPage) ?>><a href="offerings.php">Offerings</a></li>
      <li<?= hue_nav_class('cart.php', $hueCurrentPage) ?>><a href="cart.php">Cart</a></li>
      <li<?= hue_nav_class('about.php', $hueCurrentPage) ?>><a href="about.php">About</a></li>
    </ul>
  </nav>
</header>
