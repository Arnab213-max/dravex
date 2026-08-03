<?php
if (!isset($_SESSION)) {
    session_start();
}
// Get cart count using pure PHP
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $count_result = executeQuery("SELECT SUM(quantity) as total FROM cart WHERE user_id = $user_id");
    if ($count_result && mysqli_num_rows($count_result) > 0) {
        $count_data = mysqli_fetch_assoc($count_result);
        $cart_count = (int)$count_data['total'];
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'default'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DRAVEX - Premium Streetwear</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/glass.css">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body>
    <div class="particles" id="particles"></div>
    <div id="toastContainer"></div>

    <nav class="navbar">
        <div class="container">
            <div class="nav-wrapper">
                <a href="index.php" class="logo">
                    <i class="fas fa-crown"></i>
                    <span>DRAVEX</span>
                </a>

                <ul class="nav-menu">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li>
                            <a href="cart.php">
                                <i class="fas fa-shopping-cart"></i> 
                                Cart 
                                <span id="cartCount" class="cart-badge" style="background:var(--gold); color:#0a0a0f; border-radius:50%; padding:0.05rem 0.5rem; font-size:0.7rem; font-weight:700; margin-left:0.25rem;">
                                    <?php echo $cart_count; ?>
                                </span>
                            </a>
                        </li>
                        <li><a href="profile.php"><i class="fas fa-user"></i></a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i></a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                    <li>
                        <div class="theme-selector">
                            <i class="fas fa-palette" style="color: var(--gold);"></i>
                            <select id="themeSelect">
                                <option value="default">Default</option>
                                <option value="dark">Dark</option>
                                <option value="blue">Blue</option>
                                <option value="green">Green</option>
                                <option value="red">Red</option>
                                <option value="purple">Purple</option>
                                <option value="orange">Orange</option>
                                <option value="pink">Pink</option>
                                <option value="cyan">Cyan</option>
                                <option value="gray">Gray</option>
                            </select>
                        </div>
                    </li>
                </ul>

                <button class="menu-toggle" id="menuToggle">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>

        <div class="mobile-menu" id="mobileMenu">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li>
                        <a href="cart.php">
                            <i class="fas fa-shopping-cart"></i> 
                            Cart 
                            <span id="mobileCartCount" style="background:var(--gold); color:#0a0a0f; border-radius:50%; padding:0.05rem 0.5rem; font-size:0.7rem; font-weight:700; margin-left:0.25rem;">
                                <?php echo $cart_count; ?>
                            </span>
                        </a>
                    </li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
                <li>
                    <div class="theme-selector-mobile">
                        <i class="fas fa-palette" style="color: var(--gold);"></i>
                        <select id="themeSelectMobile">
                            <option value="default">Default</option>
                            <option value="dark">Dark</option>
                            <option value="blue">Blue</option>
                            <option value="green">Green</option>
                            <option value="red">Red</option>
                            <option value="purple">Purple</option>
                            <option value="orange">Orange</option>
                            <option value="pink">Pink</option>
                            <option value="cyan">Cyan</option>
                            <option value="gray">Gray</option>
                        </select>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
    <script src="js/script.js"></script>