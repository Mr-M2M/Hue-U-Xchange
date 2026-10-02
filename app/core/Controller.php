<?php
namespace App\Core;

use App\Models\Cart;

/**
 * Base controller. Provides the three things every controller needs -
 * rendering a view inside the shared layout, redirecting (Post-Redirect-
 * Get), and guarding state-changing requests - so individual controllers
 * contain request flow only, never full HTML documents or SQL.
 */
abstract class Controller
{
    protected function render($view, array $data = [])
    {
        extract($data);
        $flashMessages = Flash::pull();
        $layout = self::layoutData();
        extract($layout);

        require APP_ROOT . '/app/views/layouts/header.php';
        require APP_ROOT . '/app/views/' . $view . '.php';
        require APP_ROOT . '/app/views/layouts/footer.php';
    }

    /**
     * Values the shared layout needs on every page: which nav section is
     * current and how many items are in the cart. Computed here so the
     * layout views stay presentation-only.
     */
    public static function layoutData()
    {
        $route = defined('HUE_ROUTE') ? HUE_ROUTE : '';
        $section = $route === '' ? 'home' : explode('/', $route)[0];
        if ($section === 'confirm') {
            $section = 'checkout';
        } elseif ($section === 'orders') {
            $section = 'products';
        }
        return array(
            'currentSection' => $section,
            'cartCount'      => Cart::itemCount(),
        );
    }

    protected function redirect($route, array $params = [])
    {
        header('Location: ' . Url::to($route, $params));
        exit;
    }

    /**
     * Guards every state-changing action: it must be a POST and carry the
     * session's form token. Anything else is redirected with a clear
     * message and nothing is changed.
     */
    protected function requireValidPost($fallbackRoute, array $params = [])
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect($fallbackRoute, $params);
        }
        $submitted = isset($_POST[Csrf::FIELD]) ? $_POST[Csrf::FIELD] : '';
        if (!Csrf::isValid($submitted)) {
            Flash::set('error', 'That form has expired or was already submitted. Please try again.');
            $this->redirect($fallbackRoute, $params);
        }
    }

    protected function notFound()
    {
        http_response_code(404);
        $flashMessages = [];
        $pageTitle = 'Not Found - Hue U Xchange';
        extract(self::layoutData());
        require APP_ROOT . '/app/views/layouts/header.php';
        require APP_ROOT . '/app/views/errors/not_found.php';
        require APP_ROOT . '/app/views/layouts/footer.php';
    }
}
