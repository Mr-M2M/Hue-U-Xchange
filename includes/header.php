<?php
/**
 * Shared page header. Set $pageTitle before including this file, e.g.:
 *   $pageTitle = 'Offerings - Hue U Xchange';
 *   require __DIR__ . '/includes/header.php';
 */
if (!isset($pageTitle) || $pageTitle === '') {
    $pageTitle = 'Hue U Xchange';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="stylesheet" href="css/style.css" />

  <!-- Google Font: Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
</head>
<body>
  <?php require __DIR__ . '/nav.php'; ?>
  <main>
