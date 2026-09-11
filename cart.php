<?php
require __DIR__ . '/includes/session.php';
require __DIR__ . '/config/database.php';

hue_start_session();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle adding items to the cart. Product IDs are validated against the
// database below rather than trusted directly from the form.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['product_id'] ?? 0);
    $qty = max(1, (int) ($_POST['quantity'] ?? 1));

    if ($id > 0) {
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id] += $qty;
        } else {
            $_SESSION['cart'][$id] = $qty;
        }
    }
}

$pageTitle = 'Your Cart - Hue U Xchange';
$cartProducts = [];
$catalogError = false;

if (!empty($_SESSION['cart'])) {
    try {
        $pdo = get_db_connection();
        $ids = array_map('intval', array_keys($_SESSION['cart']));
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare(
            "SELECT product_id, product_name, price FROM products WHERE product_id IN ($placeholders)"
        );
        $stmt->execute($ids);
        foreach ($stmt->fetchAll() as $row) {
            $cartProducts[(int) $row['product_id']] = $row;
        }
    } catch (Throwable $e) {
        error_log('Cart page could not load products: ' . $e->getMessage());
        $catalogError = true;
    }
}

require __DIR__ . '/includes/header.php';
?>
    <section class="intro">
      <h1>Your Cart</h1>

      <?php if ($catalogError): ?>
        <p class="notice error">Your cart could not be loaded right now. Please try again shortly.</p>
      <?php elseif (empty($_SESSION['cart'])): ?>
        <p class="notice">Your cart is empty. <a href="offerings.php">Browse offerings</a>.</p>
      <?php else: ?>
        <?php
          $total = 0;
          foreach ($_SESSION['cart'] as $id => $qty):
            $product = $cartProducts[(int) $id] ?? null;
            if (!$product) {
                continue;
            }
            $subtotal = (float) $product['price'] * $qty;
            $total += $subtotal;
        ?>
          <div class="product-card">
            <h2><?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p>Quantity: <?= (int) $qty ?></p>
            <p class="price">Subtotal: $<?= htmlspecialchars(number_format($subtotal, 2), ENT_QUOTES, 'UTF-8') ?></p>
          </div>
        <?php endforeach; ?>

        <h3><strong>Total: $<?= htmlspecialchars(number_format($total, 2), ENT_QUOTES, 'UTF-8') ?></strong></h3>

        <a href="checkout.php" class="cta-button">Proceed to Checkout</a>
      <?php endif; ?>
    </section>
<?php require __DIR__ . '/includes/footer.php'; ?>
