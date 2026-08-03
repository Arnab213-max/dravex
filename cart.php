<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['flash']['error'] = 'Please login to view your cart.';
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle cart updates - PURE PHP
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart'])) {
        $cart_id = (int)$_POST['cart_id'];
        $quantity = (int)$_POST['quantity'];
        if ($quantity > 0) {
            $update = executeQuery("UPDATE cart SET quantity = $quantity WHERE id = $cart_id AND user_id = $user_id");
            if ($update) {
                $_SESSION['flash']['success'] = 'Cart updated!';
            }
        } else {
            executeQuery("DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id");
            $_SESSION['flash']['success'] = 'Item removed!';
        }
        header('Location: cart.php');
        exit();
    } elseif (isset($_POST['remove_item'])) {
        $cart_id = (int)$_POST['cart_id'];
        executeQuery("DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id");
        $_SESSION['flash']['success'] = 'Item removed from cart!';
        header('Location: cart.php');
        exit();
    } elseif (isset($_POST['clear_cart'])) {
        executeQuery("DELETE FROM cart WHERE user_id = $user_id");
        $_SESSION['flash']['success'] = 'Cart cleared!';
        header('Location: cart.php');
        exit();
    }
}

// Get cart items with proper JOIN
$cart_items = [];
$subtotal = 0;
$total_items = 0;

$cart_query = "
    SELECT 
        c.id as cart_id,
        c.quantity,
        p.id as product_id,
        p.name,
        p.price,
        p.discount_price,
        p.image,
        p.quantity as stock 
    FROM cart c 
    INNER JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = $user_id
";

$cart_result = executeQuery($cart_query);

if ($cart_result && mysqli_num_rows($cart_result) > 0) {
    while ($item = mysqli_fetch_assoc($cart_result)) {
        $item['price_to_use'] = $item['discount_price'] ?: $item['price'];
        $item['item_total'] = $item['price_to_use'] * $item['quantity'];
        $subtotal += $item['item_total'];
        $total_items += $item['quantity'];
        $cart_items[] = $item;
    }
}

// Get cart count for display
$count_query = "SELECT SUM(quantity) as total FROM cart WHERE user_id = $user_id";
$count_result = executeQuery($count_query);
$cart_count = 0;
if ($count_result && mysqli_num_rows($count_result) > 0) {
    $count_data = mysqli_fetch_assoc($count_result);
    $cart_count = (int)$count_data['total'];
}

include 'includes/header.php';
?>

<main>
    <div class="container" style="padding-top:var(--spacing-xl); padding-bottom:var(--spacing-xl);">
        
        <!-- Display Flash Messages -->
        <?php if (isset($_SESSION['flash']['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['flash']['success']; unset($_SESSION['flash']['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash']['error'])): ?>
            <div class="alert alert-error"><?php echo $_SESSION['flash']['error']; unset($_SESSION['flash']['error']); ?></div>
        <?php endif; ?>

        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom:var(--spacing-lg);">
            <h1 class="section-title" style="margin-bottom:0; text-align:left;">
                <i class="fas fa-shopping-cart" style="color:var(--gold);"></i> Shopping Cart
                <?php if ($total_items > 0): ?>
                    <span style="font-size:1rem; color:var(--text-muted);">(<?php echo $total_items; ?> items)</span>
                <?php endif; ?>
            </h1>
            <?php if (!empty($cart_items)): ?>
                <a href="products.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Continue Shopping
                </a>
            <?php endif; ?>
        </div>
        
        <?php if (empty($cart_items)): ?>
            <div class="glass-card" style="text-align:center; padding:4rem; max-width:600px; margin:0 auto;">
                <i class="fas fa-shopping-cart" style="font-size:4rem; color:var(--text-muted); margin-bottom:1rem;"></i>
                <h3 style="font-size:1.5rem;">Your cart is empty</h3>
                <p class="text-muted" style="margin-bottom:1.5rem;">Looks like you haven't added any items to your cart yet.</p>
                <a href="products.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-bag"></i> Start Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="row row-2">
                <!-- Cart Items -->
                <div>
                    <?php foreach ($cart_items as $item): ?>
                        <div class="glass-cart-item animate-fade" style="display:flex; align-items:center; gap:var(--spacing-md); padding:var(--spacing-md); margin-bottom:var(--spacing-md); background:rgba(255,255,255,0.03); border:1px solid rgba(212,175,55,0.08); border-radius:12px; transition:all 0.3s ease;">
                            <div style="width:80px; height:80px; flex-shrink:0;">
                                <?php 
                                $image_path = 'uploads/' . $item['image'];
                                if (file_exists($image_path)) {
                                    echo '<img src="' . $image_path . '" style="width:100%; height:100%; object-fit:cover; border-radius:8px;">';
                                } else {
                                    echo '<div style="width:100%; height:100%; background:linear-gradient(135deg,#2d1b69,#1a0a2e); border-radius:8px; display:flex; align-items:center; justify-content:center; color:rgba(255,255,255,0.2); font-size:2rem;">';
                                    echo '<i class="fas fa-tshirt"></i>';
                                    echo '</div>';
                                }
                                ?>
                            </div>
                            <div style="flex:1;">
                                <h3 style="font-size:1rem; margin-bottom:0.25rem;"><?php echo htmlspecialchars($item['name']); ?></h3>
                                <div style="color:var(--gold); font-weight:600;">Rs.<?php echo number_format($item['price_to_use'], 2); ?></div>
                                <div style="font-size:0.8rem; color:var(--text-muted);">Stock: <?php echo $item['stock']; ?> units</div>
                            </div>
                            <div style="display:flex; gap:var(--spacing-sm); align-items:center; flex-wrap:wrap;">
                                <form method="POST" style="display:flex; gap:5px; align-items:center;">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                    <button type="submit" name="update_cart" class="btn btn-secondary btn-sm" onclick="this.form.quantity.value = <?php echo max(1, $item['quantity'] - 1); ?>">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" name="quantity" class="qty-input" value="<?php echo $item['quantity']; ?>" 
                                           min="1" max="<?php echo $item['stock']; ?>" style="width:60px; text-align:center; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:#fff; padding:5px;">
                                    <button type="submit" name="update_cart" class="btn btn-secondary btn-sm" onclick="this.form.quantity.value = <?php echo min($item['stock'], $item['quantity'] + 1); ?>">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </form>
                                <form method="POST">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                    <button type="submit" name="remove_item" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                            <div style="font-weight:700; font-size:1.1rem; color:var(--gold); min-width:100px; text-align:right;">
                                Rs.<?php echo number_format($item['item_total'], 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div style="display:flex; gap:var(--spacing-md); margin-top:var(--spacing-md); flex-wrap:wrap;">
                        <form method="POST" onsubmit="return confirm('Are you sure you want to clear your cart?');">
                            <button type="submit" name="clear_cart" class="btn btn-danger">
                                <i class="fas fa-trash-alt"></i> Clear Cart
                            </button>
                        </form>
                        <a href="products.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Continue Shopping
                        </a>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div>
                    <div class="glass-card" style="position:sticky; top:100px;">
                        <h3 style="margin-bottom:var(--spacing-md); color:var(--gold);">Order Summary</h3>
                        <div style="display:flex; justify-content:space-between; padding:var(--spacing-sm) 0; border-bottom:1px solid rgba(255,255,255,0.05);">
                            <span>Items (<?php echo $total_items; ?>)</span>
                            <span>Rs.<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div style="display:flex; justify-content:space-between; padding:var(--spacing-sm) 0; border-bottom:1px solid rgba(255,255,255,0.05);">
                            <span>Delivery</span>
                            <span style="color:var(--success);">Free</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; padding:var(--spacing-sm) 0; border-bottom:1px solid rgba(255,255,255,0.05);">
                            <span>Tax (5%)</span>
                            <span>Rs.<?php echo number_format($subtotal * 0.05, 2); ?></span>
                        </div>
                        <div style="display:flex; justify-content:space-between; padding:var(--spacing-md) 0; font-weight:700; font-size:1.2rem; border-top:2px solid var(--gold); margin-top:var(--spacing-sm);">
                            <span>Total</span>
                            <span style="color:var(--gold);">Rs.<?php echo number_format($subtotal * 1.05, 2); ?></span>
                        </div>
                        <a href="checkout.php" class="btn btn-primary w-100" style="margin-top:var(--spacing-md);">
                            <i class="fas fa-credit-card"></i> Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
// Auto update  
document.querySelectorAll('.qty-input').forEach(function(input) {
    input.addEventListener('change', function() {
        var form = this.closest('form');
        var min = parseInt(this.min);
        var max = parseInt(this.max);
        var val = parseInt(this.value);
        if (isNaN(val) || val < min) this.value = min;
        else if (val > max) this.value = max;
        form.submit();
    });
});
</script>

<?php include 'includes/footer.php'; ?>