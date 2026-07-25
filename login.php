<?php
require_once 'config/database.php';
require_once 'config/session.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, full_name, email, password, user_type, profile_image FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['profile_image'] = $user['profile_image'];
                $_SESSION['last_activity'] = time();
                
                header('Location: ' . ($user['user_type'] === 'admin' ? 'admin/dashboard.php' : 'index.php'));
                exit();
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CodeCraze & Threads</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/glass.css">
    <link rel="stylesheet" href="css/animations.css">
    <style>
        .login-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 1100px;
            width: 100%;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-radius: var(--radius-xl);
            overflow: hidden;
            border: 1px solid var(--glass-border);
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            min-height: 600px;
        }
        .login-form-side { padding: 3rem 2.5rem; display: flex; flex-direction: column; justify-content: center; }
        .login-form-side .auth-logo { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 2rem; display: inline-block; }
        .login-form-side .auth-logo i { color: var(--purple); }
        .login-form-side h2 { font-size: 2rem; margin-bottom: 0.5rem; background: var(--purple-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .login-form-side .subtitle { color: var(--text-secondary); margin-bottom: 2rem; }
        .login-image-side { position: relative; overflow: hidden; min-height: 500px; }
        .login-image-side .slide { position: absolute; inset: 0; opacity: 0; transform: scale(1.1); transition: opacity 1.5s ease, transform 1.5s ease; display: flex; align-items: center; justify-content: center; flex-direction: column; padding: 2rem; background-size: cover; background-position: center; }
        .login-image-side .slide.active { opacity: 1; transform: scale(1); }
        .login-image-side .slide-content { text-align: center; color: white; z-index: 2; position: relative; }
        .login-image-side .slide-content i { font-size: 4rem; margin-bottom: 1rem; color: var(--purple); text-shadow: 0 0 30px rgba(139,92,246,0.5); }
        .login-image-side .slide-content h3 { font-size: 1.8rem; font-weight: 700; }
        .login-image-side .slide-content p { opacity: 0.9; max-width: 300px; margin: 0 auto; }
        .login-image-side .slide-overlay { position: absolute; inset: 0; background: linear-gradient(135deg, rgba(108,43,217,0.7), rgba(6,182,212,0.5)); z-index: 1; }
        .slide-indicators { position: absolute; bottom: 2rem; left: 50%; transform: translateX(-50%); display: flex; gap: 0.5rem; z-index: 3; }
        .slide-indicators .dot { width: 10px; height: 10px; border-radius: 50%; background: rgba(255,255,255,0.4); cursor: pointer; transition: 0.3s; }
        .slide-indicators .dot.active { background: white; transform: scale(1.2); }
        @media (max-width: 992px) { .login-wrapper { grid-template-columns: 1fr; max-width: 500px; } .login-image-side { min-height: 300px; order: -1; } .login-form-side { padding: 2rem 1.5rem; } .login-image-side .slide-content i { font-size: 3rem; } .login-image-side .slide-content h3 { font-size: 1.4rem; } }
        @media (max-width: 576px) { .login-wrapper { margin: 1rem; } .login-form-side { padding: 1.5rem 1rem; } .login-image-side { min-height: 200px; } .login-form-side h2 { font-size: 1.5rem; } }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="login-wrapper animate-zoom">
            <div class="login-form-side">
                <a href="index.php" class="auth-logo"><i class="fas fa-code"></i> CodeCraze &amp; Threads</a>
                <h2>Welcome Back</h2>
                <p class="subtitle">Login to continue your style journey</p>
                <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter your email" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-sign-in-alt"></i> Login</button>
                    <div class="auth-footer"><p>Don't have an account? <a href="register.php">Register here</a></p></div>
                </form>
            </div>
            <div class="login-image-side" id="sliderContainer">
                <div class="slide active" style="background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460);">
                    <div class="slide-overlay"></div>
                    <div class="slide-content"><i class="fas fa-tshirt"></i><h3>Premium Quality</h3><p>High-quality coding apparel designed for comfort and style</p></div>
                </div>
                <div class="slide" style="background: linear-gradient(135deg, #1a1a2e, #2d1b69, #0f3460);">
                    <div class="slide-overlay"></div>
                    <div class="slide-content"><i class="fas fa-code"></i><h3>Unique Designs</h3><p>Express your passion with our exclusive coding-themed designs</p></div>
                </div>
                <div class="slide" style="background: linear-gradient(135deg, #0f3460, #16213e, #1a1a2e);">
                    <div class="slide-overlay"></div>
                    <div class="slide-content"><i class="fas fa-truck-fast"></i><h3>Fast Delivery</h3><p>Get your favorite coding gear delivered to your doorstep</p></div>
                </div>
                <div class="slide" style="background: linear-gradient(135deg, #2d1b69, #0f3460, #1a1a2e);">
                    <div class="slide-overlay"></div>
                    <div class="slide-content"><i class="fas fa-star"></i><h3>Customer Love</h3><p>Join thousands of satisfied developers who love our products</p></div>
                </div>
                <div class="slide-indicators">
                    <span class="dot active" data-slide="0"></span>
                    <span class="dot" data-slide="1"></span>
                    <span class="dot" data-slide="2"></span>
                    <span class="dot" data-slide="3"></span>
                </div>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var slides = document.querySelectorAll('.slide');
        var dots = document.querySelectorAll('.dot');
        var current = 0;
        var interval;
        function goToSlide(index) {
            slides.forEach(function(s) { s.classList.remove('active'); });
            dots.forEach(function(d) { d.classList.remove('active'); });
            slides[index].classList.add('active');
            dots[index].classList.add('active');
            current = index;
        }
        function nextSlide() { goToSlide((current + 1) % slides.length); }
        function startAutoSlide() { interval = setInterval(nextSlide, 4000); }
        function stopAutoSlide() { clearInterval(interval); }
        dots.forEach(function(dot, index) {
            dot.addEventListener('click', function() {
                stopAutoSlide();
                goToSlide(index);
                startAutoSlide();
            });
        });
        var container = document.getElementById('sliderContainer');
        container.addEventListener('mouseenter', stopAutoSlide);
        container.addEventListener('mouseleave', startAutoSlide);
        startAutoSlide();
    });
    </script>
    <script src="js/script.js"></script>
</body>
</html> 