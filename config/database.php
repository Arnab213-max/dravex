<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'codecraze_threads');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

function getConnection() {
    global $conn;
    return $conn;
}

function escapeString($string) {
    global $conn;
    return mysqli_real_escape_string($conn, $string);
}

function executeQuery($sql) {
    global $conn;
    return mysqli_query($conn, $sql);
}

function getLastInsertId() {
    global $conn;
    return mysqli_insert_id($conn);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>