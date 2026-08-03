<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Handle Add to Cart - FIXED
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['flash']['error'] = 'Please login first to add items to cart.';
        header('Location: login.php');
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity <= 0) {
        $quantity = 1;
    }
    
    $check = executeQuery("SELECT id, quantity FROM products WHERE id = $product_id");
    if (mysqli_num_rows($check) > 0) {
        $prod = mysqli_fetch_assoc($check);
        
        if ($prod['quantity'] <= 0) {
            $_SESSION['flash']['error'] = 'Product is out of stock!';
            header('Location: products.php');
            exit();
        }
        
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
    header('Location: products.php');
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
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity <= 0) {
        $quantity = 1;
    }
    
    $check = executeQuery("SELECT id, quantity FROM products WHERE id = $product_id AND quantity > 0");
    if (mysqli_num_rows($check) > 0) {
        $prod = mysqli_fetch_assoc($check);
        if ($quantity <= $prod['quantity']) {
            executeQuery("DELETE FROM cart WHERE user_id = $user_id");
            executeQuery("INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, $quantity)");
            header('Location: checkout.php');
            exit();
        } else {
            $_SESSION['flash']['error'] = 'Not enough stock available!';
        }
    } else {
        $_SESSION['flash']['error'] = 'Product not available!';
    }
    header('Location: products.php');
    exit();
}

$categories = executeQuery("SELECT * FROM categories ORDER BY name");

$where = ["p.quantity > 0"];
$params = [];
$types = "";

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
if ($category_id > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($search)) {
    $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $s = "%$search%";
    $params[] = $s;
    $params[] = $s;
    $types .= "ss";
}

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'latest';
switch($sort) {
    case 'price_low': $order = "ORDER BY p.price ASC"; break;
    case 'price_high': $order = "ORDER BY p.price DESC"; break;
    case 'rating': $order = "ORDER BY p.rating DESC"; break;
    case 'name': $order = "ORDER BY p.name ASC"; break;
    default: $order = "ORDER BY p.id DESC";
}

$query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE " . implode(" AND ", $where) . " $order";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $products = mysqli_stmt_get_result($stmt);
} else {
    $products = executeQuery($query);
}

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

        <div class="section-header">
            <h1 class="section-title">Our Products</h1>
            <p class="section-subtitle">Discover premium streetwear for every style</p>
        </div>

        <div style="display:flex; gap:var(--spacing-md); flex-wrap:wrap; margin-bottom:var(--spacing-xl);">
            <form method="GET" style="flex:1; min-width:200px;">
                <div style="display:flex; gap:var(--spacing-sm);">
                    <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
                </div>
            </form>
            <select name="category" id="categoryFilter" class="form-control" style="width:auto; min-width:150px;">
                <option value="0">All Categories</option>
                <?php mysqli_data_seek($categories, 0); while($cat = mysqli_fetch_assoc($categories)): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endwhile; ?>
            </select>
            <select name="sort" id="sortFilter" class="form-control" style="width:auto; min-width:150px;">
                <option value="latest" <?php echo $sort == 'latest' ? 'selected' : ''; ?>>Latest</option>
                <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                <option value="rating" <?php echo $sort == 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Name A-Z</option>
            </select>
        </div>

        <?php if (mysqli_num_rows($products) > 0): ?>
        <div class="row row-4">
            <?php while ($p = mysqli_fetch_assoc($products)): ?>
            <div class="glass-product animate-fade">
                <div class="product-image">
                    <img src="uploads/<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                    <?php if ($p['discount_price'] && $p['discount_price'] < $p['price']): ?>
                    <span class="discount-badge"><?php echo round((($p['price'] - $p['discount_price']) / $p['price']) * 100); ?>% OFF</span>
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <span class="product-category"><?php echo htmlspecialchars($p['category_name']); ?></span>
                    <h3 class="product-title"><?php echo htmlspecialchars($p['name']); ?></h3>
                    <div class="product-price">
                        <?php if ($p['discount_price']): ?>
                        <span class="original-price">Rs.<?php echo number_format($p['price'], 2); ?></span>
                        <span>Rs.<?php echo number_format($p['discount_price'], 2); ?></span>
                        <?php else: ?>
                        <span>Rs.<?php echo number_format($p['price'], 2); ?></span>
                        <?php endif; ?>
                    </div>
                    <div style="display:flex; gap:5px; flex-wrap:wrap; margin-top:10px;">
                        <a href="product.php?id=<?php echo $p['id']; ?>" class="btn btn-secondary btn-sm" style="flex:1; text-align:center; min-width:70px;">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <?php if (isset($_SESSION['user_id']) && $p['quantity'] > 0): ?>
                            <form method="POST" style="display:inline; flex:1;">
                                <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" name="add_to_cart" class="btn btn-primary btn-sm" style="width:100%; min-width:60px;">
                                    <i class="fas fa-cart-plus"></i> Add
                                </button>
                            </form>
                            <form method="POST" style="display:inline; flex:1;">
                                <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" name="buy_now" class="btn btn-success btn-sm" style="width:100%; min-width:60px;">
                                            <i class="fa-regular fa-money-bill-1"></i> Buy
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary btn-sm" style="flex:1; text-align:center; min-width:60px;">
                                <i class="fas fa-cart-plus"></i> Add
                            </a>
                            <a href="login.php" class="btn btn-success btn-sm" style="flex:1; text-align:center; min-width:60px;">
                                <i class="fa-regular fa-money-bill-1"></i> Buy
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="glass-card" style="text-align:center; padding:3rem;">
            <i class="fas fa-search" style="font-size:3rem; color:var(--text-muted);"></i>
            <h3>No products found</h3>
            <p class="text-muted">Try adjusting your filters or search terms</p>
            <a href="products.php" class="btn btn-primary">Clear Filters</a>
        </div>
        <?php endif; ?>
    </div>
</main>

<script>
document.getElementById('categoryFilter').addEventListener('change', function() {
    var url = new URL(window.location.href);
    url.searchParams.set('category', this.value);
    window.location.href = url.toString();
});
document.getElementById('sortFilter').addEventListener('change', function() {
    var url = new URL(window.location.href);
    url.searchParams.set('sort', this.value);
    window.location.href = url.toString();
});
</script>
<?php include 'includes/footer.php'; ?>