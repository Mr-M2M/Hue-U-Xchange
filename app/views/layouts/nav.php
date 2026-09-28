<?php
/**
 * Shared site navigation. Every link is built through App\Core\Url so
 * the routing convention exists in exactly one place. $currentSection
 * and $cartCount come from Controller::layoutData().
 */
$hueSection = isset($currentSection) ? $currentSection : '';
$hueCartCount = isset($cartCount) ? (int) $cartCount : 0;
$hueNavItems = array(
    array('home', '', 'Home'),
    array('offerings', 'offerings', 'Offerings'),
    array('lore', 'lore', 'The Curing Process'),
    array('about', 'about', 'About'),
    array('products', 'products', 'Manage Products'),
);
?>
<header class="site-header">
  <div class="header-inner">
    <a href="<?= \App\Core\Url::to('') ?>" class="brand-link">
      <span class="brand-mark" aria-hidden="true">&#10022;</span>
      <span>Hue U Xchange</span>
    </a>
    <nav aria-label="Main navigation">
      <ul>
        <?php foreach ($hueNavItems as $item): ?>
          <?php $isCurrent = $hueSection === $item[0]; ?>
          <li<?= $isCurrent ? ' class="active"' : '' ?>>
            <a href="<?= \App\Core\Url::to($item[1]) ?>"<?= $isCurrent ? ' aria-current="page"' : '' ?>><?= htmlspecialchars($item[2], ENT_QUOTES, 'UTF-8') ?></a>
          </li>
        <?php endforeach; ?>
        <?php $cartCurrent = in_array($hueSection, array('cart', 'checkout'), true); ?>
        <li class="nav-cart<?= $cartCurrent ? ' active' : '' ?>">
          <a href="<?= \App\Core\Url::to('cart') ?>"<?= $cartCurrent ? ' aria-current="page"' : '' ?>>
            Cart <span class="cart-count"><?= $hueCartCount ?></span><span class="visually-hidden"> <?= $hueCartCount === 1 ? 'item' : 'items' ?></span>
          </a>
        </li>
      </ul>
    </nav>
  </div>
</header>
