<?php
require __DIR__ . '/config/database.php';

$pageTitle = 'Offerings - Hue U Xchange';
$products = [];
$catalogError = false;

try {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare(
        'SELECT product_id, product_name, symbolic_description, price, image_reference
         FROM products
         WHERE is_active = 1
         ORDER BY display_order ASC, product_name ASC'
    );
    $stmt->execute();
    $products = $stmt->fetchAll();
} catch (Throwable $e) {
    error_log('Offerings page could not load products: ' . $e->getMessage());
    $catalogError = true;
}

require __DIR__ . '/includes/header.php';
?>
    <section class="intro">
      <h1>Choose Your Symbolic Offering</h1>
      <p class="tagline">Each item is a tool of transformation for your journey from shadow to light.</p>

      <?php if ($catalogError): ?>
        <p class="notice error">The offerings catalog could not be loaded right now. Please try again shortly.</p>
      <?php elseif (empty($products)): ?>
        <p class="notice">No offerings are available at this time. Please check back soon.</p>
      <?php else: ?>
        <div class="product-grid">
          <?php foreach ($products as $product): ?>
            <div class="product-card">
              <img src="<?= htmlspecialchars($product['image_reference'], ENT_QUOTES, 'UTF-8') ?>"
                   alt="<?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?>" />
              <h2><?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?></h2>
              <p><?= htmlspecialchars($product['symbolic_description'], ENT_QUOTES, 'UTF-8') ?></p>
              <p class="price">$<?= htmlspecialchars(number_format((float) $product['price'], 2), ENT_QUOTES, 'UTF-8') ?></p>
              <form action="cart.php" method="POST">
                <input type="hidden" name="product_id" value="<?= (int) $product['product_id'] ?>">
                <label>Qty:
                  <input type="number" name="quantity" value="1" min="1" required>
                </label>
                <button type="submit">Add to Cart</button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
<?php require __DIR__ . '/includes/footer.php'; ?>
