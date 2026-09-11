    <section class="intro">
      <h1>Your Cart</h1>

      <?php if ($catalogError): ?>
        <p class="notice error">Your cart could not be loaded right now. Please try again shortly.</p>
      <?php elseif (empty($lines)): ?>
        <p class="notice">Your cart is empty. <a href="<?= \App\Core\Url::to('offerings') ?>">Browse offerings</a>.</p>
      <?php else: ?>
        <?php foreach ($lines as $line): $product = $line['product']; ?>
          <div class="product-card cart-line">
            <div>
              <h2><?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?></h2>
              <p class="price">$<?= htmlspecialchars(number_format((float) $product['price'], 2), ENT_QUOTES, 'UTF-8') ?> each</p>
              <p class="price">Subtotal: $<?= htmlspecialchars(number_format($line['subtotal'], 2), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <div class="cart-line-actions">
              <form action="<?= \App\Core\Url::to('cart/update') ?>" method="POST">
                <input type="hidden" name="product_id" value="<?= (int) $product['product_id'] ?>">
                <input type="number" name="quantity" value="<?= (int) $line['quantity'] ?>" min="1" max="<?= \App\Models\Cart::MAX_QUANTITY ?>" required>
                <button type="submit">Update</button>
              </form>
              <form action="<?= \App\Core\Url::to('cart/remove') ?>" method="POST">
                <input type="hidden" name="product_id" value="<?= (int) $product['product_id'] ?>">
                <button type="submit" class="link-button">Remove</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>

        <h3><strong>Total: $<?= htmlspecialchars(number_format($total, 2), ENT_QUOTES, 'UTF-8') ?></strong></h3>

        <a href="<?= \App\Core\Url::to('checkout') ?>" class="cta-button">Proceed to Checkout</a>
      <?php endif; ?>
    </section>
