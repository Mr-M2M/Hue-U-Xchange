    <section class="intro">
      <h1>Initiation Form</h1>
      <p class="tagline">You are one step away from becoming a Certified Light Carrier.</p>
    </section>

    <div class="checkout-layout">
      <section class="panel" aria-labelledby="summary-heading">
        <h2 id="summary-heading">Order Summary</h2>
        <?php require APP_ROOT . '/app/views/checkout/_summary.php'; ?>
        <p class="hint">Your order is recorded when you complete initiation. No payment is collected.</p>
        <p><a href="<?= \App\Core\Url::to('cart') ?>">Edit your cart</a></p>
      </section>

      <section class="panel" aria-labelledby="details-heading">
        <h2 id="details-heading">Your Details</h2>
        <?php if (!empty($errors)): ?>
          <p class="notice error" role="alert"><strong>Error:</strong> Please correct the highlighted fields below.</p>
        <?php endif; ?>
        <form class="stacked" action="<?= \App\Core\Url::to('checkout/submit') ?>" method="POST" data-once novalidate>
          <?= \App\Core\Csrf::field() ?>

          <div class="field">
            <label for="name">Name <span class="req">(required)</span></label>
            <input id="name" type="text" name="name" maxlength="120" required autocomplete="name"
                   value="<?= htmlspecialchars($values['name'], ENT_QUOTES, 'UTF-8') ?>"<?= !empty($errors['name']) ? ' aria-invalid="true" aria-describedby="err-name"' : '' ?>>
            <?php if (!empty($errors['name'])): ?>
              <p class="field-error" id="err-name"><strong>Error:</strong> <?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
          </div>

          <div class="field">
            <label for="email">Email <span class="req">(required)</span></label>
            <input id="email" type="email" name="email" maxlength="180" required autocomplete="email"
                   value="<?= htmlspecialchars($values['email'], ENT_QUOTES, 'UTF-8') ?>"<?= !empty($errors['email']) ? ' aria-invalid="true" aria-describedby="err-email"' : '' ?>>
            <?php if (!empty($errors['email'])): ?>
              <p class="field-error" id="err-email"><strong>Error:</strong> <?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
          </div>

          <div class="field">
            <label for="signature">Energy Signature <span class="req">(optional)</span></label>
            <select id="signature" name="signature" aria-describedby="hint-sig<?= !empty($errors['signature']) ? ' err-signature' : '' ?>"<?= !empty($errors['signature']) ? ' aria-invalid="true"' : '' ?>>
              <option value="" <?= $values['signature'] === '' ? 'selected' : '' ?>>No signature selected</option>
              <?php foreach ($signatures as $option): ?>
                <option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>" <?= $values['signature'] === $option ? 'selected' : '' ?>><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
            <p class="hint" id="hint-sig">Flame, Wave, or Stone &mdash; the element that carries your light.</p>
            <?php if (!empty($errors['signature'])): ?>
              <p class="field-error" id="err-signature"><strong>Error:</strong> <?= htmlspecialchars($errors['signature'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
          </div>

          <button type="submit" data-busy-label="Completing initiation...">Complete Initiation</button>
        </form>
      </section>
    </div>
