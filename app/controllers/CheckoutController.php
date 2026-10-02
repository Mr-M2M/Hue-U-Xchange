<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Models\Cart;
use App\Models\Order;

/**
 * Checkout flow: initiation form -> server-side validation -> order saved
 * to MySQL (customers, orders, order_items) -> one-time Certified Light
 * Carrier confirmation. No payment is processed.
 */
class CheckoutController extends Controller
{
    /** Optional Energy Signatures a Lightbearer may choose at checkout. */
    const SIGNATURES = array('Flame', 'Wave', 'Stone');

    public function index()
    {
        $cart = $this->loadCartOrRedirect();

        $this->render('checkout/form', array(
            'pageTitle'  => 'Checkout - Hue U Xchange',
            'values'     => array('name' => '', 'email' => '', 'signature' => ''),
            'errors'     => array(),
            'signatures' => self::SIGNATURES,
            'lines'      => $cart['lines'],
            'total'      => $cart['total'],
        ));
    }

    public function submit()
    {
        $this->requireValidPost('checkout');

        $cart = $this->loadCartOrRedirect();

        $values = array(
            'name'      => trim((string) (isset($_POST['name']) ? $_POST['name'] : '')),
            'email'     => trim((string) (isset($_POST['email']) ? $_POST['email'] : '')),
            'signature' => trim((string) (isset($_POST['signature']) ? $_POST['signature'] : '')),
        );
        $errors = self::validate($values);

        if (!empty($errors)) {
            $this->render('checkout/form', array(
                'pageTitle'  => 'Checkout - Hue U Xchange',
                'values'     => $values,
                'errors'     => $errors,
                'signatures' => self::SIGNATURES,
                'lines'      => $cart['lines'],
                'total'      => $cart['total'],
            ));
            return;
        }

        // Save the order (trusted MySQL prices resolved by Cart::contents)
        // in one transaction. The cart is cleared only after the order is
        // committed; if saving fails, the cart is kept and nothing is
        // recorded.
        try {
            $placed = (new Order(get_db_connection()))->place(
                $values['name'],
                $values['email'],
                $values['signature'],
                $cart['lines']
            );
        } catch (\Throwable $e) {
            error_log('Checkout could not save the order: ' . $e->getMessage());
            Flash::set('error', 'Your initiation could not be completed right now. Your cart has been kept - please try again shortly.');
            $this->redirect('checkout');
            return;
        }

        // Rotate the form token so the same form cannot be processed a
        // second time, then clear the cart and show the confirmation once.
        $_SESSION['checkout_confirmation'] = $placed['order_id'];
        Csrf::rotate();
        Cart::clear();

        $this->redirect('confirm');
    }

    public function confirmation()
    {
        if (!isset($_SESSION['checkout_confirmation'])) {
            $this->redirect('home');
            return;
        }

        $orderId = (int) $_SESSION['checkout_confirmation'];
        unset($_SESSION['checkout_confirmation']);

        // The confirmation is read back from the database, so it shows
        // exactly what was saved for this order.
        $order = (new Order(get_db_connection()))->findWithItems($orderId);
        if ($order === null) {
            $this->redirect('home');
            return;
        }

        $lines = array();
        foreach ($order['items'] as $item) {
            $lines[] = array(
                'product'  => array('product_name' => $item['product_name'], 'price' => $item['unit_price']),
                'quantity' => (int) $item['quantity'],
                'subtotal' => (float) $item['line_total'],
            );
        }

        $this->render('checkout/confirm', array(
            'pageTitle' => 'Confirmation - Hue U Xchange',
            'name'      => $order['full_name'],
            'signature' => (string) $order['energy_signature'],
            'reference' => $order['order_reference'],
            'lines'     => $lines,
            'total'     => (float) $order['order_total'],
        ));
    }

    /** Server-side validation for the initiation form. */
    private static function validate(array $values)
    {
        $errors = array();

        if ($values['name'] === '') {
            $errors['name'] = 'Name is required.';
        } elseif (mb_strlen($values['name']) > 120) {
            $errors['name'] = 'Name must be 120 characters or fewer.';
        }

        if ($values['email'] === '') {
            $errors['email'] = 'Email is required.';
        } elseif (mb_strlen($values['email']) > 180) {
            $errors['email'] = 'Email must be 180 characters or fewer.';
        } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address, such as name@example.com.';
        }

        // Energy Signature is optional; if one is sent it must be a known value.
        if ($values['signature'] !== '' && !in_array($values['signature'], self::SIGNATURES, true)) {
            $errors['signature'] = 'That Energy Signature is not a recognized option.';
        }

        return $errors;
    }

    /**
     * Loads the cart with trusted database prices. An empty cart (or a
     * cart whose items are no longer active) blocks checkout.
     */
    private function loadCartOrRedirect()
    {
        if (Cart::isEmpty()) {
            Flash::set('error', 'Your cart is empty. Add an offering before checking out.');
            $this->redirect('cart');
        }

        try {
            $cart = Cart::contents(get_db_connection());
        } catch (\Throwable $e) {
            error_log('Checkout could not load cart contents: ' . $e->getMessage());
            $cart = array('lines' => array(), 'catalog_error' => true);
        }

        if (!empty($cart['catalog_error'])) {
            Flash::set('error', 'Checkout could not be completed right now. Please try again shortly.');
            $this->redirect('cart');
        }
        if (empty($cart['lines'])) {
            Flash::set('error', 'Your cart is empty. Add an offering before checking out.');
            $this->redirect('cart');
        }

        return $cart;
    }
}
