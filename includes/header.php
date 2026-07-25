<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CodeCraze & Threads</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/glass.css">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body>
    <div class="particles" id="particles"></div>
    <div id="toastContainer"></div>

    <nav class="navbar">
        <div class="container">
            <div class="nav-wrapper">
                <a href="index.php" class="logo">
                    <i class="fas fa-code"></i>
                    <span>CodeCraze</span>
                    <span class="logo-amp">&amp; Threads</span>
                </a>

                <ul class="nav-menu">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="cart.php">Cart <span id="cartCount" class="cart-badge">0</span></a></li>
                        <li><a href="orders.php">Orders</a></li>
                        <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin'): ?>
                            <li><a href="admin/dashboard.php">Admin</a></li>
                        <?php endif; ?>
                        <li><a href="profile.php"><i class="fas fa-user"></i></a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i></a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>

                <button class="menu-toggle" id="menuToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
        <div class="mobile-menu" id="mobileMenu">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="cart.php">Cart <span id="mobileCartCount">0</span></a></li>
                    <li><a href="orders.php">Orders</a></li>
                    <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin'): ?>
                        <li><a href="admin/dashboard.php">Admin</a></li>
                    <?php endif; ?>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <script src="js/script.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Particles
        const particles = document.getElementById('particles');
        for (let i = 0; i < 15; i++) {
            const p = document.createElement('div');
            p.className = 'particle';
            const s = Math.random() * 4 + 2;
            p.style.width = s + 'px';
            p.style.height = s + 'px';
            p.style.left = Math.random() * 100 + '%';
            p.style.animationDuration = Math.random() * 20 + 10 + 's';
            p.style.animationDelay = Math.random() * 10 + 's';
            p.style.background = Math.random() > 0.5 ? '#8b5cf6' : '#06b6d4';
            particles.appendChild(p);
        }

        // Mobile menu
        const toggle = document.getElementById('menuToggle');
        const menu = document.getElementById('mobileMenu');
        if (toggle && menu) {
            toggle.addEventListener('click', function() {
                this.classList.toggle('active');
                menu.classList.toggle('active');
            });
            menu.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', function() {
                    toggle.classList.remove('active');
                    menu.classList.remove('active');
                });
            });
        }

        // Toast
        window.showToast = function(msg, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `<span>${msg}</span><button class="toast-close">&times;</button>`;
            container.appendChild(toast);
            setTimeout(() => { toast.classList.add('fade-out'); setTimeout(() => toast.remove(), 300); }, 3000);
            toast.querySelector('.toast-close').onclick = function() {
                toast.classList.add('fade-out');
                setTimeout(() => toast.remove(), 300);
            };
        };

        // Cart counter
        window.updateCart = function() {
            <?php if (isset($_SESSION['user_id'])): ?>
            fetch('api/cart_count.php')
                .then(r => r.json())
                .then(d => {
                    document.getElementById('cartCount').textContent = d.count || 0;
                    document.getElementById('mobileCartCount').textContent = d.count || 0;
                });
            <?php endif; ?>
        };
        updateCart();
    });
    </script>