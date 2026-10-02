-- Hue U Xchange database export
-- Creates the hue_u_xchange database with four related tables:
--   products     the catalog (seeded with the five core offerings)
--   customers    one row per Lightbearer email address
--   orders       one completed checkout; each belongs to one customer
--   order_items  one line of an order; each references one product
-- Relationships: customers 1-to-many orders, orders 1-to-many order_items,
-- products 1-to-many order_items.
-- Safe to import repeatedly: does not drop or alter any unrelated database,
-- and uses "CREATE TABLE IF NOT EXISTS" plus a guarded seed so it will not
-- duplicate rows if the file is imported more than once.
-- Import through phpMyAdmin: Import tab -> choose this file -> Go.

CREATE DATABASE IF NOT EXISTS `hue_u_xchange`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `hue_u_xchange`;

CREATE TABLE IF NOT EXISTS `products` (
  `product_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_name` VARCHAR(120) NOT NULL,
  `symbolic_description` TEXT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `image_reference` VARCHAR(255) NOT NULL DEFAULT 'images/placeholder.png',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `display_order` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`product_id`),
  UNIQUE KEY `uniq_product_name` (`product_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed the five offerings using the names, prices, and descriptions already
-- present in the application (offerings.php / cart.php). INSERT ... ON
-- DUPLICATE KEY UPDATE keeps this file safely re-importable.

INSERT INTO `products`
  (`product_name`, `symbolic_description`, `price`, `image_reference`, `is_active`, `display_order`)
VALUES
  ('Divine Hoodie', 'Wrap yourself in warmth and worth.', 44.00, 'images/divine-hoodie.png', 1, 1),
  ('Aura Oils', 'Align your frequency with intention.', 22.00, 'images/aura-oils.png', 1, 2),
  ('Ritual Kit', 'Tools for transformation.', 33.00, 'images/ritual-kit.png', 1, 3),
  ('Music EP', 'Sonic guidance from the Lightbearer archives.', 11.00, 'images/music-ep.png', 1, 4),
  ('Access Code', 'Unlock inner sanctums of self-awareness.', 55.00, 'images/access-code.png', 1, 5)
ON DUPLICATE KEY UPDATE
  `symbolic_description` = VALUES(`symbolic_description`),
  `price` = VALUES(`price`),
  `image_reference` = VALUES(`image_reference`),
  `is_active` = VALUES(`is_active`),
  `display_order` = VALUES(`display_order`);

-- A customer is identified by email address. Checkout reuses the existing
-- row for a returning email, so one customer can have many orders.
CREATE TABLE IF NOT EXISTS `customers` (
  `customer_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(180) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `uniq_customer_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- One completed checkout. No payment is collected; order_total is the sum
-- of its order_items line totals, calculated from trusted product prices.
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_reference` VARCHAR(12) NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `energy_signature` ENUM('Flame', 'Wave', 'Stone') NULL DEFAULT NULL,
  `order_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `order_status` VARCHAR(20) NOT NULL DEFAULT 'confirmed',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `uniq_order_reference` (`order_reference`),
  KEY `idx_orders_customer` (`customer_id`),
  CONSTRAINT `fk_orders_customer`
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `chk_orders_total` CHECK (`order_total` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- One line of an order. unit_price is copied from products at checkout so
-- later price edits do not change the history of a completed order.
CREATE TABLE IF NOT EXISTS `order_items` (
  `order_item_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `quantity` SMALLINT UNSIGNED NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `line_total` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`order_item_id`),
  UNIQUE KEY `uniq_order_product` (`order_id`, `product_id`),
  KEY `idx_order_items_product` (`product_id`),
  CONSTRAINT `fk_order_items_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_order_items_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `chk_order_items_quantity` CHECK (`quantity` BETWEEN 1 AND 25),
  CONSTRAINT `chk_order_items_price` CHECK (`unit_price` >= 0 AND `line_total` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
