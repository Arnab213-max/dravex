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
    <!-- Hero Section with DRAVEX Video Showcase Background -->
    <section class="hero-section video-hero">
        <!-- DRAVEX Video Showcase Background -->
        <div class="dravex-video-bg">
            <!-- DRAVEX Text Animation -->
            <div class="dravex-video-text">
                <div class="dravex-video-brand">DRAVEX</div>
                <div class="dravex-video-tagline">WEAR CONFIDENCE</div>
                <div class="dravex-video-sub">BUILT DIFFERENT</div>
            </div>
            
            <!-- DRAVEX Text Particles -->
            <div class="dravex-particles" id="dravexParticles"></div>
            
            <!-- DRAVEX Glow Effect -->
            <div class="dravex-glow"></div>
            
            <!-- DRAVEX Text Shadow -->
            <div class="dravex-shadow"></div>
        </div>
        
        <!-- Dark Overlay -->
        <div class="hero-overlay"></div>
        
        <div class="container">
            <div class="hero-content-wrapper">
                <div class="hero-content animate-zoom">
        
                    <h1 class="hero-title">
                        <span class="typing-text" id="typingText"></span>
                        <span class="hero-subtitle">Premium Streetwear</span>
                    </h1>
                    <p class="hero-description">Elevate your style with premium streetwear designed for the bold. Quality meets luxury.</p>
                    <div class="hero-buttons">
                        <a href="products.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-bag"></i> Shop Now
                        </a>
                        <a href="#featured" class="btn btn-secondary btn-lg">
                            <i class="fas fa-arrow-down"></i> Explore
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
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

    <!-- Featured Products -->
    <section id="featured" class="products-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Featured Products</h2>
                <p class="section-subtitle">Our handpicked collection of premium streetwear</p>
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
                            <a href="product.php?id=<?php echo $p['id']; ?>" class="btn btn-secondary btn-sm">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <div class="section-footer"><a href="products.php" class="btn btn-primary">View All Products</a></div>
        </div>
    </section>

    <!-- Trending Products -->
    <section class="products-section" style="background:rgba(0,0,0,0.2);">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Trending Now</h2>
                <p class="section-subtitle">What's hot in the streetwear world</p>
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
                            <a href="product.php?id=<?php echo $p['id']; ?>" class="btn btn-secondary btn-sm">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Latest Products -->
    <section class="products-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">New Arrivals</h2>
                <p class="section-subtitle">Fresh styles, freshly dropped</p>
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
                            <a href="product.php?id=<?php echo $p['id']; ?>" class="btn btn-secondary btn-sm">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
</main>
<style>
.video-hero {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    overflow: hidden;
    padding: 0;
}
.dravex-video-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
    overflow: hidden;
    background: #0a0a0f;
}


.dravex-video-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 2;
    text-align: center;
    pointer-events: none;
    width: 100%;
    padding: 0 2rem;
}

.dravex-video-brand {
    font-size: 12rem;
    font-weight: 900;
    letter-spacing: 15px;
    background: linear-gradient(135deg, #d4af37, #f5d76e, #d4af37);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    background-size: 200% 200%;
    animation: textShine 4s ease-in-out infinite;
    text-shadow: none;
    font-family: 'Inter', sans-serif;
    opacity: 0.15;
    transform: scale(0.8);
    animation: textPulse 8s ease-in-out infinite, textShine 4s ease-in-out infinite;
}

.dravex-video-tagline {
    font-size: 2.5rem;
    letter-spacing: 20px;
    color: rgba(212, 175, 55, 0.1);
    text-transform: uppercase;
    font-weight: 300;
    margin-top: -0.5rem;
    animation: taglineFade 6s ease-in-out infinite;
}

.dravex-video-sub {
    font-size: 1.2rem;
    letter-spacing: 15px;
    color: rgba(255, 255, 255, 0.05);
    text-transform: uppercase;
    font-weight: 300;
    margin-top: 0.5rem;
    animation: subFade 6s ease-in-out infinite 2s;
}


@keyframes textShine {
    0%, 100% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
}

@keyframes textPulse {
    0%, 100% {
        opacity: 0.15;
        transform: scale(0.9);
    }
    50% {
        opacity: 0.25;
        transform: scale(1);
    }
}

@keyframes taglineFade {
    0%, 100% {
        opacity: 0.05;
        transform: translateY(10px);
    }
    50% {
        opacity: 0.15;
        transform: translateY(-10px);
    }
}

@keyframes subFade {
    0%, 100% {
        opacity: 0.02;
        transform: translateY(5px);
    }
    50% {
        opacity: 0.08;
        transform: translateY(-5px);
    }
}


.dravex-glow {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 60%;
    height: 60%;
    background: radial-gradient(ellipse at center, rgba(212, 175, 55, 0.08), transparent 70%);
    z-index: 1;
    pointer-events: none;
    animation: glowPulse 6s ease-in-out infinite;
}

@keyframes glowPulse {
    0%, 100% {
        transform: translate(-50%, -50%) scale(0.8);
        opacity: 0.5;
    }
    50% {
        transform: translate(-50%, -50%) scale(1.2);
        opacity: 1;
    }
}


.dravex-shadow {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 80%;
    height: 80%;
    background: radial-gradient(ellipse at center, rgba(212, 175, 55, 0.03), transparent 70%);
    z-index: 0;
    pointer-events: none;
    animation: shadowPulse 8s ease-in-out infinite;
}

@keyframes shadowPulse {
    0%, 100% {
        transform: translate(-50%, -50%) scale(1);
        opacity: 0.3;
    }
    50% {
        transform: translate(-50%, -50%) scale(1.3);
        opacity: 0.8;
    }
}

.dravex-particles {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
    pointer-events: none;
}

.particle-dravex {
    position: absolute;
    border-radius: 50%;
    background: #fffefb;
    opacity: 0;
}

@keyframes particleFloatDravex {
    0% {
        opacity: 0;
        transform: translateY(0) scale(0);
    }
    20% {
        opacity: 0.6;
        transform: translateY(-50px) scale(1);
    }
    50% {
        opacity: 0.3;
        transform: translateY(-150px) scale(0.8);
    }
    80% {
        opacity: 0.1;
        transform: translateY(-250px) scale(0.5);
    }
    100% {
        opacity: 0;
        transform: translateY(-350px) scale(0);
    }
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(180deg, 
        rgba(0, 0, 0, 0.3) 0%,
        rgba(0, 0, 0, 0.5) 50%,
        rgba(0, 0, 0, 0.8) 100%
    );
    z-index: 1;
}

.hero-content-wrapper {
    position: relative;
    z-index: 2;
    width: 100%;
    padding: 4rem 0;
}

.hero-badge {
    display: inline-block;
    padding: 0.3rem 1.5rem;
    background: rgba(212, 175, 55, 0.1);
    border: 1px solid rgba(212, 175, 55, 0.2);
    color: #d4af37;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 1.5rem;
    animation: fadeInUp 1s ease forwards;
}

.pulse-gold {
    animation: pulseGold 2s ease-in-out infinite;
}

@keyframes pulseGold {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(212, 175, 55, 0);
    }
    50% {
        box-shadow: 0 0 20px 5px rgba(212, 175, 55, 0.1);
    }
}


.hero-title {
    font-size: 4.5rem;
    font-weight: 900;
    margin-bottom: 1rem;
    animation: fadeInUp 1s ease 0.2s forwards;
    opacity: 0;
}

.typing-text {
    background: linear-gradient(135deg, #d4af37, #f5d76e, #d4af37);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    border-right: 3px solid #d4af37;
    padding-right: 5px;
    background-size: 200% 200%;
    animation: gradientMove 4s ease infinite;
}

.hero-subtitle {
    display: block;
    font-size: 1.8rem;
    font-weight: 300;
    color: rgba(255, 255, 255, 0.6);
    -webkit-text-fill-color: rgba(255, 255, 255, 0.6);
}

.hero-description {
    color: rgba(255, 255, 255, 0.5);
    max-width: 600px;
    margin: 1.5rem 0;
    font-size: 1.1rem;
    animation: fadeInUp 1s ease 0.4s forwards;
    opacity: 0;
}

.hero-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    animation: fadeInUp 1s ease 0.6s forwards;
    opacity: 0;
}

.btn {
    padding: 0.8rem 2.5rem;
    border-radius: 50px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary {
    background: linear-gradient(135deg, #d4af37, #f5d76e);
    color: #0a0a0f;
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 40px rgba(212, 175, 55, 0.4);
    color: #0a0a0f;
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: #ffffff;
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-3px);
    color: #ffffff;
}

.btn-lg {
    padding: 1rem 3rem;
    font-size: 1rem;
}


@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes gradientMove {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

/* Responsive */
@media (max-width: 992px) {
    .hero-title {
        font-size: 3.5rem;
    }
    .hero-subtitle {
        font-size: 1.4rem;
    }
    .dravex-video-brand {
        font-size: 8rem;
        letter-spacing: 10px;
    }
    .dravex-video-tagline {
        font-size: 2rem;
        letter-spacing: 15px;
    }
    .dravex-video-sub {
        font-size: 1rem;
        letter-spacing: 10px;
    }
}

@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    .hero-subtitle {
        font-size: 1.2rem;
    }
    .hero-buttons {
        flex-direction: column;
        align-items: center;
    }
    .hero-buttons .btn {
        width: 100%;
        max-width: 300px;
        justify-content: center;
    }
    .hero-badge {
        font-size: 0.6rem;
        padding: 0.2rem 1rem;
    }
    .dravex-video-brand {
        font-size: 5rem;
        letter-spacing: 5px;
    }
    .dravex-video-tagline {
        font-size: 1.5rem;
        letter-spacing: 10px;
    }
    .dravex-video-sub {
        font-size: 0.8rem;
        letter-spacing: 5px;
    }
}

@media (max-width: 576px) {
    .hero-title {
        font-size: 2rem;
    }
    .hero-subtitle {
        font-size: 1rem;
    }
    .dravex-video-brand {
        font-size: 3.5rem;
        letter-spacing: 3px;
    }
    .dravex-video-tagline {
        font-size: 1rem;
        letter-spacing: 5px;
    }
    .dravex-video-sub {
        font-size: 0.6rem;
        letter-spacing: 3px;
    }
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Typing 
    const words = ['Premium', 'Bold', 'Streetwear', 'Luxury', 'Urban', 'Style', 'DRAVEX'];
    const el = document.getElementById('typingText');
    if (el) {
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
    }

    // Stats
    document.querySelectorAll('.stat-number').forEach(function(el) {
        const target = parseInt(el.getAttribute('data-count'));
        if (isNaN(target)) target = 0;
        let current = 0;
        const increment = Math.ceil(target / 50);
        const interval = setInterval(function() {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(interval);
            }
            el.textContent = current;
        }, 40);
    });


    const container = document.getElementById('dravexParticles');
    if (container) {
        for (let i = 0; i < 30; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle-dravex';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.top = Math.random() * 100 + '%';
            particle.style.width = (Math.random() * 3 + 1) + 'px';
            particle.style.height = particle.style.width;
            particle.style.background = Math.random() > 0.5 ? '#d4af37' : '#f5d76e';
            particle.style.animationDelay = (Math.random() * 5) + 's';
            particle.style.animationDuration = (Math.random() * 3 + 4) + 's';
            container.appendChild(particle);
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>