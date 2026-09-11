    <section class="intro">
      <h1>Initiation Form</h1>
      <p class="tagline">You are one step away from becoming a Certified Light Carrier.</p>

      <form class="stacked" action="<?= \App\Core\Url::to('checkout/submit') ?>" method="POST">
        <label>
          Name:
          <input type="text" name="name" maxlength="120" required
                 value="<?= htmlspecialchars($values['name'], ENT_QUOTES, 'UTF-8') ?>" />
        </label>
        <?php if (!empty($errors['name'])): ?>
          <p class="field-error"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <label>
          Email:
          <input type="email" name="email" maxlength="180" required
                 value="<?= htmlspecialchars($values['email'], ENT_QUOTES, 'UTF-8') ?>" />
        </label>
        <?php if (!empty($errors['email'])): ?>
          <p class="field-error"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <label>
          Energy Signature:
          <select name="signature" required>
            <option value="" disabled <?= $values['signature'] === '' ? 'selected' : '' ?>>Choose one</option>
            <?php foreach (array('Flame', 'Wave', 'Stone') as $option): ?>
              <option value="<?= $option ?>" <?= $values['signature'] === $option ? 'selected' : '' ?>><?= $option ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <?php if (!empty($errors['signature'])): ?>
          <p class="field-error"><?= htmlspecialchars($errors['signature'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <button type="submit">Complete Initiation</button>
      </form>
    </section>
