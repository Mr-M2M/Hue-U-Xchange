<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/product_functions.php';

$pageTitle = 'Manage Products - Hue U Xchange';
$baseUrl = '../';
$products = [];
$loadError = false;

try {
    $pdo = get_db_connection();
    $products = hue_get_all_products($pdo);
} catch (Throwable $e) {
    error_log('Product management list could not load: ' . $e->getMessage());
    $loadError = true;
}

require __DIR__ . '/../includes/header.php';
?>
    <section class="intro">
      <h1>Manage Products</h1>
      <p class="tagline">Create, edit, and deactivate offerings in the catalog.</p>

      <?php if (isset($_GET['created'])): ?>
        <p class="notice success">Product created successfully.</p>
      <?php elseif (isset($_GET['updated'])): ?>
        <p class="notice success">Product updated successfully.</p>
      <?php elseif (isset($_GET['deactivated'])): ?>
        <p class="notice success">Product deactivated. It no longer appears on the public Offerings page.</p>
      <?php elseif (isset($_GET['activated'])): ?>
        <p class="notice success">Product reactivated. It now appears on the public Offerings page.</p>
      <?php endif; ?>

      <p><a href="product_create.php" class="cta-button">Add New Product</a></p>

      <?php if ($loadError): ?>
        <p class="notice error">The product list could not be loaded right now. Please try again shortly.</p>
      <?php elseif (empty($products)): ?>
        <p class="notice">No products exist yet. Use "Add New Product" to create the first one.</p>
      <?php else: ?>
        <table class="admin-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Price</th>
              <th>Status</th>
              <th>Order</th>
              <th>Updated</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $product): ?>
              <tr>
                <td><?= (int) $product['product_id'] ?></td>
                <td><?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>$<?= htmlspecialchars(number_format((float) $product['price'], 2), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <?php if ((int) $product['is_active'] === 1): ?>
                    <span class="status-pill status-active">Active</span>
                  <?php else: ?>
                    <span class="status-pill status-inactive">Inactive</span>
                  <?php endif; ?>
                </td>
                <td><?= (int) $product['display_order'] ?></td>
                <td><?= htmlspecialchars(date('M j, Y g:i A', strtotime($product['updated_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="admin-actions">
                  <a href="product_edit.php?id=<?= (int) $product['product_id'] ?>">Edit</a>
                  <a href="product_delete.php?id=<?= (int) $product['product_id'] ?>">
                    <?= ((int) $product['is_active'] === 1) ? 'Deactivate' : 'Activate' ?>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
