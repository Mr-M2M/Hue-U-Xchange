<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Models\Cart;

/**
 * Handles the session-based cart. Reads and normalizes request data,
 * delegates every quantity/price rule to the Cart model, and selects a
 * view. Every mutation is a POST request; a plain GET only ever
 * displays the cart.
 */
class CartController extends Controller
{
    public function index()
    {
        try {
            $cart = Cart::contents(get_db_connection());
        } catch (\Throwable $e) {
            error_log('Cart page could not connect: ' . $e->getMessage());
            $cart = array('lines' => array(), 'total' => 0.0, 'catalog_error' => true);
        }

        $this->render('cart/index', array(
            'pageTitle' => 'Your Cart - Hue U Xchange',
            'lines'     => $cart['lines'],
            'total'     => $cart['total'],
            'catalogError' => $cart['catalog_error'],
        ));
    }

    public function add()
    {
        $this->mutate(true);
    }

    public function update()
    {
        $this->mutate(false);
    }

    private function mutate($increment)
    {
        $this->requireValidPost('cart');

        $productId = isset($_POST['product_id']) ? $_POST['product_id'] : 0;
        $quantity = isset($_POST['quantity']) ? $_POST['quantity'] : '1';

        try {
            $pdo = get_db_connection();
            $result = $increment
                ? Cart::add($pdo, $productId, $quantity)
                : Cart::updateQuantity($pdo, $productId, $quantity);

            if ($result['ok']) {
                Flash::set('success', $increment ? 'Added to your cart. Keep walking in the light.' : 'Cart updated.');
            } else {
                Flash::set('error', $result['error']);
            }
        } catch (\Throwable $e) {
            error_log('Cart mutation failed: ' . $e->getMessage());
            Flash::set('error', 'Your cart could not be updated right now. Please try again shortly.');
        }

        $this->redirect('cart');
    }

    public function remove()
    {
        $this->requireValidPost('cart');

        $productId = isset($_POST['product_id']) ? $_POST['product_id'] : 0;
        $result = Cart::remove($productId);

        if ($result['ok']) {
            Flash::set('success', 'Item removed from your cart.');
        } else {
            Flash::set('error', $result['error']);
        }

        $this->redirect('cart');
    }
}
