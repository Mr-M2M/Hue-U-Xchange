<?php
namespace App\Models;

use PDO;
use PDOException;
use RuntimeException;

/** Thrown when a create/update would violate the unique product-name constraint. */
class DuplicateProductException extends RuntimeException
{
}

/**
 * Hue U Xchange - Product model.
 *
 * Owns every product query and write. All statements are prepared with
 * bound parameters - no user-supplied value is ever concatenated into a
 * SQL string. This class accepts already-validated values from
 * controllers, returns structured arrays, never generates HTML, never
 * redirects, and never reads $_GET/$_POST directly.
 */
class Product
{
    /** @var PDO */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Validate raw form input for a product. Returns [cleanValues, errors].
     * $errors is empty when validation passes. Cleaned values are always
     * returned (even on failure) so a form can safely repopulate itself.
     * This is pure validation logic - no database access - so it is a
     * static helper controllers can call before touching the model.
     */
    public static function validateInput(array $input)
    {
        $errors = [];
        $clean = [];

        $name = trim((string) (isset($input['product_name']) ? $input['product_name'] : ''));
        $clean['product_name'] = $name;
        if ($name === '') {
            $errors['product_name'] = 'Product name is required.';
        } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
            $errors['product_name'] = 'Product name must be between 2 and 120 characters.';
        }

        $desc = trim((string) (isset($input['symbolic_description']) ? $input['symbolic_description'] : ''));
        $clean['symbolic_description'] = $desc;
        if ($desc === '') {
            $errors['symbolic_description'] = 'Symbolic description is required.';
        } elseif (mb_strlen($desc) < 2 || mb_strlen($desc) > 2000) {
            $errors['symbolic_description'] = 'Description must be between 2 and 2000 characters.';
        }

        $priceRaw = trim((string) (isset($input['price']) ? $input['price'] : ''));
        $clean['price'] = $priceRaw;
        if ($priceRaw === '' || !is_numeric($priceRaw)) {
            $errors['price'] = 'Price must be a number (e.g. 24.99).';
        } else {
            $price = (float) $priceRaw;
            if ($price < 0) {
                $errors['price'] = 'Price cannot be negative.';
            } elseif ($price > 100000) {
                $errors['price'] = 'Price must be less than 100,000.';
            } else {
                $clean['price'] = round($price, 2);
            }
        }

        $image = trim((string) (isset($input['image_reference']) ? $input['image_reference'] : ''));
        if ($image === '') {
            $image = 'images/placeholder.png';
        }
        $clean['image_reference'] = $image;
        // strpos(...) !== false is used instead of str_contains() because
        // str_contains() requires PHP 8.0+ and this application targets
        // PHP 7.4.
        if (!preg_match('#^[A-Za-z0-9_\-./]+$#', $image) || strpos($image, '..') !== false) {
            $errors['image_reference'] = 'Image reference may only contain letters, numbers, dashes, underscores, dots, and slashes.';
        }

        $activeRaw = (string) (isset($input['is_active']) ? $input['is_active'] : '1');
        if (!in_array($activeRaw, array('0', '1'), true)) {
            $errors['is_active'] = 'Active status must be Active or Inactive.';
            $clean['is_active'] = 1;
        } else {
            $clean['is_active'] = (int) $activeRaw;
        }

        $orderRaw = trim((string) (isset($input['display_order']) ? $input['display_order'] : '0'));
        $clean['display_order'] = $orderRaw;
        if ($orderRaw === '' || !ctype_digit($orderRaw)) {
            $errors['display_order'] = 'Display order must be a whole number of 0 or more.';
        } else {
            $clean['display_order'] = (int) $orderRaw;
        }

        return array($clean, $errors);
    }

    /** Active products only, in catalog display order. For the public Offerings page. */
    public function getActive()
    {
        $stmt = $this->pdo->prepare(
            'SELECT product_id, product_name, symbolic_description, price, image_reference, display_order
             FROM products
             WHERE is_active = 1
             ORDER BY display_order ASC, product_name ASC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Every product (active and inactive), for the management list. */
    public function getAll()
    {
        $stmt = $this->pdo->prepare(
            'SELECT product_id, product_name, symbolic_description, price, image_reference,
                    is_active, display_order, created_at, updated_at
             FROM products
             ORDER BY display_order ASC, product_name ASC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** One product by ID, or null if it does not exist. */
    public function find($id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM products WHERE product_id = :id LIMIT 1');
        $stmt->execute(array(':id' => (int) $id));
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /** True if a product with this ID exists. */
    public function exists($id)
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM products WHERE product_id = :id LIMIT 1');
        $stmt->execute(array(':id' => (int) $id));
        return (bool) $stmt->fetchColumn();
    }

    /** Active products matching a set of IDs, keyed by product_id. For validating cart contents. */
    public function getActiveByIds(array $ids)
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if ($ids === array()) {
            return array();
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT product_id, product_name, price FROM products WHERE is_active = 1 AND product_id IN ($placeholders)"
        );
        $stmt->execute($ids);
        $byId = array();
        foreach ($stmt->fetchAll() as $row) {
            $byId[(int) $row['product_id']] = $row;
        }
        return $byId;
    }

    /**
     * Insert a new product from already-validated, cleaned data.
     * Returns the new product_id. Throws DuplicateProductException if the
     * product name is already in use (unique constraint), or PDOException
     * for any other database failure.
     */
    public function create(array $data)
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO products
                (product_name, symbolic_description, price, image_reference, is_active, display_order)
             VALUES
                (:product_name, :symbolic_description, :price, :image_reference, :is_active, :display_order)'
        );
        try {
            $stmt->execute(array(
                ':product_name'         => $data['product_name'],
                ':symbolic_description' => $data['symbolic_description'],
                ':price'                => $data['price'],
                ':image_reference'      => $data['image_reference'],
                ':is_active'            => $data['is_active'],
                ':display_order'        => $data['display_order'],
            ));
        } catch (PDOException $e) {
            if ((int) $e->errorInfo[1] === 1062) {
                throw new DuplicateProductException('A product named "' . $data['product_name'] . '" already exists.');
            }
            throw $e;
        }
        return (int) $this->pdo->lastInsertId();
    }

    /** Update an existing product from already-validated, cleaned data. Returns true if a row changed. */
    public function update($id, array $data)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE products SET
                product_name = :product_name,
                symbolic_description = :symbolic_description,
                price = :price,
                image_reference = :image_reference,
                is_active = :is_active,
                display_order = :display_order
             WHERE product_id = :id'
        );
        try {
            $stmt->execute(array(
                ':product_name'         => $data['product_name'],
                ':symbolic_description' => $data['symbolic_description'],
                ':price'                => $data['price'],
                ':image_reference'      => $data['image_reference'],
                ':is_active'            => $data['is_active'],
                ':display_order'        => $data['display_order'],
                ':id'                   => (int) $id,
            ));
        } catch (PDOException $e) {
            if ((int) $e->errorInfo[1] === 1062) {
                throw new DuplicateProductException('A product named "' . $data['product_name'] . '" already exists.');
            }
            throw $e;
        }
        return $stmt->rowCount() > 0;
    }

    /**
     * Deactivate or reactivate a product (the safe, reversible alternative
     * to a permanent DELETE). Returns true if a row changed.
     */
    public function setActive($id, $active)
    {
        $stmt = $this->pdo->prepare('UPDATE products SET is_active = :active WHERE product_id = :id');
        $stmt->execute(array(':active' => $active ? 1 : 0, ':id' => (int) $id));
        return $stmt->rowCount() > 0;
    }
}
