<?php
require __DIR__ . '/includes/session.php';
hue_start_session();

$name = isset($_POST['name']) ? htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8') : 'Lightbearer';

// Clear the completed order's cart data now that the initiation is confirmed.
$_SESSION = [];
session_destroy();

$pageTitle = 'Confirmation - Hue U Xchange';
require __DIR__ . '/includes/header.php';
?>
    <section class="intro">
      <h1>Welcome, <?= $name ?>.</h1>
      <p>You are now a <strong>Certified Light Carrier</strong>.</p>
      <p>The portal recognizes your signature and your offerings.</p>
      <p>Thank you for stepping into the light. <a href="index.php">Return Home</a>.</p>
    </section>
<?php require __DIR__ . '/includes/footer.php'; ?>
