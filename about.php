<?php

require_once 'config/database.php';
require_once 'config/session.php';

include 'includes/header.php';
?>

<main>
    <div class="container" style="padding-top:var(--spacing-xl); padding-bottom:var(--spacing-xl);">
        <div class="glass-card animate-zoom" style="max-width:800px; margin:0 auto; text-align:center;">
            <h1 style="color:var(--gold); font-size:3rem; margin-bottom:var(--spacing-md);">About DRAVEX</h1>
            <p style="color:var(--text-secondary); font-size:1.2rem; margin-bottom:var(--spacing-lg);">Wear Confidence. Built Different.</p>
            
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:var(--spacing-lg); margin:var(--spacing-xl) 0;">
                <div>
                    <i class="fas fa-crown" style="font-size:2.5rem; color:var(--gold); margin-bottom:var(--spacing-sm);"></i>
                    <h4 style="color:var(--text-primary);">Premium Quality</h4>
                    <p style="color:var(--text-muted); font-size:0.9rem;">Only the finest materials for streetwear that lasts.</p>
                </div>
                <div>
                    <i class="fas fa-star" style="font-size:2.5rem; color:var(--gold); margin-bottom:var(--spacing-sm);"></i>
                    <h4 style="color:var(--text-primary);">Luxury Style</h4>
                    <p style="color:var(--text-muted); font-size:0.9rem;">Elevate your wardrobe with our exclusive collections.</p>
                </div>
                <div>
                    <i class="fas fa-gem" style="font-size:2.5rem; color:var(--gold); margin-bottom:var(--spacing-sm);"></i>
                    <h4 style="color:var(--text-primary);">Exclusive Brand</h4>
                    <p style="color:var(--text-muted); font-size:0.9rem;">Join thousands of satisfied customers worldwide.</p>
                </div>
            </div>
            
            <p style="color:var(--text-secondary); line-height:1.8;">DRAVEX is a premium streetwear brand dedicated to the bold, the fearless, and the confident. We believe that fashion is a statement of who you are, and we're here to help you make that statement with style.</p>
            
            <a href="products.php" class="btn btn-primary" style="margin-top:var(--spacing-lg);">Explore Collection</a>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>