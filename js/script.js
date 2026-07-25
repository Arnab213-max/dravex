/**
 * Main JavaScript
 * CodeCraze & Threads
 */

// Add to cart function
function addToCart(productId, quantity) {
    if (!loggedIn) {
        window.location.href = 'login.php';
        return;
    }

    fetch('api/add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId, quantity: quantity })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Product added to cart!', 'success');
            updateCart();
        } else {
            showToast(data.message || 'Failed to add to cart', 'error');
        }
    })
    .catch(() => showToast('Error adding to cart', 'error'));
}

// Typing effect
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
            setTimeout(() => isDeleting = true, 2000);
        } else if (isDeleting && charIndex === 0) {
            isDeleting = false;
            wordIndex = (wordIndex + 1) % words.length;
        }
        
        setTimeout(typeEffect, isDeleting ? 100 : 150);
    }
    typeEffect();
});

// Counter animation
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.stat-number').forEach(el => {
        const target = parseInt(el.dataset.count);
        let current = 0;
        const increment = Math.ceil(target / 50);
        const interval = setInterval(() => {
            current += increment;
            if (current >= target) { current = target; clearInterval(interval); }
            el.textContent = current;
        }, 40);
    });
});