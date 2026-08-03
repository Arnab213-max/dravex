<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function destroySession() {
    session_unset();
    session_destroy();
}
?>