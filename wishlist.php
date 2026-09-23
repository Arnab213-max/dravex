<?php
require_once 'config/database.php';
require_once 'config/session.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

executeQuery("
    DELETE w FROM wishlist w
    LEFT JOIN products p ON w.product_id = p.id
    WHERE w.user_id = $user_id
    AND (
        w.created_at < DATE_SUB(NOW(), INTERVAL 5 DAY)
        OR p.id IS NULL
        OR p.quantity <= 0
    )
");

if (isset($_GET['remove'])) {
    $id = (int)$_GET['remove'];
    executeQuery("DELETE FROM wishlist WHERE id = $id AND user_id = $user_id");
    $_SESSION['flash']['success'] = 'Removed from wishlist!';
    header('Location: wishlist.php');
    exit();
}

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
    SELECT w.id as wish_id, w.created_at as wish_added_at, p.*, c.name as category_name 
    FROM wishlist w 
    JOIN products p ON w.product_id = p.id 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE w.user_id = $user_id 
    AND p.quantity > 0
    AND w.created_at >= DATE_SUB(NOW(), INTERVAL 5 DAY)
    ORDER BY w.created_at DESC
");

include 'includes/header.php';
?>

<main>
    <div class="container wishlist-page-wrapper">
        <h1 class="section-title wishlist-title">
            <i class="fas fa-heart"></i> My Wishlist
        </h1>
        <p class="wishlist-note">
            <i class="fas fa-info-circle"></i> Wishlist items are kept for 5 days and are automatically removed if the product goes out of stock.
        </p>

        <?php if (isset($_SESSION['flash']['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['flash']['success']; unset($_SESSION['flash']['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash']['error'])): ?>
            <div class="alert alert-error"><?php echo $_SESSION['flash']['error']; unset($_SESSION['flash']['error']); ?></div>
        <?php endif; ?>

        <?php if (mysqli_num_rows($wishlist) > 0): ?>
            <div class="row row-4 wishlist-grid">
                <?php while ($item = mysqli_fetch_assoc($wishlist)): ?>
                    <?php
                        $expires_ts = strtotime($item['wish_added_at']) + (5 * 24 * 60 * 60);
                        $days_left = max(0, (int)ceil(($expires_ts - time()) / 86400));
                    ?>
                    <div class="glass-product animate-fade">
                        <div class="product-image">
                            <?php if (!empty($item['image']) && file_exists('uploads/' . $item['image'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <?php else: ?>
                                <img src="uploads/placeholder.jpg" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <?php endif; ?>

                            <?php if ($item['discount_price'] && $item['discount_price'] < $item['price']): ?>
                                <span class="discount-badge"><?php echo round((($item['price'] - $item['discount_price']) / $item['price']) * 100); ?>% OFF</span>
                            <?php endif; ?>

                            <a href="wishlist.php?remove=<?php echo $item['wish_id']; ?>" class="remove-wishlist" onclick="return confirm('Remove from wishlist?')">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                        <div class="product-info">
                            <span class="product-category"><?php echo htmlspecialchars($item['category_name'] ?? 'Uncategorized'); ?></span>
                            <h3 class="product-title"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <div class="product-price">
                                <?php if ($item['discount_price']): ?>
                                    <span class="original-price">Rs.<?php echo number_format($item['price'], 2); ?></span>
                                    <span>Rs.<?php echo number_format($item['discount_price'], 2); ?></span>
                                <?php else: ?>
                                    <span>Rs.<?php echo number_format($item['price'], 2); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="wishlist-expiry">
                                <i class="fas fa-clock"></i>
                                <?php if ($days_left <= 1): ?>
                                    Expires today
                                <?php else: ?>
                                    Expires in <?php echo $days_left; ?> days
                                <?php endif; ?>
                            </div>
                            <div class="wishlist-actions">
                                <a href="product.php?id=<?php echo $item['id']; ?>" class="btn btn-secondary btn-sm">View</a>
                                <form method="POST" class="wishlist-cart-form">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="add_to_cart" class="btn btn-primary btn-sm">
                                        <i class="fas fa-cart-plus"></i> Add
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="glass-card empty-wishlist">
                <i class="fas fa-heart"></i>
                <h3>Wishlist is empty</h3>
                <p class="text-muted">Start adding products to your wishlist!</p>
                <a href="products.php" class="btn btn-primary">Browse Products</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
.wishlist-page-wrapper {
    padding-top: 3rem;
    padding-bottom: 3rem;
}

.wishlist-title {
    text-align: left;
    margin-bottom: 0.5rem;
    color: #2c2c2c;
}

.wishlist-title i {
    color: #e74c3c;
    margin-right: 0.5rem;
}

.wishlist-note {
    color: #6a6a6a;
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.wishlist-note i {
    color: #6a6a6a;
}

.wishlist-grid {
    margin-top: 1rem;
}

.wishlist-grid .glass-product {
    background: #ffffff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(44, 44, 44, 0.06);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
    position: relative;
    height: 100%;
}

.wishlist-grid .glass-product:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(44, 44, 44, 0.12);
}

.wishlist-grid .product-image {
    position: relative;
    width: 100%;
    aspect-ratio: 1 / 1;
    overflow: hidden;
    background: #f0ede6;
}

.wishlist-grid .product-image img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.wishlist-grid .discount-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    display: inline-block;
    width: auto;
    max-width: 80px;
    background: #e74c3c;
    color: #ffffff;
    padding: 0.25rem 0.55rem;
    border-radius: 20px;
    font-size: 0.68rem;
    font-weight: 700;
    line-height: 1;
    z-index: 5;
    white-space: nowrap;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

.remove-wishlist {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(239, 68, 68, 0.9);
    color: #ffffff;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-size: 0.75rem;
    z-index: 5;
    transition: all 0.2s ease;
}

.remove-wishlist:hover {
    background: #dc2626;
    transform: scale(1.1);
    color: #ffffff;
}

.wishlist-grid .product-info {
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    flex: 1;
}

.wishlist-grid .product-category {
    color: #6a6a6a;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.wishlist-grid .product-title {
    color: #2c2c2c;
    font-weight: 600;
    font-size: 1.05rem;
    margin: 0;
    line-height: 1.3;
}

.wishlist-grid .product-price {
    color: #2c2c2c;
    font-weight: 700;
    font-size: 1.05rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    flex-wrap: wrap;
}

.wishlist-grid .original-price {
    color: #999;
    text-decoration: line-through;
    font-weight: 400;
    font-size: 0.85rem;
}

.wishlist-expiry {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.78rem;
    color: #b45309;
    background: #fef3c7;
    padding: 0.3rem 0.6rem;
    border-radius: 20px;
    width: fit-content;
    margin-top: 0.25rem;
}

.wishlist-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.75rem;
    flex-wrap: wrap;
}

.wishlist-actions .btn {
    flex: 1;
    justify-content: center;
    padding: 0.6rem 1rem;
    font-size: 0.78rem;
    border-radius: 50px;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.25s ease;
}

.wishlist-cart-form {
    flex: 1;
    display: flex;
    margin: 0;
}

.wishlist-cart-form .btn {
    width: 100%;
}

.wishlist-actions .btn-primary {
    background: linear-gradient(135deg, #2c2c2c, #4a4a4a);
    color: #f8f6f0;
}

.wishlist-actions .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(44, 44, 44, 0.25);
    color: #f8f6f0;
}

.wishlist-actions .btn-secondary {
    background: rgba(44, 44, 44, 0.05);
    border: 1px solid rgba(44, 44, 44, 0.15);
    color: #2c2c2c;
}

.wishlist-actions .btn-secondary:hover {
    background: rgba(44, 44, 44, 0.1);
    transform: translateY(-2px);
    color: #2c2c2c;
}

.empty-wishlist {
    text-align: center;
    padding: 3rem 2rem;
}

.empty-wishlist i {
    font-size: 3rem;
    color: #6a6a6a;
    margin-bottom: 1rem;
}

.empty-wishlist h3 {
    color: #2c2c2c;
    margin-bottom: 0.5rem;
}

.empty-wishlist .btn-primary {
    margin-top: 1rem;
    background: linear-gradient(135deg, #2c2c2c, #4a4a4a);
    color: #f8f6f0;
    padding: 0.8rem 2rem;
    border-radius: 50px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 0.85rem;
}

@media (max-width: 576px) {
    .wishlist-title {
        font-size: 1.6rem;
    }
    .wishlist-actions {
        flex-direction: column;
    }
}
</style>

<?php include 'includes/footer.php'; ?>