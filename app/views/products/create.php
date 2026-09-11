    <section class="intro">
      <h1>Add New Product</h1>
      <p class="tagline">Create a new symbolic offering for the catalog.</p>

      <?php if ($dbError): ?>
        <p class="notice error">The product could not be saved right now. Please try again shortly.</p>
      <?php endif; ?>

      <?php require APP_ROOT . '/app/views/products/_form.php'; ?>

      <p><a href="<?= \App\Core\Url::to('products') ?>">Back to product list</a></p>
    </section>
