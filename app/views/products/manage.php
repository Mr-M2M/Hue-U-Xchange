    <section class="intro">
      <h1>Manage Products</h1>
      <p class="tagline">Create, edit, and deactivate offerings in the catalog.</p>

      <p><a href="<?= \App\Core\Url::to('products/create') ?>" class="cta-button">Add New Product</a></p>

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
                  <a href="<?= \App\Core\Url::to('products/edit', array('id' => (int) $product['product_id'])) ?>">Edit</a>
                  <a href="<?= \App\Core\Url::to('products/delete', array('id' => (int) $product['product_id'])) ?>">
                    <?= ((int) $product['is_active'] === 1) ? 'Deactivate' : 'Activate' ?>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>
