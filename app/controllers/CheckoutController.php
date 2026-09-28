<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Models\Cart;

/**
 * Checkout flow: initiation form -> server-side validation -> simulated
 * order -> one-time Certified Light Carrier confirmation. No payment is
 * processed; the order exists only in the session.
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

        // Successful processing: snapshot the order (trusted MySQL prices
        // resolved by Cart::contents), rotate the form token so the same
        // form cannot be processed a second time, and only then clear the
        // cart.
        $_SESSION['checkout_confirmation'] = array(
            'name'      => $values['name'],
            'signature' => $values['signature'],
            'reference' => 'SLA-' . strtoupper(bin2hex(random_bytes(3))),
            'lines'     => $cart['lines'],
            'total'     => $cart['total'],
        );
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

        $order = $_SESSION['checkout_confirmation'];
        unset($_SESSION['checkout_confirmation']);

        $this->render('checkout/confirm', array(
            'pageTitle' => 'Confirmation - Hue U Xchange',
            'name'      => $order['name'],
            'signature' => isset($order['signature']) ? $order['signature'] : '',
            'reference' => isset($order['reference']) ? $order['reference'] : '',
            'lines'     => $order['lines'],
            'total'     => $order['total'],
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
