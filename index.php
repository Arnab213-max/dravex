<?php
require_once 'config/database.php';
require_once 'config/session.php';

//  tables exist before querying
$stats = [
    'products' => 0,
    'categories' => 0,
    'users' => 0,
    'orders' => 0
];

if (tableExists('products')) {
    $result = executeQuery("SELECT COUNT(*) as total FROM products WHERE quantity > 0");
    if ($result) {
        $stats['products'] = mysqli_fetch_assoc($result)['total'];
    }
}

if (tableExists('categories')) {
    $result = executeQuery("SELECT COUNT(*) as total FROM categories");
    if ($result) {
        $stats['categories'] = mysqli_fetch_assoc($result)['total'];
    }
}

if (tableExists('users')) {
    $result = executeQuery("SELECT COUNT(*) as total FROM users WHERE user_type = 'customer'");
    if ($result) {
        $stats['users'] = mysqli_fetch_assoc($result)['total'];
    }
}

if (tableExists('orders')) {
    $result = executeQuery("SELECT COUNT(*) as total FROM orders");
    if ($result) {
        $stats['orders'] = mysqli_fetch_assoc($result)['total'];
    }
}

// for products
$featured = [];
$trending = [];
$latest = [];

if (tableExists('products') && tableExists('categories')) {
    $featured_result = executeQuery("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.featured = 1 AND p.quantity > 0 ORDER BY p.id DESC LIMIT 4");
    if ($featured_result) {
        while ($row = mysqli_fetch_assoc($featured_result)) {
            $featured[] = $row;
        }
    }
    
    $trending_result = executeQuery("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.trending = 1 AND p.quantity > 0 ORDER BY p.id DESC LIMIT 4");
    if ($trending_result) {
        while ($row = mysqli_fetch_assoc($trending_result)) {
            $trending[] = $row;
        }
    }
    
    $latest_result = executeQuery("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.latest = 1 AND p.quantity > 0 ORDER BY p.id DESC LIMIT 4");
    if ($latest_result) {
        while ($row = mysqli_fetch_assoc($latest_result)) {
            $latest[] = $row;
        }
    }
}

include 'includes/header.php';
?>

<main>
    <section class="hero-section">
        <div class="container">
            <div class="glass-hero animate-zoom">
                <div class="hero-content">
                    <h1 class="hero-title">
                        <span class="typing-text" id="typingText"></span>
                        <span class="hero-subtitle">Premium Streetwear</span>
                    </h1>
                    <p class="hero-description">Elevate your style with premium streetwear designed for the bold. Quality meets luxury.</p>
                    <div class="hero-buttons">
                        <a href="products.php" class="btn btn-primary btn-lg"><i class="fas fa-shopping-bag"></i> Shop Now</a>
                        <a href="#featured" class="btn btn-secondary btn-lg"><i class="fas fa-arrow-down"></i> Explore</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="stats-section">
        <div class="container">
            <div class="row row-4 stats-grid">
                <div class="glass-card stat-card animate-fade"><div class="stat-number" data-count="<?php echo $stats['products']; ?>">0</div><div class="stat-label">Products</div></div>
                <div class="glass-card stat-card animate-fade"><div class="stat-number" data-count="<?php echo $stats['categories']; ?>">0</div><div class="stat-label">Categories</div></div>
                <div class="glass-card stat-card animate-fade"><div class="stat-number" data-count="<?php echo $stats['users']; ?>">0</div><div class="stat-label">Happy Customers</div></div>
                <div class="glass-card stat-card animate-fade"><div class="stat-number" data-count="<?php echo $stats['orders']; ?>">0</div><div class="stat-label">Orders Delivered</div></div>
            </div>
        </div>
    </section>

    <section id="featured" class="products-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Featured Products</h2>
                <p class="section-subtitle">Our handpicked collection of premium streetwear</p>
            </div>
            <div class="row row-4">
                <?php if (!empty($featured)): ?>
                    <?php foreach ($featured as $p): ?>
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
                            <div class="product-actions">
                                <a href="product.php?id=<?php echo $p['id']; ?>" class="btn btn-secondary btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted" style="text-align:center; width:100%;">No featured products available.</p>
                <?php endif; ?>
            </div>
            <div class="section-footer"><a href="products.php" class="btn btn-primary">View All Products</a></div>
        </div>
    </section>

    <section class="products-section" style="background:rgba(0,0,0,0.2);">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Trending Now</h2>
                <p class="section-subtitle">What's hot in the streetwear world</p>
            </div>
            <div class="row row-4">
                <?php if (!empty($trending)): ?>
                    <?php foreach ($trending as $p): ?>
                    <div class="glass-product animate-fade">
                        <div class="product-image">
                            <img src="uploads/<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                            <?php if ($p['discount_price'] && $p['discount_price'] < $p['price']): ?>
                            <span class="discount-badge"><?php echo round((($p['price'] - $p['discount_price']) / $p['price']) * 100); ?>% OFF</span>
                            <?php endif; ?>
                            <span class="trending-badge"><i class="fas fa-fire"></i> Trending</span>
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
                            <div class="product-actions">
                                <a href="product.php?id=<?php echo $p['id']; ?>" class="btn btn-secondary btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted" style="text-align:center; width:100%;">No trending products available.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="products-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">New Arrivals</h2>
                <p class="section-subtitle">Fresh styles, freshly dropped</p>
            </div>
            <div class="row row-4">
                <?php if (!empty($latest)): ?>
                    <?php foreach ($latest as $p): ?>
                    <div class="glass-product animate-fade">
                        <div class="product-image">
                            <img src="uploads/<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                            <?php if ($p['discount_price'] && $p['discount_price'] < $p['price']): ?>
                            <span class="discount-badge"><?php echo round((($p['price'] - $p['discount_price']) / $p['price']) * 100); ?>% OFF</span>
                            <?php endif; ?>
                            <span class="new-badge">New</span>
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
                            <div class="product-actions">
                                <a href="product.php?id=<?php echo $p['id']; ?>" class="btn btn-secondary btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted" style="text-align:center; width:100%;">No new arrivals available.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>
<?php include 'includes/footer.php'; ?>