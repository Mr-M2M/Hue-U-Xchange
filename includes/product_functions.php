<?php
/**
 * Hue U Xchange - reusable product data-access functions.
 *
 * Every product query and write in the application goes through this
 * file so the SQL for reading, creating, updating, and deactivating a
 * product only exists in one place. All statements are prepared with
 * bound parameters - no user-supplied value is ever concatenated into a
 * SQL string.
 *
 * Usage from any page:
 *   require_once __DIR__ . '/../config/database.php';
 *   require_once __DIR__ . '/../includes/product_functions.php';
 *   $pdo = get_db_connection();
 *   $products = hue_get_active_products($pdo);
 */

/**
 * Validate raw form input for a product. Returns [cleanValues, errors].
 * $errors is empty when validation passes. Cleaned values are always
 * returned (even on failure) so a form can safely repopulate itself.
 */
function hue_validate_product_input(array $input): array
{
    $errors = [];
    $clean = [];

    $name = trim((string) ($input['product_name'] ?? ''));
    $clean['product_name'] = $name;
    if ($name === '') {
        $errors['product_name'] = 'Product name is required.';
    } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
        $errors['product_name'] = 'Product name must be between 2 and 120 characters.';
    }

    $desc = trim((string) ($input['symbolic_description'] ?? ''));
    $clean['symbolic_description'] = $desc;
    if ($desc === '') {
        $errors['symbolic_description'] = 'Symbolic description is required.';
    } elseif (mb_strlen($desc) < 2 || mb_strlen($desc) > 2000) {
        $errors['symbolic_description'] = 'Description must be between 2 and 2000 characters.';
    }

    $priceRaw = trim((string) ($input['price'] ?? ''));
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

    $image = trim((string) ($input['image_reference'] ?? ''));
    if ($image === '') {
        $image = 'images/placeholder.png';
    }
    $clean['image_reference'] = $image;
    if (!preg_match('#^[A-Za-z0-9_\-./]+$#', $image) || str_contains($image, '..')) {
        $errors['image_reference'] = 'Image reference may only contain letters, numbers, dashes, underscores, dots, and slashes.';
    }

    $activeRaw = (string) ($input['is_active'] ?? '1');
    if (!in_array($activeRaw, ['0', '1'], true)) {
        $errors['is_active'] = 'Active status must be Active or Inactive.';
        $clean['is_active'] = 1;
    } else {
        $clean['is_active'] = (int) $activeRaw;
    }

    $orderRaw = trim((string) ($input['display_order'] ?? '0'));
    $clean['display_order'] = $orderRaw;
    if ($orderRaw === '' || !ctype_digit($orderRaw)) {
        $errors['display_order'] = 'Display order must be a whole number of 0 or more.';
    } else {
        $clean['display_order'] = (int) $orderRaw;
    }

    return [$clean, $errors];
}

/** Active products only, in catalog display order. For the public Offerings page. */
function hue_get_active_products(PDO $pdo): array
{
    $stmt = $pdo->prepare(
        'SELECT product_id, product_name, symbolic_description, price, image_reference, display_order
         FROM products
         WHERE is_active = 1
         ORDER BY display_order ASC, product_name ASC'
    );
    $stmt->execute();
    return $stmt->fetchAll();
}

/** Every product (active and inactive), for the management list. */
function hue_get_all_products(PDO $pdo): array
{
    $stmt = $pdo->prepare(
        'SELECT product_id, product_name, symbolic_description, price, image_reference,
                is_active, display_order, created_at, updated_at
         FROM products
         ORDER BY display_order ASC, product_name ASC'
    );
    $stmt->execute();
    return $stmt->fetchAll();
}

/** One product by ID, or null if it does not exist. */
function hue_get_product(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM products WHERE product_id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    return $row !== false ? $row : null;
}

/** True if a product with this ID exists. */
function hue_product_exists(PDO $pdo, int $id): bool
{
    $stmt = $pdo->prepare('SELECT 1 FROM products WHERE product_id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    return (bool) $stmt->fetchColumn();
}

/** Active products matching a set of IDs, keyed by product_id. For validating cart contents. */
function hue_get_active_products_by_ids(PDO $pdo, array $ids): array
{
    $ids = array_values(array_unique(array_map('intval', $ids)));
    if ($ids === []) {
        return [];
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare(
        "SELECT product_id, product_name, price FROM products WHERE is_active = 1 AND product_id IN ($placeholders)"
    );
    $stmt->execute($ids);
    $byId = [];
    foreach ($stmt->fetchAll() as $row) {
        $byId[(int) $row['product_id']] = $row;
    }
    return $byId;
}

/**
 * Insert a new product from already-validated, cleaned data.
 * Returns the new product_id. Throws HueDuplicateProductException if the
 * product name is already in use (unique constraint), or PDOException
 * for any other database failure.
 */
function hue_create_product(PDO $pdo, array $data): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO products
            (product_name, symbolic_description, price, image_reference, is_active, display_order)
         VALUES
            (:product_name, :symbolic_description, :price, :image_reference, :is_active, :display_order)'
    );
    try {
        $stmt->execute([
            ':product_name'         => $data['product_name'],
            ':symbolic_description' => $data['symbolic_description'],
            ':price'                => $data['price'],
            ':image_reference'      => $data['image_reference'],
            ':is_active'            => $data['is_active'],
            ':display_order'        => $data['display_order'],
        ]);
    } catch (PDOException $e) {
        if ((int) $e->errorInfo[1] === 1062) {
            throw new HueDuplicateProductException('A product named "' . $data['product_name'] . '" already exists.');
        }
        throw $e;
    }
    return (int) $pdo->lastInsertId();
}

/** Update an existing product from already-validated, cleaned data. Returns true if a row changed. */
function hue_update_product(PDO $pdo, int $id, array $data): bool
{
    $stmt = $pdo->prepare(
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
        $stmt->execute([
            ':product_name'         => $data['product_name'],
            ':symbolic_description' => $data['symbolic_description'],
            ':price'                => $data['price'],
            ':image_reference'      => $data['image_reference'],
            ':is_active'            => $data['is_active'],
            ':display_order'        => $data['display_order'],
            ':id'                   => $id,
        ]);
    } catch (PDOException $e) {
        if ((int) $e->errorInfo[1] === 1062) {
            throw new HueDuplicateProductException('A product named "' . $data['product_name'] . '" already exists.');
        }
        throw $e;
    }
    return $stmt->rowCount() > 0;
}

/**
 * Deactivate or reactivate a product (the safe, reversible alternative to
 * a permanent DELETE). Returns true if a row changed.
 */
function hue_set_product_active(PDO $pdo, int $id, bool $active): bool
{
    $stmt = $pdo->prepare('UPDATE products SET is_active = :active WHERE product_id = :id');
    $stmt->execute([':active' => $active ? 1 : 0, ':id' => $id]);
    return $stmt->rowCount() > 0;
}

class HueDuplicateProductException extends RuntimeException
{
}
