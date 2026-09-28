<?php
/**
 * Shared document head + nav + main-content opening tag. Every
 * controller reaches this through Controller::render(); it expects
 * $pageTitle, $flashMessages, $currentSection, and $cartCount to be set.
 */
if (!isset($pageTitle) || $pageTitle === '') {
    $pageTitle = 'Hue U Xchange';
}
if (!isset($flashMessages)) {
    $flashMessages = array();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Hue U Xchange - the symbolic storefront and portal of the Shining Light Army, inspired by Donny D and The Curing Process.">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;700&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <a class="skip-link" href="#main-content">Skip to main content</a>
  <?php require APP_ROOT . '/app/views/layouts/nav.php'; ?>
  <main id="main-content" tabindex="-1">
    <?php if (!empty($flashMessages)): ?>
      <div class="flash-stack" role="status" aria-live="polite">
        <?php foreach ($flashMessages as $msg): ?>
          <?php $isError = $msg['type'] === 'error'; ?>
          <p class="notice <?= $isError ? 'error' : 'success' ?>">
            <strong><?= $isError ? 'Error:' : 'Success:' ?></strong>
            <?= htmlspecialchars($msg['text'], ENT_QUOTES, 'UTF-8') ?>
          </p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
