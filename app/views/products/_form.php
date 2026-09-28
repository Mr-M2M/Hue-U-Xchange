<?php
/**
 * Shared product form partial, included by products/create.php and
 * products/edit.php. Expects the including view to define:
 *   $formAction   string  form target URL
 *   $values       array   current field values (submitted or loaded)
 *   $errors       array   field_name => message
 *   $submitLabel  string  submit button text
 */
$hueErr = function ($field) use ($errors) {
    if (empty($errors[$field])) {
        return '';
    }
    return '<p class="field-error" id="err-' . $field . '"><strong>Error:</strong> '
        . htmlspecialchars($errors[$field], ENT_QUOTES, 'UTF-8') . '</p>';
};
$hueAria = function ($field, $hintId = '') use ($errors) {
    $ids = trim($hintId . (empty($errors[$field]) ? '' : ' err-' . $field));
    $out = $ids !== '' ? ' aria-describedby="' . $ids . '"' : '';
    return $out . (empty($errors[$field]) ? '' : ' aria-invalid="true"');
};
$hueVal = function ($field, $default = '') use ($values) {
    return htmlspecialchars((string) (isset($values[$field]) ? $values[$field] : $default), ENT_QUOTES, 'UTF-8');
};
$hueActive = (int) (isset($values['is_active']) ? $values['is_active'] : 1);
?>
<?php if (!empty($errors)): ?>
  <p class="notice error" role="alert"><strong>Error:</strong> Please correct the highlighted fields below.</p>
<?php endif; ?>
<form class="stacked" action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>" method="POST" novalidate>
  <?= \App\Core\Csrf::field() ?>

  <div class="field">
    <label for="product_name">Product name <span class="req">(required)</span></label>
    <input id="product_name" type="text" name="product_name" maxlength="120" required
           value="<?= $hueVal('product_name') ?>"<?= $hueAria('product_name', 'hint-name') ?>>
    <p class="hint" id="hint-name">2 to 120 characters. Names must be unique.</p>
    <?= $hueErr('product_name') ?>
  </div>

  <div class="field">
    <label for="symbolic_description">Symbolic description <span class="req">(required)</span></label>
    <textarea id="symbolic_description" name="symbolic_description" rows="3" maxlength="2000" required<?= $hueAria('symbolic_description', 'hint-desc') ?>><?= $hueVal('symbolic_description') ?></textarea>
    <p class="hint" id="hint-desc">2 to 2,000 characters.</p>
    <?= $hueErr('symbolic_description') ?>
  </div>

  <div class="field-row">
    <div class="field">
      <label for="price">Price in USD <span class="req">(required)</span></label>
      <input id="price" type="text" name="price" inputmode="decimal" required
             value="<?= $hueVal('price') ?>"<?= $hueAria('price', 'hint-price') ?>>
      <p class="hint" id="hint-price">For example 24.99. Zero or more, up to two decimals.</p>
      <?= $hueErr('price') ?>
    </div>

    <div class="field">
      <label for="display_order">Display order <span class="req">(required)</span></label>
      <input id="display_order" type="number" name="display_order" min="0" step="1" required
             value="<?= $hueVal('display_order', '0') ?>"<?= $hueAria('display_order', 'hint-order') ?>>
      <p class="hint" id="hint-order">Whole number; lower numbers appear first.</p>
      <?= $hueErr('display_order') ?>
    </div>
  </div>

  <div class="field">
    <label for="image_reference">Image reference</label>
    <input id="image_reference" type="text" name="image_reference" maxlength="255"
           value="<?= $hueVal('image_reference') ?>" placeholder="images/placeholder.png"<?= $hueAria('image_reference', 'hint-img') ?>>
    <p class="hint" id="hint-img">Optional path inside public/images, such as images/placeholder.png.</p>
    <?= $hueErr('image_reference') ?>
  </div>

  <div class="field">
    <label for="is_active">Status</label>
    <select id="is_active" name="is_active"<?= $hueAria('is_active') ?>>
      <option value="1" <?= $hueActive === 1 ? 'selected' : '' ?>>Active - shown on Offerings</option>
      <option value="0" <?= $hueActive === 0 ? 'selected' : '' ?>>Inactive - hidden from Offerings</option>
    </select>
    <?= $hueErr('is_active') ?>
  </div>

  <button type="submit"><?= htmlspecialchars($submitLabel, ENT_QUOTES, 'UTF-8') ?></button>
</form>
