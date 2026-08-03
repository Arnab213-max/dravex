<?php
require_once 'config/database.php';
require_once 'config/session.php';

if (!isset($_SESSION['user_id'])) {
    echo " Please login first!<br>";
    echo "<a href='login.php'>Login</a>";
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

if ($product_id <= 0) {
    echo " Invalid product ID!<br>";
    echo "<a href='test_cart_system.php'>Go Back</a>";
    exit();
}

echo "<h2>Adding Product ID: $product_id to Cart</h2>";

// Check product exists
$product_check = executeQuery("SELECT id, name, quantity FROM products WHERE id = $product_id");
if (mysqli_num_rows($product_check) == 0) {
    echo " Product not found!<br>";
    echo "<a href='test_cart_system.php'>Go Back</a>";
    exit();
}

$product = mysqli_fetch_assoc($product_check);
echo "Product: " . $product['name'] . "<br>";
echo "Stock: " . $product['quantity'] . "<br>";

if ($product['quantity'] <= 0) {
    echo " Product is out of stock!<br>";
    echo "<a href='test_cart_system.php'>Go Back</a>";
    exit();
}

// Check if already in cart
$cart_check = executeQuery("SELECT id, quantity FROM cart WHERE user_id = $user_id AND product_id = $product_id");

if (mysqli_num_rows($cart_check) > 0) {
    // Update 
    $cart_item = mysqli_fetch_assoc($cart_check);
    $new_qty = $cart_item['quantity'] + 1;
    $update = executeQuery("UPDATE cart SET quantity = $new_qty WHERE id = " . $cart_item['id']);
    if ($update) {
        echo "Updated quantity to $new_qty<br>";
    } else {
        echo "Failed to update cart!<br>";
    }
} else {
    // Insert new
    $insert = executeQuery("INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, 1)");
    if ($insert) {
        echo " Product added to cart!<br>";
    } else {
        echo " Failed to add to cart! Error: " . mysqli_error($conn) . "<br>";
    }
}

// current cart
echo "<h3>Current Cart:</h3>";
$cart_query = "SELECT c.*, p.name FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = $user_id";
$cart_result = executeQuery($cart_query);

if (mysqli_num_rows($cart_result) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Product</th><th>Quantity</th></tr>";
    while ($row = mysqli_fetch_assoc($cart_result)) {
        echo "<tr>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['quantity'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo " Cart is still empty!<br>";
}

echo "<br><a href='test_cart_system.php'>Go Back</a> | ";
echo "<a href='cart.php'>View Cart</a>";
?>