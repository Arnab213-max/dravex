<?php
require_once '../config/database.php';
require_once '../config/session.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit();
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id <= 0) {
    header('Location: products.php');
    exit();
}

$result = executeQuery("SELECT image FROM products WHERE id = $product_id");
$product = mysqli_fetch_assoc($result);
if ($product && $product['image'] != 'default.jpg' && file_exists('../uploads/' . $product['image'])) {
    unlink('../uploads/' . $product['image']);
}

executeQuery("DELETE FROM products WHERE id = $product_id");
$_SESSION['flash']['success'] = 'Product deleted successfully!';
header('Location: products.php');
exit();
?>