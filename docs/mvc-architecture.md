# Hue U Xchange - MVC Architecture

Phase 3 reorganized the application into a Model-View-Controller structure,
and Phase 4 (Week 5) finalized it. The Phase 4 additions are summarized in
"Phase 4 changes" at the end of this document.
This document explains what each directory is responsible for and how a
browser request actually moves through the code below - it describes only
what the code in this repository does.

## Directory responsibilities

```
app/
  core/         Framework plumbing shared by every request: Autoloader,
                Session, Flash (one-time redirect messages), Url (route-
                link builder), Csrf (per-session form token), and the base
                Controller class.
  models/       Product.php, Cart.php, and Order.php - all database access
                and session-cart rules live here. Lore.php holds the
                narrative content for The Curing Process page. No HTML, no
                $_GET/$_POST.
  controllers/  HomeController, AboutController, LoreController,
                ProductController, CartController, CheckoutController,
                OrderController - request handling, validation, and flow
                control. No SQL, no full HTML pages.
  views/        Presentation only. layouts/ holds the shared header, nav,
                and footer; the rest are grouped by feature (home/,
                about/, lore/, products/, cart/, checkout/, orders/,
                errors/).
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


## Phase 4 changes

Phase 4 kept the structure above and made these finalization changes:

- **Form tokens (`App\Core\Csrf`).** Every POST form renders
  `Csrf::field()`, and `Controller::requireValidPost()` rejects any
  state-changing request that is not a POST or does not carry the
  session's token. The token is rotated after a successful checkout, so
  the same checkout form cannot be processed twice.
- **Layout data.** `Controller::layoutData()` supplies `$currentSection`
  (for the active-navigation state and `aria-current`) and `$cartCount`
  (the nav cart badge) to the shared layout, so `layouts/nav.php` no
  longer reads `$_GET` or defines functions. The front controller defines
  `HUE_ROUTE` once for this purpose.
- **Cart rule fix.** `Cart::addOrSet()` now applies `MAX_QUANTITY` to the
  combined quantity, so repeated adds cannot push a line past 25.
- **Price validation.** `Product::validateInput()` accepts only plain
  decimals with up to two places (rejects values such as `1e3`).
- **Checkout.** `CheckoutController` shows an order summary on the form,
  treats Energy Signature as optional (validated only when sent), and
  clears the cart only after successful processing.
- **The Curing Process page.** `LoreController` passes data from
  `App\Models\Lore` (artist biography, characters, oath, music, Coming
  Soon) to `views/lore/index.php`.
- **Session.** `Session::start()` is still the only place that calls
  `session_start()`; it now sets HttpOnly and SameSite=Lax cookie
  parameters first.

## Final submission changes

The final audit kept the structure above and added order persistence:

- **Relational tables.** `database/hue_u_xchange.sql` now creates
  `customers`, `orders`, and `order_items` alongside `products`. See
  [`database-design.md`](database-design.md) for the relationship diagram.
- **`App\Models\Order`.** `place()` saves a checkout inside one
  transaction: it reuses or creates the customer row for the email
  address, inserts the order, and inserts one `order_items` row per cart
  line using the unit price already resolved from MySQL by
  `Cart::contents()`. If any insert fails, the transaction is rolled back
  and nothing is recorded. `findWithItems()` and `recent()` read orders
  back with joins.
- **`CheckoutController`.** After validation, `submit()` calls
  `Order::place()`. The cart is cleared only after the order commits; on a
  database failure the visitor sees a safe message and keeps the cart.
  `confirmation()` reads the saved order back from MySQL, so the
  Certified Light Carrier page shows exactly what was stored.
- **`OrderController` and `views/orders/index.php`.** A read-only Order
  History page (route `orders`, linked from Manage Products) lists saved
  orders with the customer name, item count, and total.

Request flow for a successful checkout:

```
Browser POST index.php?route=checkout/submit
  -> public/index.php (front controller: session, route map)
  -> CheckoutController::submit()  validates token, name, email, signature
  -> Cart::contents($pdo)          trusted prices from products
  -> Order::place(...)             customers -> orders -> order_items (transaction)
  -> redirect to ?route=confirm    (Post-Redirect-Get)
  -> CheckoutController::confirmation()
  -> Order::findWithItems($id)     joins orders, customers, order_items, products
  -> views/checkout/confirm.php inside layouts/header.php + footer.php
```

