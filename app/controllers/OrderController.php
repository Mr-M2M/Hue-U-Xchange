<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;

/**
 * Read-only order history for the product-management area. Shows the
 * orders saved by checkout together with their customer and item counts.
 */
class OrderController extends Controller
{
    public function index()
    {
        $orders = array();
        $loadError = false;
        try {
            $orders = (new Order(get_db_connection()))->recent(50);
        } catch (\Throwable $e) {
            error_log('Order history could not be loaded: ' . $e->getMessage());
            $loadError = true;
        }

        $this->render('orders/index', array(
            'pageTitle' => 'Order History - Hue U Xchange',
            'orders'    => $orders,
            'loadError' => $loadError,
        ));
    }
}
