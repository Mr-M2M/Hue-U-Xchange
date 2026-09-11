    <section class="intro">
      <h1>Edit Product</h1>

      <?php if ($invalidId): ?>
        <p class="notice error">A valid product ID is required to edit a product.</p>
        <p><a href="<?= \App\Core\Url::to('products') ?>">Back to product list</a></p>
      <?php elseif ($notFound): ?>
        <p class="notice error">That product could not be found. It may have already been removed.</p>
        <p><a href="<?= \App\Core\Url::to('products') ?>">Back to product list</a></p>
      <?php elseif ($dbError): ?>
        <p class="notice error">The product could not be loaded or saved right now. Please try again shortly.</p>
        <p><a href="<?= \App\Core\Url::to('products') ?>">Back to product list</a></p>
      <?php else: ?>
        <p class="tagline">Editing product #<?= (int) $id ?>.</p>
        <?php require APP_ROOT . '/app/views/products/_form.php'; ?>
        <p><a href="<?= \App\Core\Url::to('products') ?>">Back to product list</a></p>
      <?php endif; ?>
    </section>
