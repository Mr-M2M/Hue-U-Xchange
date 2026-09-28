# Hue U Xchange

A PHP and MySQL web portal and symbolic storefront for the Shining Light Army. It sells offerings, music, apparel, and spiritual tools tied to Donny D and *The Curing Process*.

Author: Donavan McFadden

## Project Purpose

Hue U Xchange gives the Shining Light Army a home online. It is a storefront where each offering stands for a step in a Lightbearer's transformation, and a portal that tells the story behind it. On the technical side, the project shows the full cycle of building a database-backed PHP application: planning, database design, CRUD, MVC architecture, validation, testing, version control, and documentation.

## Core Features

- **Home, About, and The Curing Process pages.** Branded narrative content: the Shining Light Army, Donny D's artist biography, the figures of Yoo-U, the Lightbearer Oath, music from the story, and a Coming Soon section.
- **Database-backed Offerings catalog.** Shows only active products, sorted by display order, with prices formatted as currency.
- **Product management (CRUD).**
  - Create, read, and update products, with server-side validation.
  - Deactivate or reactivate products. The record is kept and can be restored.
  - Deactivation always goes through a confirmation step and a POST request.
- **Session-based shopping cart.**
  - Add, update, and remove items.
  - Products are checked against the database.
  - Quantities must be between 1 and 25 per item.
  - Prices always come from MySQL, never from the browser.
  - The cart persists across pages and refreshes.
- **Checkout.**
  - An order summary, name and email validation, and an optional Energy Signature (Flame, Wave, or Stone).
  - Protection against submitting the same order twice.
  - A one-time Certified Light Carrier confirmation page with a simulated order reference.
  - No payment is processed.
- **Safe error handling.** Visitors see a friendly message when the database is unavailable or a route does not exist. Technical details go only to the PHP error log.
- **Responsive, accessible interface.**
  - A skip link and visible keyboard focus.
  - A labelled control for every form field, and field-level errors written in text.
  - An active-navigation state and a cart item count.
  - Layouts for both mobile and desktop.

## Technology Used

- PHP 7.4 (all code avoids PHP 8-only syntax)
- MySQL / MariaDB with PDO and prepared statements
- Apache with `mod_rewrite` (XAMPP 7.4.11), or PHP's built-in server for quick testing
- HTML5, CSS3, and a small optional JavaScript file (`public/assets/js/app.js`) that only disables a submit button after a click
- phpMyAdmin for importing the database
- Git and GitHub for version control and releases
- Python 3 and `requests` for the optional HTTP regression script in `tests/`

## MVC Directory Overview

```
app/
  core/          Autoloader, Session, Flash, Url, Csrf, and the base Controller class
  models/        Product.php, Cart.php (database access and cart rules), Lore.php (story content)
  controllers/   HomeController, AboutController, LoreController, ProductController,
                 CartController, CheckoutController
  views/         Presentation only, grouped by feature, plus views/layouts/ for the
                 shared header, nav, and footer
config/          database.php (centralized PDO connection) and config.local.example.php
database/        hue_u_xchange.sql - importable schema + seed data (five core offerings)
docs/            mvc-architecture.md - request flow and responsibilities
public/          Web root: index.php (front controller), .htaccess, router.php,
                 assets/css, assets/js, images/
routes/          routes.php - the route-to-controller map
tests/           http_regression.py - automated HTTP regression checks
```

See [`docs/mvc-architecture.md`](docs/mvc-architecture.md) for a full walkthrough of how a request moves through the code.

## Local Prerequisites

- [XAMPP 7.4.11](https://www.apachefriends.org/) (Apache, PHP 7.4, MySQL/MariaDB, phpMyAdmin), or any PHP 7.4+ install with the `pdo_mysql` and `mbstring` extensions
- Git
- A modern browser, such as Chrome or Microsoft Edge

## XAMPP Setup

1. Install XAMPP and open the XAMPP Control Panel.
2. Click **Start** next to **Apache** and **MySQL**.
3. In Apache's `httpd.conf` (or your vhost), make sure the `htdocs` directory has `AllowOverride All`, so `public/.htaccess` is read. This step is only needed for pretty URLs; `index.php?route=...` always works.
4. Confirm that `extension=pdo_mysql` and `extension=mbstring` are enabled in `php.ini`. Both are enabled by default in XAMPP.

## Cloning the Repository

Clone the repository into your XAMPP `htdocs` folder:

```
cd C:\xampp\htdocs
git clone https://github.com/Mr-M2M/Hue-U-Xchange.git hue-u-xchange
```

On a Mac, the folder is `/Applications/XAMPP/htdocs`.

## Database Import

1. Open **phpMyAdmin** at `http://localhost/phpmyadmin`.
2. Click the **Import** tab.
3. Choose `database/hue_u_xchange.sql` and click **Go**.
4. Confirm that the `hue_u_xchange` database now has a `products` table with the five core offerings: Divine Hoodie ($44.00), Aura Oils ($22.00), Ritual Kit ($33.00), Music EP ($11.00), and Access Code ($55.00).

The import is safe to repeat. It uses `CREATE ... IF NOT EXISTS` and `INSERT ... ON DUPLICATE KEY UPDATE`, and it never drops or changes any other database.

## Configuration

1. Copy `config/config.local.example.php` to `config/config.local.php`.
2. Set your local MySQL host, port, database name, username, and password. XAMPP's default is user `root` with an empty password.
3. `config/config.local.php` is git-ignored. Never commit it.

As an alternative to the local file, you can set the environment variables `HUE_DB_HOST`, `HUE_DB_PORT`, `HUE_DB_NAME`, `HUE_DB_USER`, and `HUE_DB_PASS`.

## Running the Application

**With XAMPP:** visit `http://localhost/hue-u-xchange/public/index.php`. If `.htaccess` is active, `http://localhost/hue-u-xchange/public/` also works.

**With PHP's built-in server** (for quick testing), run this from the project root:

```
php -S 127.0.0.1:8000 -t public public/router.php
```

Then open `http://127.0.0.1:8000/`.

## Main Application Routes

| Route (`index.php?route=...`) | Purpose |
|---|---|
| *(empty)* or `home` | Home page |
| `about` | About Hue U Xchange |
| `lore` | The Curing Process: biography, story, music, Coming Soon |
| `offerings` | Public, database-backed catalog (active products only) |
| `products` | Product-management list (all products) |
| `products/create` | Add a product |
| `products/edit&id=N` | Edit a product |
| `products/delete&id=N` | Confirm and deactivate or reactivate a product (POST) |
| `cart` | View the cart |
| `cart/add`, `cart/update`, `cart/remove` | Cart actions (POST only) |
| `checkout` | Initiation (checkout) form with order summary |
| `checkout/submit` | Process checkout (POST only) |
| `confirm` | One-time Certified Light Carrier confirmation |

Any other route returns a 404 Not Found page.

## Testing Overview

Phase 4 testing combined an automated HTTP regression script, a real-browser walkthrough, and code inspection.

- **Automated regression.** `tests/http_regression.py` runs 59 checks against a running copy of the application. It covers:
  - Page loads, the database connection, and safe handling of a database failure.
  - Product create, read, update, and deactivate, plus all validation rules.
  - Cart rules and totals, and checkout validation, confirmation, and duplicate-submission control.
  - Navigation, 404 handling, CSRF refusal, XSS and SQL-injection inputs, and a scan for PHP warnings.
- **Browser walkthrough.** Headless Chromium at desktop (1366 px) and mobile (390 px) widths, including keyboard-only navigation.
- **Clean rebuild.** The database was dropped and rebuilt from `database/hue_u_xchange.sql`.
- **PHP 7.4 compatibility.** Checked with PHPCompatibility (`testVersion 7.4`).

To run the regression script:

```
python3 tests/http_regression.py --base http://127.0.0.1:8000 --mysql "mysql -uUSER -pPASS"
```

The full written results are in the *Hue U Xchange Application Test Description* document.

## Known Limitations

- Checkout is simulated. No payment is collected and orders are not saved to the database.
- Product management has no login. Anyone who can reach the site can open **Manage Products**, so the app is meant for local or classroom use, not public production.
- Deactivation hides a product rather than permanently deleting it. This is intentional, to preserve records.
- Phase 4 testing ran on PHP 8.5 with MariaDB 11.8 and headless Chromium in a Linux development environment. PHP 7.4 compatibility was checked with a static analysis tool, not by running PHP 7.4. The XAMPP 7.4.11 / Apache run and Microsoft Edge were not available in that environment.
- Product images are static files in `public/images/`. There is no image upload.

## Future Improvements

- Admin login and roles for product management
- Saving orders to an `orders` table, with order history
- Real payment integration through a hosted provider
- Product image upload with type and size validation
- Streaming previews for the Music EP tracks
- Book 2 story content and the Hopeful Camp mural series (see Coming Soon)

## Repository and Release Information

- Repository: https://github.com/Mr-M2M/Hue-U-Xchange
- Releases:
  - [Phase #2](https://github.com/Mr-M2M/Hue-U-Xchange/releases/tag/Phase-2): database foundation
  - [Database Support Phase](https://github.com/Mr-M2M/Hue-U-Xchange/releases/tag/Phase-3-Database-Support): CRUD
  - [Phase #3](https://github.com/Mr-M2M/Hue-U-Xchange/releases/tag/Phase-3): MVC
  - [Phase #4](https://github.com/Mr-M2M/Hue-U-Xchange/releases/tag/Phase-4): finalized application
- Development branches:
  - `week-2-database-framework`
  - `week-3-database-support`
  - `week-4-mvc-architecture`
  - `week-5-finalize-application`

## Project Summary

Hue U Xchange is a PHP and MySQL web application built as a symbolic storefront and community portal for the Shining Light Army, inspired by Donny D and *The Curing Process*. The final application uses the Model-View-Controller pattern to separate data, request processing, and presentation, with one front controller and a route map.

It includes:

- A database-backed product catalog, and product-management operations (create, read, update, and deactivate or reactivate) with server-side validation.
- A session-based cart that uses trusted database prices.
- A validated checkout with an order summary, duplicate-submission protection, and a Certified Light Carrier confirmation.
- A story page with the artist biography, music, and Coming Soon content.
- A responsive, keyboard-accessible interface built from reusable layouts and components.

The project demonstrates planning, database design, PHP development, CRUD operations, MVC architecture, testing, version control, and technical documentation.
