-- Hue U Xchange database export
-- Creates the database and products table and seeds the five offerings.
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
