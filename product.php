<?php
require_once 'config/database.php';
require_once 'config/session.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id <= 0) { header('Location: products.php'); exit(); }

// Handle Add to Cart - FIXED
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['flash']['error'] = 'Please login first to add items to cart.';
        header('Location: login.php');
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $product_id_post = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    // Check if quantity is valid
    if ($quantity <= 0) {
        $quantity = 1;
    }
    
    // Check product exists and has stock
    $check = executeQuery("SELECT id, quantity FROM products WHERE id = $product_id_post");
    if (mysqli_num_rows($check) > 0) {
        $prod = mysqli_fetch_assoc($check);
        
        if ($prod['quantity'] <= 0) {
            $_SESSION['flash']['error'] = 'Product is out of stock!';
            header('Location: product.php?id=' . $product_id_post);
            exit();
        }
        
        // Check if already in cart
        $cart_check = executeQuery("SELECT id, quantity FROM cart WHERE user_id = $user_id AND product_id = $product_id_post");
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
            // Insert into cart
            executeQuery("INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id_post, $quantity)");
            $_SESSION['flash']['success'] = 'Product added to cart!';
        }
    } else {
        $_SESSION['flash']['error'] = 'Product not available!';
    }
    header('Location: product.php?id=' . $product_id_post);
    exit();
}

// Handle Buy Now
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy_now'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['flash']['error'] = 'Please login first to purchase.';
        header('Location: login.php');
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $product_id_post = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity <= 0) {
        $quantity = 1;
    }
    
    $check = executeQuery("SELECT id, quantity FROM products WHERE id = $product_id_post AND quantity > 0");
    if (mysqli_num_rows($check) > 0) {
        $prod = mysqli_fetch_assoc($check);
        if ($quantity <= $prod['quantity']) {
            executeQuery("DELETE FROM cart WHERE user_id = $user_id");
            executeQuery("INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id_post, $quantity)");
            header('Location: checkout.php');
            exit();
        } else {
            $_SESSION['flash']['error'] = 'Not enough stock available!';
        }
    } else {
        $_SESSION['flash']['error'] = 'Product not available!';
    }
    header('Location: product.php?id=' . $product_id_post);
    exit();
}

$stmt = mysqli_prepare($conn, "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$product) { header('Location: products.php'); exit(); }

$related = executeQuery("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = " . $product['category_id'] . " AND p.id != " . $product_id . " AND p.quantity > 0 LIMIT 4");

include 'includes/header.php';
?>

<main>
    <div class="container" style="padding-top:var(--spacing-xl);">
        <?php if (isset($_SESSION['flash']['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['flash']['success']; unset($_SESSION['flash']['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash']['error'])): ?>
            <div class="alert alert-error"><?php echo $_SESSION['flash']['error']; unset($_SESSION['flash']['error']); ?></div>
        <?php endif; ?>

        <div class="row row-2">
            <div class="glass-card product-gallery">
                <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width:100%; border-radius:var(--radius-md);">
                <?php if ($product['discount_price'] && $product['discount_price'] < $product['price']): ?>
                    <span class="discount-badge" style="position:absolute; top:20px; right:20px; background:var(--danger); color:white; padding:0.3rem 1rem; border-radius:50px; font-size:0.8rem; font-weight:700;">
                        <?php echo round((($product['price'] - $product['discount_price']) / $product['price']) * 100); ?>% OFF
                    </span>
                <?php endif; ?>
            </div>
            <div class="glass-card product-detail-info">
                <span class="product-category" style="color:var(--gold); font-size:0.8rem; text-transform:uppercase; letter-spacing:2px;">
                    <?php echo htmlspecialchars($product['category_name']); ?>
                </span>
                <h1 style="font-size:2rem; margin-top:5px;"><?php echo htmlspecialchars($product['name']); ?></h1>
                <div style="display:flex; align-items:center; gap:8px; margin-top:5px;">
                    <div style="color:var(--warning);">
                        <?php 
                        $rating = $product['rating'] ?: 0;
                        for ($i = 1; $i <= 5; $i++):
                        ?>
                            <i class="fas fa-star<?php echo $i <= $rating ? '' : '-o'; ?>" style="font-size:0.9rem;"></i>
                        <?php endfor; ?>
                    </div>
                    <span style="color:var(--text-muted); font-size:0.8rem;">(<?php echo number_format($rating, 1); ?> rating)</span>
                </div>
                <div class="product-price" style="font-size:1.8rem; color:var(--gold); margin-top:10px;">
                    <?php if ($product['discount_price']): ?>
                        <span class="original-price" style="font-size:1.2rem; color:var(--text-muted); text-decoration:line-through; margin-right:10px;">
                            Rs.<?php echo number_format($product['price'], 2); ?>
                        </span>
                        <span>Rs.<?php echo number_format($product['discount_price'], 2); ?></span>
                    <?php else: ?>
                        <span>Rs.<?php echo number_format($product['price'], 2); ?></span>
                    <?php endif; ?>
                </div>
                <div class="product-stock" style="margin-top:5px;">
                    <?php if ($product['quantity'] > 0): ?>
                        <span style="color:var(--success);">
                            <i class="fas fa-check-circle"></i> In Stock (<?php echo $product['quantity']; ?> units available)
                        </span>
                    <?php else: ?>
                        <span style="color:var(--danger);">
                            <i class="fas fa-times-circle"></i> Out of Stock
                        </span>
                    <?php endif; ?>
                </div>
                <div class="product-description" style="margin:var(--spacing-md) 0;">
                    <h4 style="color:var(--text-secondary); margin-bottom:5px;">Description</h4>
                    <p style="color:var(--text-secondary); line-height:1.8;"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>

                <?php if ($product['quantity'] > 0): ?>
                    <div style="margin-top:var(--spacing-md); padding-top:var(--spacing-md); border-top:1px solid rgba(255,255,255,0.05);">
                        <form method="POST" style="display:flex; gap:15px; flex-wrap:wrap; align-items:center;">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            
                            <div style="display:flex; align-items:center; gap:10px;">
                                <label style="color:rgba(255,255,255,0.5); font-size:0.85rem; font-weight:500;">Qty:</label>
                                <div style="display:flex; align-items:center; gap:5px;">
                                    <button type="button" onclick="updateQty('decrease')" class="btn btn-secondary btn-sm" style="padding:5px 12px;">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['quantity']; ?>" 
                                           style="width:60px; text-align:center; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:8px; color:#fff; padding:8px;">
                                    <button type="button" onclick="updateQty('increase')" class="btn btn-secondary btn-sm" style="padding:5px 12px;">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <?php if (isset($_SESSION['user_id'])): ?>
                                <button type="submit" name="add_to_cart" class="btn btn-primary" style="flex:1; min-width:140px; padding:12px 25px;">
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                                <button type="submit" name="buy_now" class="btn btn-success" style="flex:1; min-width:140px; padding:12px 25px;">
                                   <i class="fa-regular fa-money-bill-1"></i> Buy Now
                                </button>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-primary" style="flex:1; min-width:140px; padding:12px 25px; text-align:center;">
                                    <i class="fas fa-sign-in-alt"></i> Login to Buy
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                <?php else: ?>
                    <div style="margin-top:var(--spacing-md); padding:15px; background:rgba(239,68,68,0.1); border-radius:12px; text-align:center; color:var(--danger);">
                        <i class="fas fa-exclamation-circle"></i> This product is currently out of stock. Please check back later.
                    </div>
                <?php endif; ?>

                <div style="margin-top:var(--spacing-md); display:flex; gap:10px; flex-wrap:wrap;">
                    <a href="products.php" class="btn btn-secondary" style="padding:10px 20px;">
                        <i class="fas fa-arrow-left"></i> Back to Products
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="cart.php" class="btn btn-primary" style="padding:10px 20px;">
                            <i class="fas fa-shopping-cart"></i> View Cart
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (mysqli_num_rows($related) > 0): ?>
            <section style="margin-top:var(--spacing-xl);">
                <h2 class="section-title">Related Products</h2>
                <p class="section-subtitle" style="color:var(--text-secondary); margin-bottom:var(--spacing-lg);">You might also like these</p>
                <div class="row row-4">
                    <?php while ($r = mysqli_fetch_assoc($related)): ?>
                        <div class="glass-product animate-fade">
                            <div class="product-image">
                                <a href="product.php?id=<?php echo $r['id']; ?>">
                                    <img src="uploads/<?php echo htmlspecialchars($r['image']); ?>" alt="<?php echo htmlspecialchars($r['name']); ?>">
                                </a>
                                <?php if ($r['discount_price'] && $r['discount_price'] < $r['price']): ?>
                                    <span class="discount-badge"><?php echo round((($r['price'] - $r['discount_price']) / $r['price']) * 100); ?>% OFF</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <span class="product-category"><?php echo htmlspecialchars($r['category_name']); ?></span>
                                <h3 class="product-title">
                                    <a href="product.php?id=<?php echo $r['id']; ?>"><?php echo htmlspecialchars($r['name']); ?></a>
                                </h3>
                                <div class="product-price">
                                    <?php if ($r['discount_price']): ?>
                                        <span class="original-price">Rs.<?php echo number_format($r['price'], 2); ?></span>
                                        <span>RS.<?php echo number_format($r['discount_price'], 2); ?></span>
                                    <?php else: ?>
                                        <span>Rs.<?php echo number_format($r['price'], 2); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div style="display:flex; gap:5px; margin-top:8px; flex-wrap:wrap;">
                                    <a href="product.php?id=<?php echo $r['id']; ?>" class="btn btn-secondary btn-sm" style="flex:1; text-align:center; min-width:60px;">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <?php if (isset($_SESSION['user_id']) && $r['quantity'] > 0): ?>
                                        <form method="POST" style="display:inline; flex:1;">
                                            <input type="hidden" name="product_id" value="<?php echo $r['id']; ?>">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" name="add_to_cart" class="btn btn-primary btn-sm" style="width:100%; min-width:50px;">
                                                <i class="fas fa-cart-plus"></i>
                                            </button>
                                        </form>
                                        <form method="POST" style="display:inline; flex:1;">
                                            <input type="hidden" name="product_id" value="<?php echo $r['id']; ?>">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" name="buy_now" class="btn btn-success btn-sm" style="width:100%; min-width:50px;">
                                              <i class="fa-regular fa-money-bill-1"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <a href="login.php" class="btn btn-primary btn-sm" style="flex:1; text-align:center; min-width:50px;">
                                            <i class="fas fa-cart-plus"></i>
                                        </a>
                                        <a href="login.php" class="btn btn-success btn-sm" style="flex:1; text-align:center; min-width:50px;">
                                          <i class="fa-regular fa-money-bill-1"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</main>

<script>
function updateQty(action) {
    var input = document.getElementById('quantity');
    var val = parseInt(input.value) || 1;
    var max = parseInt(input.max) || 99;
    if (action === 'increase' && val < max) {
        val++;
    } else if (action === 'decrease' && val > 1) {
        val--;
    }
    input.value = val;
}
</script>

<?php include 'includes/footer.php'; ?>