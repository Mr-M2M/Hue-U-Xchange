    <section class="intro">
      <h1>Manage Products</h1>
      <p class="tagline">Create, edit, and deactivate offerings in the catalog. Inactive products stay in the database but are hidden from the public Offerings page.</p>

      <p><a href="<?= \App\Core\Url::to('products/create') ?>" class="cta-button">Add New Product</a></p>

      <?php if ($loadError): ?>
        <p class="notice error" role="alert"><strong>Error:</strong> The product list could not be loaded right now. Please try again shortly.</p>
      <?php elseif (empty($products)): ?>
        <p class="notice">No products exist yet. Use "Add New Product" to create the first one.</p>
      <?php else: ?>
        <div class="table-wrap">
        <table class="admin-table">
          <caption class="visually-hidden">All products, including inactive ones</caption>
          <thead>
            <tr>
              <th scope="col">ID</th>
              <th scope="col">Name</th>
              <th scope="col">Price</th>
              <th scope="col">Status</th>
              <th scope="col" class="hide-sm">Order</th>
              <th scope="col" class="hide-sm">Updated</th>
              <th scope="col">Actions</th>
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
                <td class="hide-sm"><?= (int) $product['display_order'] ?></td>
                <td class="hide-sm"><?= htmlspecialchars(date('M j, Y g:i A', strtotime($product['updated_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="admin-actions">
                  <a href="<?= \App\Core\Url::to('products/edit', array('id' => (int) $product['product_id'])) ?>">Edit<span class="visually-hidden"> <?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?></span></a>
                  <a href="<?= \App\Core\Url::to('products/delete', array('id' => (int) $product['product_id'])) ?>">
                    <?= ((int) $product['is_active'] === 1) ? 'Deactivate' : 'Activate' ?><span class="visually-hidden"> <?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?></span>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        </div>
      <?php endif; ?>
    </section>
