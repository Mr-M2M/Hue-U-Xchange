<?php
namespace App\Models;

use App\Core\Session;
use PDO;

/**
 * Hue U Xchange - Cart model (session-based).
 *
 * Owns the session cart's data rules: every product ID and quantity is
 * validated against the database before it is trusted, prices always
 * come from the database (never a hidden form field), and quantities
 * are bounded. This class never generates HTML.
 */
class Cart
{
    const MAX_QUANTITY = 25;

    /** Raw session cart contents: [product_id => quantity]. */
    public static function raw()
    {
        Session::ensureCart();
        return $_SESSION['cart'];
    }

    /**
     * Validate and normalize a submitted quantity.
     * Returns [int|null $qty, string|null $error].
     */
    private static function validateQuantity($qtyRaw)
    {
        if (!ctype_digit((string) $qtyRaw)) {
            return array(null, 'Quantity must be a whole number.');
        }
        $qty = (int) $qtyRaw;
        if ($qty < 1) {
            return array(null, 'Quantity must be at least 1.');
        }
        if ($qty > self::MAX_QUANTITY) {
            return array(null, 'Quantity cannot exceed ' . self::MAX_QUANTITY . ' per item.');
        }
        return array($qty, null);
    }

    /**
     * Add a quantity to a product line (creating it if new). Validates the
     * product ID against active products in the database.
     * Returns ['ok' => bool, 'error' => string|null].
     */
    public static function add(PDO $pdo, $productId, $qtyRaw)
    {
        return self::addOrSet($pdo, $productId, $qtyRaw, true);
    }

    /**
     * Set a product line to an exact quantity (used by the cart's
     * "Update" control). Same validation as add().
     */
    public static function updateQuantity(PDO $pdo, $productId, $qtyRaw)
    {
        return self::addOrSet($pdo, $productId, $qtyRaw, false);
    }

    private static function addOrSet(PDO $pdo, $productId, $qtyRaw, $increment)
    {
        Session::ensureCart();
        $id = (int) $productId;

        if ($id <= 0) {
            return array('ok' => false, 'error' => 'That product could not be identified.');
        }

        list($qty, $qtyError) = self::validateQuantity($qtyRaw);
        if ($qtyError !== null) {
            return array('ok' => false, 'error' => $qtyError);
        }

        $productModel = new Product($pdo);
        $validProducts = $productModel->getActiveByIds(array($id));
        if (!isset($validProducts[$id])) {
            return array('ok' => false, 'error' => 'That product is no longer available.');
        }

        if ($increment && isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id] += $qty;
        } else {
            $_SESSION['cart'][$id] = $qty;
        }

        return array('ok' => true, 'error' => null);
    }

    /** Remove a single line from the cart. */
    public static function remove($productId)
    {
        Session::ensureCart();
        $id = (int) $productId;
        if ($id <= 0) {
            return array('ok' => false, 'error' => 'That product could not be identified.');
        }
        unset($_SESSION['cart'][$id]);
        return array('ok' => true, 'error' => null);
    }

    /** Empty the cart entirely (used after a successful checkout). */
    public static function clear()
    {
        $_SESSION['cart'] = array();
    }

    public static function isEmpty()
    {
        Session::ensureCart();
        return empty($_SESSION['cart']);
    }

    /**
     * Resolve the current session cart against trusted database data.
     * Drops any line that is no longer an active product (deactivated or
     * deleted since it was added) and prices every line from the
     * database. Returns:
     *   [
     *     'lines'        => [['product' => array, 'quantity' => int, 'subtotal' => float], ...],
     *     'total'        => float,
     *     'catalog_error'=> bool,
     *   ]
     */
    public static function contents(PDO $pdo)
    {
        Session::ensureCart();
        $result = array('lines' => array(), 'total' => 0.0, 'catalog_error' => false);

        if (empty($_SESSION['cart'])) {
            return $result;
        }

        try {
            $productModel = new Product($pdo);
            $products = $productModel->getActiveByIds(array_keys($_SESSION['cart']));

            foreach (array_keys($_SESSION['cart']) as $sessionId) {
                if (!isset($products[(int) $sessionId])) {
                    unset($_SESSION['cart'][$sessionId]);
                }
            }

            foreach ($_SESSION['cart'] as $id => $qty) {
                $product = isset($products[(int) $id]) ? $products[(int) $id] : null;
                if (!$product) {
                    continue;
                }
                $subtotal = (float) $product['price'] * (int) $qty;
                $result['lines'][] = array(
                    'product'  => $product,
                    'quantity' => (int) $qty,
                    'subtotal' => $subtotal,
                );
                $result['total'] += $subtotal;
            }
        } catch (\Throwable $e) {
            error_log('Cart could not resolve products: ' . $e->getMessage());
            $result['catalog_error'] = true;
        }

        return $result;
    }
}
