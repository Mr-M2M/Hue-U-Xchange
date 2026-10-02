    <section class="intro confirm-hero">
      <p class="eyebrow">Initiation complete</p>
      <h1>Welcome, <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>.</h1>
      <p class="confirm-line">You are now a <strong>Certified Light Carrier</strong>.</p>
      <p>The portal recognizes your <?= $signature !== '' ? '<strong>' . htmlspecialchars($signature, ENT_QUOTES, 'UTF-8') . '</strong> ' : '' ?>signature and your offerings.</p>
      <?php if ($reference !== ''): ?>
        <p class="hint">Order reference: <strong><?= htmlspecialchars($reference, ENT_QUOTES, 'UTF-8') ?></strong> &mdash; your order has been recorded. No payment was collected.</p>
      <?php endif; ?>
    </section>

    <?php if (!empty($lines)): ?>
      <section class="panel" aria-labelledby="confirm-summary">
        <h2 id="confirm-summary">Order Summary</h2>
        <?php require APP_ROOT . '/app/views/checkout/_summary.php'; ?>
      </section>
    <?php endif; ?>

    <p>Thank you for stepping into the light. Keep your oath: live authentically, serve selflessly, seek harmony.</p>
    <div class="hero-actions">
      <a href="<?= \App\Core\Url::to('home') ?>" class="cta-button">Return Home</a>
      <a href="<?= \App\Core\Url::to('lore') ?>" class="cta-button secondary">Continue the Story</a>
    </div>
