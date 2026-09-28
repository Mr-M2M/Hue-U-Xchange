    <section class="intro">
      <p class="eyebrow">Database-backed catalog</p>
      <h1>Choose Your Symbolic Offering</h1>
      <p class="tagline">Each item is a tool of transformation for your journey from shadow to light.</p>

      <?php if ($catalogError): ?>
        <p class="notice error" role="alert"><strong>Error:</strong> The offerings catalog could not be loaded right now. Please try again shortly.</p>
      <?php elseif (empty($products)): ?>
        <p class="notice">No offerings are available at this time. Please check back soon.</p>
      <?php else: ?>
        <div class="product-grid">
          <?php foreach ($products as $product): ?>
            <?php $pid = (int) $product['product_id']; ?>
            <article class="product-card">
              <img src="<?= htmlspecialchars($product['image_reference'], ENT_QUOTES, 'UTF-8') ?>"
                   alt="<?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?> offering artwork"
                   width="600" height="400" loading="lazy">
              <h2><?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?></h2>
              <p><?= htmlspecialchars($product['symbolic_description'], ENT_QUOTES, 'UTF-8') ?></p>
              <p class="price">$<?= htmlspecialchars(number_format((float) $product['price'], 2), ENT_QUOTES, 'UTF-8') ?></p>
              <form action="<?= \App\Core\Url::to('cart/add') ?>" method="POST" class="add-form">
                <?= \App\Core\Csrf::field() ?>
                <input type="hidden" name="product_id" value="<?= $pid ?>">
                <label for="qty-<?= $pid ?>">Quantity</label>
                <input id="qty-<?= $pid ?>" type="number" name="quantity" value="1" min="1" max="<?= \App\Models\Cart::MAX_QUANTITY ?>" required>
                <button type="submit">Add <span class="visually-hidden"><?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?> </span>to Cart</button>
              </form>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
