<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/product_functions.php';

$pageTitle = 'Add Product - Hue U Xchange';
$baseUrl = '../';
$formAction = 'product_create.php';
$submitLabel = 'Create Product';
$errors = [];
$values = ['is_active' => 1, 'display_order' => 0];
$dbError = false;
$created = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$values, $errors] = hue_validate_product_input($_POST);

    if (empty($errors)) {
        try {
            $pdo = get_db_connection();
            $newId = hue_create_product($pdo, $values);
            $created = true;
            header('Location: products.php?created=1');
            exit;
        } catch (HueDuplicateProductException $e) {
            $errors['product_name'] = $e->getMessage();
        } catch (Throwable $e) {
            error_log('Product create failed: ' . $e->getMessage());
            $dbError = true;
        }
    }
}

require __DIR__ . '/../includes/header.php';
?>
    <section class="intro">
      <h1>Add New Product</h1>
      <p class="tagline">Create a new symbolic offering for the catalog.</p>

      <?php if ($dbError): ?>
        <p class="notice error">The product could not be saved right now. Please try again shortly.</p>
      <?php endif; ?>

      <?php require __DIR__ . '/_product_form.php'; ?>

      <p><a href="products.php">Back to product list</a></p>
    </section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
