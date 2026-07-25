<?php
require_once 'config/session.php';
destroySession();
header('Location: login.php');
exit();
?>