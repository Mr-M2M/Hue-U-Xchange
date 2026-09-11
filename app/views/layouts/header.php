<?php
/**
 * Shared document head + nav + main-content opening tag. Every
 * controller reaches this through Controller::render(); it expects
 * $pageTitle and $flashMessages to already be set.
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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="stylesheet" href="assets/css/style.css" />

  <!-- Google Font: Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
</head>
<body>
  <?php require APP_ROOT . '/app/views/layouts/nav.php'; ?>
  <main>
    <?php foreach ($flashMessages as $msg): ?>
      <p class="notice <?= $msg['type'] === 'error' ? 'error' : 'success' ?>">
        <?= htmlspecialchars($msg['text'], ENT_QUOTES, 'UTF-8') ?>
      </p>
    <?php endforeach; ?>
