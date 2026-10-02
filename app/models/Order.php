<?php
namespace App\Models;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Hue U Xchange - Order model.
 *
 * Persists a completed checkout across the three related tables:
 * customers (one per email) -> orders (one per checkout) -> order_items
 * (one per product line). Every write happens inside one transaction, so
 * an order is either saved completely or not at all. Like the other
 * models, this class receives already-validated values from a
 * controller, uses prepared statements only, and never generates HTML.
 */
class Order
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Save an order for the given customer and resolved cart lines.
     *
     * $lines must come from Cart::contents(), which prices every line from
     * the products table, so no browser-supplied price can reach this
     * method. Line totals and the order total are recalculated here from
     * those trusted unit prices.
     *
     * Returns ['order_id' => int, 'reference' => string].
     */
    public function place($name, $email, $signature, array $lines)
    {
        if (empty($lines)) {
            throw new RuntimeException('An order must contain at least one item.');
        }

        $this->pdo->beginTransaction();
        try {
            $customerId = $this->saveCustomer($name, $email);

            $total = 0.0;
            foreach ($lines as $line) {
                $total += round((float) $line['product']['price'] * (int) $line['quantity'], 2);
            }

            $reference = $this->insertOrder($customerId, $signature, $total);
            $orderId = (int) $this->pdo->lastInsertId();

            $item = $this->pdo->prepare(
                'INSERT INTO order_items (order_id, product_id, quantity, unit_price, line_total)
                 VALUES (:order_id, :product_id, :quantity, :unit_price, :line_total)'
            );
            foreach ($lines as $line) {
                $unitPrice = round((float) $line['product']['price'], 2);
                $quantity = (int) $line['quantity'];
                $item->execute(array(
                    ':order_id'   => $orderId,
                    ':product_id' => (int) $line['product']['product_id'],
                    ':quantity'   => $quantity,
                    ':unit_price' => $unitPrice,
                    ':line_total' => round($unitPrice * $quantity, 2),
                ));
            }

            $this->pdo->commit();
            return array('order_id' => $orderId, 'reference' => $reference);
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * One order with its customer and line items (joined back to products
     * for the current product name), or null if it does not exist.
     */
    public function findWithItems($orderId)
    {
        $stmt = $this->pdo->prepare(
            'SELECT o.order_id, o.order_reference, o.energy_signature, o.order_total, o.created_at,
                    c.customer_id, c.full_name, c.email
             FROM orders o
             INNER JOIN customers c ON c.customer_id = o.customer_id
             WHERE o.order_id = :id
             LIMIT 1'
        );
        $stmt->execute(array(':id' => (int) $orderId));
        $order = $stmt->fetch();
        if ($order === false) {
            return null;
        }

        $items = $this->pdo->prepare(
            'SELECT oi.product_id, p.product_name, oi.quantity, oi.unit_price, oi.line_total
             FROM order_items oi
             INNER JOIN products p ON p.product_id = oi.product_id
             WHERE oi.order_id = :id
             ORDER BY oi.order_item_id ASC'
        );
        $items->execute(array(':id' => (int) $orderId));
        $order['items'] = $items->fetchAll();

        return $order;
    }

    /** Recent orders with customer name and item count, newest first. */
    public function recent($limit = 50)
    {
        $stmt = $this->pdo->prepare(
            'SELECT o.order_id, o.order_reference, o.order_total, o.energy_signature, o.created_at,
                    c.full_name, COUNT(oi.order_item_id) AS line_count,
                    COALESCE(SUM(oi.quantity), 0) AS item_count
             FROM orders o
             INNER JOIN customers c ON c.customer_id = o.customer_id
             LEFT JOIN order_items oi ON oi.order_id = o.order_id
             GROUP BY o.order_id, o.order_reference, o.order_total, o.energy_signature,
                      o.created_at, c.full_name
             ORDER BY o.created_at DESC, o.order_id DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Insert the customer, or reuse the existing row for a returning email
     * (updating the name). LAST_INSERT_ID(customer_id) makes lastInsertId()
     * return the existing id in the duplicate case.
     */
    private function saveCustomer($name, $email)
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO customers (full_name, email) VALUES (:name, :email)
             ON DUPLICATE KEY UPDATE full_name = VALUES(full_name),
                                     customer_id = LAST_INSERT_ID(customer_id)'
        );
        $stmt->execute(array(':name' => $name, ':email' => mb_strtolower($email)));
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Insert the order row with a unique, human-readable reference such as
     * SLA-3F9A1C. A reference collision is retried a few times.
     */
    private function insertOrder($customerId, $signature, $total)
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO orders (order_reference, customer_id, energy_signature, order_total)
             VALUES (:reference, :customer_id, :signature, :total)'
        );

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $reference = 'SLA-' . strtoupper(bin2hex(random_bytes(3)));
            try {
                $stmt->execute(array(
                    ':reference'   => $reference,
                    ':customer_id' => $customerId,
                    ':signature'   => $signature !== '' ? $signature : null,
                    ':total'       => round($total, 2),
                ));
                return $reference;
            } catch (PDOException $e) {
                // 23000 = integrity constraint; retry only a duplicate reference.
                if ($e->getCode() !== '23000' || strpos($e->getMessage(), 'uniq_order_reference') === false) {
                    throw $e;
                }
            }
        }

        throw new RuntimeException('Could not generate a unique order reference.');
    }
}
