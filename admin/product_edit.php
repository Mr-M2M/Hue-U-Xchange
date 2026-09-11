<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/product_functions.php';

$pageTitle = 'Edit Product - Hue U Xchange';
$baseUrl = '../';
$errors = [];
$dbError = false;
$notFound = false;
$invalidId = false;

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    $invalidId = true;
} else {
    try {
        $pdo = get_db_connection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$values, $errors] = hue_validate_product_input($_POST);

            if (empty($errors)) {
                if (!hue_product_exists($pdo, $id)) {
                    $notFound = true;
                } else {
                    try {
                        hue_update_product($pdo, $id, $values);
                        header('Location: products.php?updated=1');
                        exit;
                    } catch (HueDuplicateProductException $e) {
                        $errors['product_name'] = $e->getMessage();
                    }
                }
            }
        } else {
            $existing = hue_get_product($pdo, $id);
            if ($existing === null) {
                $notFound = true;
            } else {
                $values = $existing;
            }
        }
    } catch (Throwable $e) {
        error_log('Product edit failed: ' . $e->getMessage());
        $dbError = true;
    }
}

$formAction = 'product_edit.php?id=' . (int) $id;
$submitLabel = 'Save Changes';

require __DIR__ . '/../includes/header.php';
?>
    <section class="intro">
      <h1>Edit Product</h1>

      <?php if ($invalidId): ?>
        <p class="notice error">A valid product ID is required to edit a product.</p>
        <p><a href="products.php">Back to product list</a></p>
      <?php elseif ($notFound): ?>
        <p class="notice error">That product could not be found. It may have already been removed.</p>
        <p><a href="products.php">Back to product list</a></p>
      <?php elseif ($dbError): ?>
        <p class="notice error">The product could not be loaded or saved right now. Please try again shortly.</p>
        <p><a href="products.php">Back to product list</a></p>
      <?php else: ?>
        <p class="tagline">Editing product #<?= (int) $id ?>.</p>
        <?php require __DIR__ . '/_product_form.php'; ?>
        <p><a href="products.php">Back to product list</a></p>
      <?php endif; ?>
    </section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
