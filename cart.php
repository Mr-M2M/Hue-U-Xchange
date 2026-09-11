<?php
require __DIR__ . '/includes/session.php';
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/product_functions.php';

hue_start_session();

const HUE_MAX_QUANTITY = 25;

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$pageTitle = 'Your Cart - Hue U Xchange';
$cartMessages = [];
$catalogError = false;

// Handle cart actions. Every write is a POST request; product IDs and
// quantities are validated with PHP control structures below, and the
// price used for every calculation always comes from the database -
// never from a hidden form field.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'add';
    $id = (int) ($_POST['product_id'] ?? 0);

    if ($id <= 0) {
        $cartMessages[] = ['type' => 'error', 'text' => 'That product could not be identified.'];
    } elseif ($action === 'remove') {
        unset($_SESSION['cart'][$id]);
        $cartMessages[] = ['type' => 'success', 'text' => 'Item removed from your cart.'];
    } else {
        // 'add' and 'update' both set/replace a quantity for a product ID.
        $qtyRaw = $_POST['quantity'] ?? '1';

        if (!ctype_digit((string) $qtyRaw)) {
            $cartMessages[] = ['type' => 'error', 'text' => 'Quantity must be a whole number.'];
        } else {
            $qty = (int) $qtyRaw;
            if ($qty < 1) {
                $cartMessages[] = ['type' => 'error', 'text' => 'Quantity must be at least 1.'];
            } elseif ($qty > HUE_MAX_QUANTITY) {
                $cartMessages[] = ['type' => 'error', 'text' => 'Quantity cannot exceed ' . HUE_MAX_QUANTITY . ' per item.'];
            } else {
                try {
                    $pdo = get_db_connection();
                    $validProduct = hue_get_active_products_by_ids($pdo, [$id]);
                    if (!isset($validProduct[$id])) {
                        $cartMessages[] = ['type' => 'error', 'text' => 'That product is no longer available.'];
                    } elseif ($action === 'add' && isset($_SESSION['cart'][$id])) {
                        $_SESSION['cart'][$id] += $qty;
                    } else {
                        $_SESSION['cart'][$id] = $qty;
                    }
                } catch (Throwable $e) {
                    error_log('Cart action failed: ' . $e->getMessage());
                    $catalogError = true;
                }
            }
        }
    }
}

$cartProducts = [];
if (!empty($_SESSION['cart']) && !$catalogError) {
    try {
        $pdo = get_db_connection();
        $cartProducts = hue_get_active_products_by_ids($pdo, array_keys($_SESSION['cart']));
        // Drop anything in the session cart that is no longer an active
        // product (deactivated or deleted since it was added).
        foreach (array_keys($_SESSION['cart']) as $sessionId) {
            if (!isset($cartProducts[(int) $sessionId])) {
                unset($_SESSION['cart'][$sessionId]);
            }
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

      <?php foreach ($cartMessages as $msg): ?>
        <p class="notice <?= $msg['type'] === 'error' ? 'error' : 'success' ?>">
          <?= htmlspecialchars($msg['text'], ENT_QUOTES, 'UTF-8') ?>
        </p>
      <?php endforeach; ?>

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
          <div class="product-card cart-line">
            <div>
              <h2><?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?></h2>
              <p class="price">$<?= htmlspecialchars(number_format((float) $product['price'], 2), ENT_QUOTES, 'UTF-8') ?> each</p>
              <p class="price">Subtotal: $<?= htmlspecialchars(number_format($subtotal, 2), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <div class="cart-line-actions">
              <form action="cart.php" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="product_id" value="<?= (int) $id ?>">
                <input type="number" name="quantity" value="<?= (int) $qty ?>" min="1" max="<?= HUE_MAX_QUANTITY ?>" required>
                <button type="submit">Update</button>
              </form>
              <form action="cart.php" method="POST">
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="product_id" value="<?= (int) $id ?>">
                <button type="submit" class="link-button">Remove</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>

        <h3><strong>Total: $<?= htmlspecialchars(number_format($total, 2), ENT_QUOTES, 'UTF-8') ?></strong></h3>

        <a href="checkout.php" class="cta-button">Proceed to Checkout</a>
      <?php endif; ?>
    </section>
<?php require __DIR__ . '/includes/footer.php'; ?>
