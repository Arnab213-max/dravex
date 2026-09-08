<?php
require_once 'config/database.php';
require_once 'config/session.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Remove from wishlist
if (isset($_GET['remove'])) {
    $id = (int)$_GET['remove'];
    executeQuery("DELETE FROM wishlist WHERE id = $id AND user_id = $user_id");
    $_SESSION['flash']['success'] = 'Removed from wishlist!';
    header('Location: wishlist.php');
    exit();
}

// Add to cart from wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = 1;
    $check = executeQuery("SELECT id, quantity FROM products WHERE id = $product_id AND quantity > 0");
    if (mysqli_num_rows($check) > 0) {
        $prod = mysqli_fetch_assoc($check);
        $cart_check = executeQuery("SELECT id, quantity FROM cart WHERE user_id = $user_id AND product_id = $product_id");
        if (mysqli_num_rows($cart_check) > 0) {
            $cart_item = mysqli_fetch_assoc($cart_check);
            $new_qty = $cart_item['quantity'] + $quantity;
            if ($new_qty <= $prod['quantity']) {
                executeQuery("UPDATE cart SET quantity = $new_qty WHERE id = " . $cart_item['id']);
                $_SESSION['flash']['success'] = 'Cart updated!';
            } else {
                $_SESSION['flash']['error'] = 'Not enough stock available!';
            }
        } else {
            executeQuery("INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, $quantity)");
            $_SESSION['flash']['success'] = 'Product added to cart!';
        }
    } else {
        $_SESSION['flash']['error'] = 'Product not available!';
    }
    header('Location: wishlist.php');
    exit();
}

$wishlist = executeQuery("
    SELECT w.id as wish_id, p.*, c.name as category_name 
    FROM wishlist w 
    JOIN products p ON w.product_id = p.id 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE w.user_id = $user_id 
    ORDER BY w.created_at DESC
");

include 'includes/header.php';
?>

<main>
    <div class="container" style="padding-top:var(--spacing-xl); padding-bottom:var(--spacing-xl);">
        <h1 class="section-title" style="text-align:left; margin-bottom:var(--spacing-lg);">
            <i class="fas fa-heart" style="color:var(--gold);"></i> My Wishlist
        </h1>

        <?php if (isset($_SESSION['flash']['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['flash']['success']; unset($_SESSION['flash']['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash']['error'])): ?>
            <div class="alert alert-error"><?php echo $_SESSION['flash']['error']; unset($_SESSION['flash']['error']); ?></div>
        <?php endif; ?>

        <?php if (mysqli_num_rows($wishlist) > 0): ?>
            <div class="row row-4">
                <?php while ($item = mysqli_fetch_assoc($wishlist)): ?>
                    <div class="glass-product animate-fade">
                        <div class="product-image">
                            <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <?php if ($item['discount_price'] && $item['discount_price'] < $item['price']): ?>
                                <span class="discount-badge"><?php echo round((($item['price'] - $item['discount_price']) / $item['price']) * 100); ?>% OFF</span>
                            <?php endif; ?>
                            <a href="wishlist.php?remove=<?php echo $item['wish_id']; ?>" class="remove-wishlist" onclick="return confirm('Remove from wishlist?')" style="position:absolute; top:10px; left:10px; background:rgba(239,68,68,0.9); color:white; padding:0.3rem 0.7rem; border-radius:50px; font-size:0.7rem; font-weight:600; text-decoration:none;">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                        <div class="product-info">
                            <span class="product-category"><?php echo htmlspecialchars($item['category_name']); ?></span>
                            <h3 class="product-title"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <div class="product-price">
                                <?php if ($item['discount_price']): ?>
                                    <span class="original-price">₹<?php echo number_format($item['price'], 2); ?></span>
                                    <span>₹<?php echo number_format($item['discount_price'], 2); ?></span>
                                <?php else: ?>
                                    <span>₹<?php echo number_format($item['price'], 2); ?></span>
                                <?php endif; ?>
                            </div>
                            <div style="display:flex; gap:5px; margin-top:10px; flex-wrap:wrap;">
                                <a href="product.php?id=<?php echo $item['id']; ?>" class="btn btn-secondary btn-sm" style="flex:1; text-align:center;">View</a>
                                <form method="POST" style="display:inline; flex:1;">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="add_to_cart" class="btn btn-primary btn-sm" style="width:100%;">
                                        <i class="fas fa-cart-plus"></i> Add
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="glass-card" style="text-align:center; padding:3rem;">
                <i class="fas fa-heart" style="font-size:3rem; color:var(--text-muted);"></i>
                <h3>Wishlist is empty</h3>
                <p class="text-muted">Start adding products to your wishlist!</p>
                <a href="products.php" class="btn btn-primary">Browse Products</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
.remove-wishlist:hover {
    background: #dc2626 !important;
    transform: scale(1.1);
}
</style>

<?php include 'includes/footer.php'; ?>