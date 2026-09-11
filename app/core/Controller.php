<?php
namespace App\Core;

/**
 * Base controller. Every controller extends this for the shared
 * render/redirect/notFound behavior so individual controllers stay
 * focused on request handling and model calls, not view plumbing.
 */
abstract class Controller
{
    /**
     * Render a view inside the shared layout. $data is extracted into
     * local variables visible to the view file. Flash messages queued
     * by a previous request (via Flash::set) are pulled and passed to
     * the layout automatically.
     */
    protected function render($view, array $data = [])
    {
        extract($data);
        $flashMessages = Flash::pull();

        require APP_ROOT . '/app/views/layouts/header.php';
        require APP_ROOT . '/app/views/' . $view . '.php';
        require APP_ROOT . '/app/views/layouts/footer.php';
    }

    /** Redirect to another route (Post-Redirect-Get) and stop execution. */
    protected function redirect($route, array $params = [])
    {
        header('Location: ' . Url::to($route, $params));
        exit;
    }

    /** Render the shared Not Found view with a real 404 status. */
    protected function notFound()
    {
        http_response_code(404);
        $flashMessages = [];
        require APP_ROOT . '/app/views/layouts/header.php';
        require APP_ROOT . '/app/views/errors/not_found.php';
        require APP_ROOT . '/app/views/layouts/footer.php';
    }
}
