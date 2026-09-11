<?php
/**
 * Shared product form partial, included by product_create.php and
 * product_edit.php. Expects the including page to define:
 *   $formAction   string  form target URL
 *   $values       array   current field values (submitted or loaded)
 *   $errors       array   field_name => message
 *   $submitLabel  string  submit button text
 */
?>
<form class="stacked" action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>" method="POST">
  <label>
    Product name
    <input type="text" name="product_name" maxlength="120" required
           value="<?= htmlspecialchars($values['product_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
  </label>
  <?php if (!empty($errors['product_name'])): ?>
    <p class="field-error"><?= htmlspecialchars($errors['product_name'], ENT_QUOTES, 'UTF-8') ?></p>
  <?php endif; ?>

  <label>
    Symbolic description
    <textarea name="symbolic_description" rows="3" maxlength="2000" required><?= htmlspecialchars($values['symbolic_description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
  </label>
  <?php if (!empty($errors['symbolic_description'])): ?>
    <p class="field-error"><?= htmlspecialchars($errors['symbolic_description'], ENT_QUOTES, 'UTF-8') ?></p>
  <?php endif; ?>

  <label>
    Price (USD)
    <input type="text" name="price" inputmode="decimal" required
           value="<?= htmlspecialchars((string) ($values['price'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
  </label>
  <?php if (!empty($errors['price'])): ?>
    <p class="field-error"><?= htmlspecialchars($errors['price'], ENT_QUOTES, 'UTF-8') ?></p>
  <?php endif; ?>

  <label>
    Image reference
    <input type="text" name="image_reference" maxlength="255"
           value="<?= htmlspecialchars($values['image_reference'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
           placeholder="images/placeholder.png">
  </label>
  <?php if (!empty($errors['image_reference'])): ?>
    <p class="field-error"><?= htmlspecialchars($errors['image_reference'], ENT_QUOTES, 'UTF-8') ?></p>
  <?php endif; ?>

  <label>
    Display order
    <input type="number" name="display_order" min="0" step="1" required
           value="<?= htmlspecialchars((string) ($values['display_order'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>">
  </label>
  <?php if (!empty($errors['display_order'])): ?>
    <p class="field-error"><?= htmlspecialchars($errors['display_order'], ENT_QUOTES, 'UTF-8') ?></p>
  <?php endif; ?>

  <label>
    Status
    <select name="is_active">
      <option value="1" <?= ((int) ($values['is_active'] ?? 1) === 1) ? 'selected' : '' ?>>Active</option>
      <option value="0" <?= ((int) ($values['is_active'] ?? 1) === 0) ? 'selected' : '' ?>>Inactive</option>
    </select>
  </label>
  <?php if (!empty($errors['is_active'])): ?>
    <p class="field-error"><?= htmlspecialchars($errors['is_active'], ENT_QUOTES, 'UTF-8') ?></p>
  <?php endif; ?>

  <button type="submit"><?= htmlspecialchars($submitLabel, ENT_QUOTES, 'UTF-8') ?></button>
</form>
