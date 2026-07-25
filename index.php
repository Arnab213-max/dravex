<?php
require_once 'config/database.php';
require_once 'config/session.php';

$featured = executeQuery("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.featured = 1 AND p.quantity > 0 ORDER BY p.id DESC LIMIT 4");
$trending = executeQuery("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.trending = 1 AND p.quantity > 0 ORDER BY p.id DESC LIMIT 4");
$latest = executeQuery("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.latest = 1 AND p.quantity > 0 ORDER BY p.id DESC LIMIT 4");

$stats = [
    'products' => mysqli_fetch_assoc(executeQuery("SELECT COUNT(*) as total FROM products WHERE quantity > 0"))['total'],
    'categories' => mysqli_fetch_assoc(executeQuery("SELECT COUNT(*) as total FROM categories"))['total'],
    'users' => mysqli_fetch_assoc(executeQuery("SELECT COUNT(*) as total FROM users WHERE user_type = 'customer'"))['total'],
    'orders' => mysqli_fetch_assoc(executeQuery("SELECT COUNT(*) as total FROM orders"))['total']
];

include 'includes/header.php';
?>

<main>
    <section class="hero-section">
        <div class="container">
            <div class="glass-hero animate-zoom">
                <div class="hero-content">
                    <h1 class="hero-title">
                        <span class="typing-text" id="typingText"></span>
                        <span class="hero-subtitle">Wear Your Code. Style Your Story.</span>
                    </h1>
                    <p class="hero-description">Discover premium coding themed apparel designed for developers, programmers, and tech enthusiasts.</p>
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
                <p class="section-subtitle">Our handpicked collection of premium coding apparel</p>
            </div>
            <div class="row row-4">
                <?php while ($p = mysqli_fetch_assoc($featured)): ?>
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
                            <span class="original-price">₹<?php echo number_format($p['price'], 2); ?></span>
                            <span>₹<?php echo number_format($p['discount_price'], 2); ?></span>
                            <?php else: ?>
                            <span>₹<?php echo number_format($p['price'], 2); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="product-actions">
                            <a href="product.php?id=<?php echo $p['id']; ?>" class="btn btn-secondary btn-sm">View</a>
                            <?php if ($p['quantity'] > 0 && isset($_SESSION['user_id'])): ?>
                            <button onclick="addToCart(<?php echo $p['id']; ?>, 1)" class="btn btn-primary btn-sm"><i class="fas fa-cart-plus"></i></button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <div class="section-footer"><a href="products.php" class="btn btn-primary">View All Products</a></div>
        </div>
    </section>

    <section class="products-section" style="background:var(--dark-secondary);">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Trending Now</h2>
                <p class="section-subtitle">What's hot in the coding fashion world</p>
            </div>
            <div class="row row-4">
                <?php while ($p = mysqli_fetch_assoc($trending)): ?>
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
                            <span class="original-price">₹<?php echo number_format($p['price'], 2); ?></span>
                            <span>₹<?php echo number_format($p['discount_price'], 2); ?></span>
                            <?php else: ?>
                            <span>₹<?php echo number_format($p['price'], 2); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="product-actions">
                            <a href="product.php?id=<?php echo $p['id']; ?>" class="btn btn-secondary btn-sm">View</a>
                            <?php if ($p['quantity'] > 0 && isset($_SESSION['user_id'])): ?>
                            <button onclick="addToCart(<?php echo $p['id']; ?>, 1)" class="btn btn-cyan btn-sm"><i class="fas fa-cart-plus"></i></button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <section class="products-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">New Arrivals</h2>
                <p class="section-subtitle">Fresh styles freshly coded</p>
            </div>
            <div class="row row-4">
                <?php while ($p = mysqli_fetch_assoc($latest)): ?>
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
                            <span class="original-price">₹<?php echo number_format($p['price'], 2); ?></span>
                            <span>₹<?php echo number_format($p['discount_price'], 2); ?></span>
                            <?php else: ?>
                            <span>₹<?php echo number_format($p['price'], 2); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="product-actions">
                            <a href="product.php?id=<?php echo $p['id']; ?>" class="btn btn-secondary btn-sm">View</a>
                            <?php if ($p['quantity'] > 0 && isset($_SESSION['user_id'])): ?>
                            <button onclick="addToCart(<?php echo $p['id']; ?>, 1)" class="btn btn-primary btn-sm"><i class="fas fa-cart-plus"></i></button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const words = ['Fashion', 'Streetwear', 'Oversized', 'Premium', 'Trending', 'New Arrival', 'Minimal'];
    const el = document.getElementById('typingText');
    if (!el) return;
    let wordIndex = 0, charIndex = 0, isDeleting = false;
    function typeEffect() {
        const currentWord = words[wordIndex];
        if (isDeleting) {
            charIndex--;
            el.textContent = currentWord.substring(0, charIndex);
        } else {
            charIndex++;
            el.textContent = currentWord.substring(0, charIndex);
        }
        if (!isDeleting && charIndex === currentWord.length) {
            setTimeout(function() { isDeleting = true; }, 2000);
        } else if (isDeleting && charIndex === 0) {
            isDeleting = false;
            wordIndex = (wordIndex + 1) % words.length;
        }
        setTimeout(typeEffect, isDeleting ? 100 : 150);
    }
    typeEffect();
});

document.querySelectorAll('.stat-number').forEach(function(el) {
    const target = parseInt(el.dataset.count);
    let current = 0;
    const increment = Math.ceil(target / 50);
    const interval = setInterval(function() {
        current += increment;
        if (current >= target) { current = target; clearInterval(interval); }
        el.textContent = current;
    }, 40);
});

function addToCart(productId, quantity) {
    <?php if (!isset($_SESSION['user_id'])): ?>
    window.location.href = 'login.php';
    return;
    <?php endif; ?>
    fetch('api/add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId, quantity: quantity })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            showToast('Product added to cart!', 'success');
            updateCart();
        } else {
            showToast(data.message || 'Failed to add to cart', 'error');
        }
    })
    .catch(function() { showToast('Error adding to cart', 'error'); });
}
</script>

<?php include 'includes/footer.php'; ?> 