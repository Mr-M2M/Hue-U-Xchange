<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/product_functions.php';

$pageTitle = 'Deactivate Product - Hue U Xchange';
$baseUrl = '../';
$invalidId = false;
$notFound = false;
$dbError = false;
$product = null;
$actionApplied = false;
$newActiveState = null;

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    $invalidId = true;
} else {
    try {
        $pdo = get_db_connection();
        $product = hue_get_product($pdo, $id);

        if ($product === null) {
            $notFound = true;
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Only a POST request (the confirmation form below) may change
            // the product's active status - a plain GET link only shows
            // the confirmation screen and never mutates data.
            $confirmed = ($_POST['confirm'] ?? '') === 'yes';
            if ($confirmed) {
                $goingActive = !((int) $product['is_active'] === 1);
                hue_set_product_active($pdo, $id, $goingActive);
                header('Location: products.php?' . ($goingActive ? 'activated=1' : 'deactivated=1'));
                exit;
            }
        }
    } catch (Throwable $e) {
        error_log('Product deactivate/activate failed: ' . $e->getMessage());
        $dbError = true;
    }
}

require __DIR__ . '/../includes/header.php';
?>
    <section class="intro">
      <h1>Deactivate / Activate Product</h1>

      <?php if ($invalidId): ?>
        <p class="notice error">A valid product ID is required.</p>
        <p><a href="products.php">Back to product list</a></p>
      <?php elseif ($notFound): ?>
        <p class="notice error">That product could not be found. It may have already been removed.</p>
        <p><a href="products.php">Back to product list</a></p>
      <?php elseif ($dbError): ?>
        <p class="notice error">This action could not be completed right now. Please try again shortly.</p>
        <p><a href="products.php">Back to product list</a></p>
      <?php else: ?>
        <?php $isActive = (int) $product['is_active'] === 1; ?>
        <p class="tagline">
          Please confirm you want to
          <?= $isActive ? 'deactivate' : 'reactivate' ?>
          this product:
        </p>
        <div class="product-card">
          <h2><?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?></h2>
          <p><?= htmlspecialchars($product['symbolic_description'], ENT_QUOTES, 'UTF-8') ?></p>
          <p class="price">$<?= htmlspecialchars(number_format((float) $product['price'], 2), ENT_QUOTES, 'UTF-8') ?></p>
          <p>Current status:
            <span class="status-pill <?= $isActive ? 'status-active' : 'status-inactive' ?>">
              <?= $isActive ? 'Active' : 'Inactive' ?>
            </span>
          </p>
        </div>

        <?php if ($isActive): ?>
          <p class="notice">Deactivating removes this product from the public Offerings page immediately. The record is kept, not deleted, and can be reactivated later.</p>
        <?php endif; ?>

        <form action="product_delete.php" method="POST">
          <input type="hidden" name="id" value="<?= (int) $product['product_id'] ?>">
          <input type="hidden" name="confirm" value="yes">
          <button type="submit"><?= $isActive ? 'Confirm Deactivation' : 'Confirm Reactivation' ?></button>
        </form>
        <p><a href="products.php">Cancel and go back</a></p>
      <?php endif; ?>
    </section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
