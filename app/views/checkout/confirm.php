    <section class="intro">
      <h1>Welcome, <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>.</h1>
      <p>You are now a <strong>Certified Light Carrier</strong>.</p>
      <p>The portal recognizes your signature and your offerings.</p>

      <?php if (!empty($lines)): ?>
        <h2>Order Summary</h2>
        <table class="admin-table">
          <thead>
            <tr>
              <th>Offering</th>
              <th>Qty</th>
              <th>Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($lines as $line): $product = $line['product']; ?>
              <tr>
                <td><?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= (int) $line['quantity'] ?></td>
                <td>$<?= htmlspecialchars(number_format($line['subtotal'], 2), ENT_QUOTES, 'UTF-8') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <h3><strong>Total: $<?= htmlspecialchars(number_format($total, 2), ENT_QUOTES, 'UTF-8') ?></strong></h3>
      <?php endif; ?>

      <p>Thank you for stepping into the light. <a href="<?= \App\Core\Url::to('home') ?>">Return Home</a>.</p>
    </section>
