# Hue U Xchange - MVC Architecture

Phase 3 reorganizes the application into a Model-View-Controller structure.
This document explains what each directory is responsible for and how a
browser request actually moves through the code below - it describes only
what the code in this repository does.

## Directory responsibilities

```
app/
  core/         Framework plumbing shared by every request: Autoloader,
                Session, Flash (one-time redirect messages), Url (route-
                link builder), and the base Controller class.
  models/       Product.php and Cart.php - all database access and
                session-cart rules live here. No HTML, no $_GET/$_POST.
  controllers/  HomeController, AboutController, ProductController,
                CartController, CheckoutController - request handling,
                validation, and flow control. No SQL, no full HTML pages.
  views/        Presentation only. layouts/ holds the shared header, nav,
                and footer; the rest are grouped by feature (home/,
                about/, products/, cart/, checkout/, errors/).
config/         database.php (the centralized PDO connection) and the
                git-ignored config.local.php with local credentials.
database/       hue_u_xchange.sql - the importable schema + seed export.
public/         The web root. index.php is the single front controller;
                .htaccess and router.php are routing entry points (see
                below); assets/css and images are static files.
routes/         routes.php - the route-to-controller map.
```

## How a request enters the application

1. Every request that reaches PHP is handled by `public/index.php` - the
   front controller. In production/XAMPP this happens either because a
   visitor requested `index.php` directly, or because `public/.htaccess`
   rewrote a prettier path (e.g. `/products`) into it.
2. `index.php` defines `APP_ROOT`, requires `app/core/Autoloader.php` and
   calls `HueAutoloader::register()` so `App\...` classes load on demand,
   then requires `config/database.php` (the `get_db_connection()`
   function used by both models) and starts the session once via
   `App\Core\Session::start()`.
3. It loads `routes/routes.php`, a plain array mapping a route string to
   a `[ControllerClass, method]` pair.

## How routes select controllers

Routing is query-string based: `index.php?route=products` is the
canonical, always-reliable form and works under XAMPP, Apache, and PHP's
built-in development server with no configuration. The front controller
reads `$_GET['route']`, looks it up in the route map, and calls
`new $controllerClass()` then `$controller->$action()`. An unmapped route
renders the shared Not Found view with a real 404 status instead of a PHP
error. An uncaught exception anywhere in the controller/model layer is
caught in `index.php`, logged with `error_log()`, and shown as the
generic Something Went Wrong view - no stack trace or credential ever
reaches the browser.

Two files exist purely to make pretty URLs optional, not required:

- `public/.htaccess` rewrites a request like `/products` into
  `index.php?route=products` for Apache/XAMPP - this requires
  `AllowOverride All` for the directory in the Apache vhost config so the
  file is actually read.
- `public/router.php` reproduces the same behavior for PHP's built-in
  server (`php -S ... public/router.php`), which ignores `.htaccess`.

Because the query-string form always works regardless of whether either
routing file is active, reliability never depends on mod_rewrite being
configured correctly.

## How controllers use models and pass data to views

A controller never writes SQL. `ProductController::manage()`, for
example, opens the shared PDO connection with `get_db_connection()`,
passes it to `new App\Models\Product($pdo)`, and calls `$productModel->
getAll()`. The model returns a plain array of rows; the controller hands
that array to `Controller::render('products/manage', ['products' =>
$products, ...])`. `render()` extracts the data array into local
variables and requires the shared header, the requested view file, and
the shared footer in sequence - so every view automatically sits inside
the same layout without repeating that markup.

Validation (e.g. `Product::validateInput()`, the email/name/signature
checks in `CheckoutController::submit()`) runs in the controller layer
before a model method is ever called, so models only ever receive
already-cleaned values.

## How views use shared layouts

`app/views/layouts/header.php` renders the `<head>`, includes
`layouts/nav.php` (which builds every link through `App\Core\Url::to()`),
opens `<main>`, and prints any queued flash message. `layouts/footer.php`
closes `<main>` and the document. Every feature view (e.g.
`views/products/offerings.php`) contains only the markup for its own
`<section>` - it is required between the header and footer by
`Controller::render()` and never opens a database connection or runs a
query itself.

## How the session-based cart fits in

`App\Models\Cart` owns every cart rule: it validates the product ID and
quantity against `App\Models\Product::getActiveByIds()` (so a
deactivated or non-existent product can never be added), enforces
`Cart::MAX_QUANTITY`, and always prices a line from the database value it
just fetched - never from a submitted price. `CartController` reads
`$_POST`, calls `Cart::add()` / `updateQuantity()` / `remove()`, sets a
flash message, and redirects back to `cart` (Post-Redirect-Get) so a
page refresh never repeats the mutation. `cart/index.php` only displays
the array `Cart::contents()` returns - it never touches `$_SESSION`
directly.

## How database configuration stays outside presentation

`config/database.php` is required once, by the front controller, before
any controller or view runs. It exposes a single `get_db_connection()`
function backed by a `static` PDO instance and reads credentials from
environment variables or the git-ignored `config/config.local.php` - no
view file, and no model, ever opens its own connection or references
credentials directly.
