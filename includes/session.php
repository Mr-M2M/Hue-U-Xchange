<?php
/**
 * Shared session initializer. Any page that needs $_SESSION (cart,
 * checkout, confirmation) should require this once, before any output,
 * instead of calling session_start() directly. Guards against starting
 * a second session if a page is ever combined with another include that
 * already started one.
 */
function hue_start_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}
