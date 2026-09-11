# Hue U Xchange

A PHP web portal and symbolic storefront for the Shining Light Army — offerings,
music, apparel, and spiritual tools connected to Donny D and The Curing Process.

As of Phase 3, the application uses a Model-View-Controller architecture — see
[`docs/mvc-architecture.md`](docs/mvc-architecture.md) for the full request-flow
explanation.

## Project structure

```
app/
  core/          Autoloader, Session, Flash, Url, and the base Controller class
  models/        Product.php, Cart.php — all database access and cart rules
  controllers/   HomeController, AboutController, ProductController,
                 CartController, CheckoutController
  views/         Presentation only, grouped by feature, plus views/layouts/
                 for the shared header, nav, and footer
config/          Centralized configuration and the PDO database connection
database/        hue_u_xchange.sql — importable database export (schema + seed data)
docs/            Project documentation (architecture notes, development records)
public/          Web root — index.php (front controller), .htaccess, router.php
                 (for PHP's built-in server), assets/css/, images/
routes/          routes.php — the route-to-controller map
```

## Local setup (XAMPP)

1. Install [XAMPP](https://www.apachefriends.org/) and start **Apache** and **MySQL**
   from the XAMPP control panel.
2. Copy this repository into your XAMPP `htdocs` folder, e.g.
   `C:\xampp\htdocs\hue-u-xchange` (Windows) or `/Applications/XAMPP/htdocs/hue-u-xchange` (Mac).
3. In your Apache vhost/`httpd.conf`, make sure the `htdocs` directory (or this
   project's directory) has `AllowOverride All` so `public/.htaccess` is read.
4. Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
5. Click **Import**, choose `database/hue_u_xchange.sql`, and click **Go**.
   This creates the `hue_u_xchange` database and the `products` table, and
   seeds the five offerings (Divine Hoodie, Aura Oils, Ritual Kit, Music EP,
   Access Code).
6. Copy `config/config.local.example.php` to `config/config.local.php` and set
   your local MySQL username/password (XAMPP defaults to user `root` with an
   empty password — the example file already reflects that default).
   `config/config.local.php` is git-ignored and must never be committed.
7. Visit `http://localhost/hue-u-xchange/public/index.php` in your browser
   (or `http://localhost/hue-u-xchange/public/` if `AllowOverride All` and
   `.htaccess` are active, since every route resolves to the same front
   controller either way).

### Alternative: PHP's built-in server

For quick local testing without the full XAMPP stack, from the project root:

```
php -S 127.0.0.1:8000 -t public public/router.php
```

Then visit `http://127.0.0.1:8000/` — `public/router.php` exists only to give
PHP's built-in server (which ignores `.htaccess`) the same routing behavior
Apache gets from `public/.htaccess`.

## Routing

Every page is served by the single front controller, `public/index.php`, using
a route name: `index.php?route=products` always works, under XAMPP, Apache, or
PHP's built-in server, with no configuration. When Apache's `mod_rewrite` is
active (via `public/.htaccess`), the same routes are also reachable as pretty
paths, e.g. `/products`, `/cart`, `/checkout`. See `routes/routes.php` for the
full route map and `docs/mvc-architecture.md` for how a request flows from
there into a controller, model, and view.

## PHP version

All code targets **PHP 7.4** — no PHP 8+-only syntax (e.g. `match`,
`str_contains()`, nullsafe `?->`, named arguments, enums) is used anywhere in
`app/`, `config/`, `public/`, or `routes/`.
