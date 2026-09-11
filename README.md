# Hue U Xchange

A PHP web portal and symbolic storefront for the Shining Light Army — offerings,
music, apparel, and spiritual tools connected to Donny D and The Curing Process.

## Project structure

```
config/      Centralized configuration and the PDO database connection
database/    hue_u_xchange.sql - importable database export (schema + seed data)
includes/    Shared header, navigation, footer, and session components
css/         Site stylesheet
images/      Product and branding images
docs/        Project documentation (development records, project plan)
*.php        Public application pages (index, offerings, cart, checkout, confirm)
about.php    About page
```

## Local setup (XAMPP)

1. Install [XAMPP](https://www.apachefriends.org/) and start **Apache** and **MySQL**
   from the XAMPP control panel.
2. Copy this repository into your XAMPP `htdocs` folder, e.g.
   `C:\xampp\htdocs\hue-u-xchange` (Windows) or `/Applications/XAMPP/htdocs/hue-u-xchange` (Mac).
3. Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
4. Click **Import**, choose `database/hue_u_xchange.sql`, and click **Go**.
   This creates the `hue_u_xchange` database and the `products` table, and
   seeds the five offerings (Divine Hoodie, Aura Oils, Ritual Kit, Music EP,
   Access Code).
5. Copy `config/config.local.example.php` to `config/config.local.php` and set
   your local MySQL username/password (XAMPP defaults to user `root` with an
   empty password — the example file already reflects that default).
   `config/config.local.php` is git-ignored and must never be committed.
6. Visit `http://localhost/hue-u-xchange/index.php` in your browser.

### Alternative: PHP's built-in server

For quick local testing without the full XAMPP stack, from the project root:

```
php -S 127.0.0.1:8000
```

Then visit `http://127.0.0.1:8000/index.php`. You still need a running MySQL/MariaDB
server and the imported database as described above.

## Configuration

Database credentials are never hard-coded in application pages. `config/database.php`
reads settings from environment variables (`HUE_DB_HOST`, `HUE_DB_PORT`, `HUE_DB_NAME`,
`HUE_DB_USER`, `HUE_DB_PASS`) when they are set, and otherwise falls back to
`config/config.local.php` for local development. Only `config/config.local.example.php`
(no real credentials) is committed to GitHub.

## Pages

| Page | File | Description |
| --- | --- | --- |
| Home | `index.php` | Welcome and introduction |
| About | `about.php` | Mission and background |
| Offerings | `offerings.php` | Database-backed product catalog with Add to Cart |
| Cart | `cart.php` | Session-based shopping cart |
| Checkout | `checkout.php` | Initiation form (no real payment processing) |
| Confirmation | `confirm.php` | Order confirmation |

## Database

See `database/hue_u_xchange.sql` for the full schema and seed data. The `products`
table stores product name, symbolic description, price, image reference, active
status, display order, and created/updated timestamps, using `utf8mb4` throughout.
