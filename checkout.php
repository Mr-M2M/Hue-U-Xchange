<?php
require __DIR__ . '/includes/session.php';
hue_start_session();

$pageTitle = 'Checkout - Hue U Xchange';
require __DIR__ . '/includes/header.php';
?>
    <section class="intro">
      <h1>Initiation Form</h1>
      <p class="tagline">You are one step away from becoming a Certified Light Carrier.</p>

      <form class="stacked" action="confirm.php" method="POST">
        <label>
          Name:
          <input type="text" name="name" required />
        </label>
        <label>
          Email:
          <input type="email" name="email" required />
        </label>
        <label>
          Energy Signature:
          <select name="signature" required>
            <option value="Flame">Flame</option>
            <option value="Wave">Wave</option>
            <option value="Stone">Stone</option>
          </select>
        </label>
        <button type="submit">Complete Initiation</button>
      </form>
    </section>
<?php require __DIR__ . '/includes/footer.php'; ?>
