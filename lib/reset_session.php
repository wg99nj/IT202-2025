<?php
// Always call session_start() at the very top of the file, before any output
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
function reset_session()
{
    session_unset();
    session_destroy();
    session_start();
}