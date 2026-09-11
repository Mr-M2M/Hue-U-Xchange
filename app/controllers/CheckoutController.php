<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Models\Cart;

/**
 * Handles the checkout form and the "Certified Light Carrier"
 * confirmation. Validates submitted values, prevents an empty-cart
 * checkout, generates a simulated order summary from trusted cart/
 * database data, and uses Post-Redirect-Get so refreshing the
 * confirmation page never repeats the order.
 */
class CheckoutController extends Controller
{
    const SIGNATURES = array('Flame', 'Wave', 'Stone');

    /** GET /checkout - show the initiation form. */
    public function index()
    {
        if (Cart::isEmpty()) {
            Flash::set('error', 'Your cart is empty. Add an offering before checking out.');
            $this->redirect('cart');
        }

        $this->render('checkout/form', array(
            'pageTitle' => 'Checkout - Hue U Xchange',
            'values'    => array('name' => '', 'email' => '', 'signature' => ''),
            'errors'    => array(),
        ));
    }

    /** POST /checkout/submit - validate, generate the order summary, clear the cart. */
    public function submit()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('checkout');
        }

        if (Cart::isEmpty()) {
            Flash::set('error', 'Your cart is empty. Add an offering before checking out.');
            $this->redirect('cart');
        }

        $values = array(
            'name'      => trim((string) (isset($_POST['name']) ? $_POST['name'] : '')),
            'email'     => trim((string) (isset($_POST['email']) ? $_POST['email'] : '')),
            'signature' => trim((string) (isset($_POST['signature']) ? $_POST['signature'] : '')),
        );
        $errors = array();

        if ($values['name'] === '') {
            $errors['name'] = 'Name is required.';
        } elseif (mb_strlen($values['name']) > 120) {
            $errors['name'] = 'Name must be 120 characters or fewer.';
        }

        if ($values['email'] === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        } elseif (mb_strlen($values['email']) > 180) {
            $errors['email'] = 'Email must be 180 characters or fewer.';
        }

        if ($values['signature'] === '' || !in_array($values['signature'], self::SIGNATURES, true)) {
            $errors['signature'] = 'Choose an Energy Signature.';
        }

        if (!empty($errors)) {
            // Preserve the submitted (safe) values so the form does not
            // ask the visitor to retype everything after a validation error.
            $this->render('checkout/form', array(
                'pageTitle' => 'Checkout - Hue U Xchange',
                'values'    => $values,
                'errors'    => $errors,
            ));
            return;
        }

        try {
            $pdo = get_db_connection();
            $cart = Cart::contents($pdo);
        } catch (\Throwable $e) {
            error_log('Checkout could not load cart contents: ' . $e->getMessage());
            Flash::set('error', 'Checkout could not be completed right now. Please try again shortly.');
            $this->redirect('cart');
            return;
        }

        if (empty($cart['lines'])) {
            Flash::set('error', 'Your cart is empty. Add an offering before checking out.');
            $this->redirect('cart');
            return;
        }

        // Simulated order summary - no real payment processing.
        $_SESSION['checkout_confirmation'] = array(
            'name'  => $values['name'],
            'lines' => $cart['lines'],
            'total' => $cart['total'],
        );

        Cart::clear();

        // Post-Redirect-Get: the confirmation is rendered on the next
        // GET request, so refreshing it never resubmits the order.
        $this->redirect('confirm');
    }

    /** GET /confirm - display the confirmation once, then clear it. */
    public function confirmation()
    {
        if (!isset($_SESSION['checkout_confirmation'])) {
            // No pending confirmation (direct visit, refresh after the
            // first view, or replay) - send the visitor home safely
            // instead of fabricating an order.
            $this->redirect('home');
            return;
        }

        $order = $_SESSION['checkout_confirmation'];
        unset($_SESSION['checkout_confirmation']);

        $this->render('checkout/confirm', array(
            'pageTitle' => 'Confirmation - Hue U Xchange',
            'name'      => $order['name'],
            'lines'     => $order['lines'],
            'total'     => $order['total'],
        ));
    }
}
