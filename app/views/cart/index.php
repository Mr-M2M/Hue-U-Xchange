    <section class="intro">
      <h1>Your Cart</h1>

      <?php if ($catalogError): ?>
        <p class="notice error" role="alert"><strong>Error:</strong> Your cart could not be loaded right now. Please try again shortly.</p>
      <?php elseif (empty($lines)): ?>
        <div class="empty-state">
          <p class="notice">Your cart is empty. Every journey starts with a single offering.</p>
          <a href="<?= \App\Core\Url::to('offerings') ?>" class="cta-button">Browse Offerings</a>
        </div>
      <?php else: ?>
        <div class="table-wrap">
          <table class="admin-table cart-table">
            <caption class="visually-hidden">Items in your cart</caption>
            <thead>
              <tr>
                <th scope="col">Offering</th>
                <th scope="col">Price</th>
                <th scope="col">Quantity</th>
                <th scope="col" class="num">Subtotal</th>
                <th scope="col"><span class="visually-hidden">Remove</span></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($lines as $line): $product = $line['product']; $pid = (int) $product['product_id']; $pname = htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8'); ?>
                <tr>
                  <th scope="row"><?= $pname ?></th>
                  <td>$<?= htmlspecialchars(number_format((float) $product['price'], 2), ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <form action="<?= \App\Core\Url::to('cart/update') ?>" method="POST" class="inline-form">
                      <?= \App\Core\Csrf::field() ?>
                      <input type="hidden" name="product_id" value="<?= $pid ?>">
                      <label for="cart-qty-<?= $pid ?>" class="visually-hidden">Quantity for <?= $pname ?></label>
                      <input id="cart-qty-<?= $pid ?>" type="number" name="quantity" value="<?= (int) $line['quantity'] ?>" min="1" max="<?= \App\Models\Cart::MAX_QUANTITY ?>" required>
                      <button type="submit" class="small">Update<span class="visually-hidden"> <?= $pname ?></span></button>
                    </form>
                  </td>
                  <td class="num">$<?= htmlspecialchars(number_format($line['subtotal'], 2), ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <form action="<?= \App\Core\Url::to('cart/remove') ?>" method="POST">
                      <?= \App\Core\Csrf::field() ?>
                      <input type="hidden" name="product_id" value="<?= $pid ?>">
                      <button type="submit" class="link-button">Remove<span class="visually-hidden"> <?= $pname ?></span></button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr>
                <th scope="row" colspan="3">Total</th>
                <td class="num total">$<?= htmlspecialchars(number_format($total, 2), ENT_QUOTES, 'UTF-8') ?></td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
        <p class="hint">Up to <?= \App\Models\Cart::MAX_QUANTITY ?> of each offering. Prices are always taken from the catalog.</p>

        <div class="hero-actions">
          <a href="<?= \App\Core\Url::to('checkout') ?>" class="cta-button">Proceed to Checkout</a>
          <a href="<?= \App\Core\Url::to('offerings') ?>" class="cta-button secondary">Keep Browsing</a>
        </div>
      <?php endif; ?>
    </section>
