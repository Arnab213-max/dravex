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
    <title>Login - DRAVEX</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/glass.css">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="css/forms.css">
    <style>
        body.login-page {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(ellipse at 50% 40%, #1a0a2e, #0a0a0f 70%);
            font-family: 'Inter', sans-serif;
            background-attachment: fixed;
        }

        .login-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 1000px;
            width: 100%;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(212, 175, 55, 0.08);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            min-height: 600px;
        }

        .login-form-side {
            padding: 40px 35px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-image-side {
            background: rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border-left: 1px solid rgba(255, 255, 255, 0.03);
        }

        .slider-container {
            position: relative;
            width: 100%;
            height: 100%;
            min-height: 500px;
            overflow: hidden;
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transform: scale(1.1);
            transition: opacity 1.5s ease, transform 1.5s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            text-align: center;
            background-size: cover;
            background-position: center;
        }

        .slide.active {
            opacity: 1;
            transform: scale(1);
        }

        .slide-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(26, 10, 46, 0.7), rgba(13, 13, 20, 0.5));
            z-index: 1;
        }

        .slide-content {
            position: relative;
            z-index: 2;
            color: white;
        }

        .slide-content .brand-name {
            font-size: 3.5rem;
            font-weight: 900;
            letter-spacing: 5px;
            background: linear-gradient(135deg, #d4af37, #f5d76e, #d4af37);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: block;
            line-height: 1.2;
        }

        .slide-content .brand-tagline {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.3);
            letter-spacing: 8px;
            text-transform: uppercase;
            font-weight: 300;
            margin-top: 5px;
        }

        .slide-content .brand-name-small {
            font-size: 1rem;
            font-weight: 300;
            color: rgba(255, 255, 255, 0.1);
            letter-spacing: 12px;
            text-transform: uppercase;
            margin-top: 15px;
        }

        .slide-content .brand-sub {
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.08);
            letter-spacing: 10px;
            text-transform: uppercase;
            margin-top: 5px;
        }

        .slide-content .brand-different {
            font-size: 0.8rem;
            color: rgba(212, 175, 55, 0.3);
            letter-spacing: 6px;
            text-transform: uppercase;
            font-weight: 500;
            margin-top: 10px;
            border-top: 1px solid rgba(212, 175, 55, 0.1);
            padding-top: 15px;
        }

        .slide-content .slide-icon {
            font-size: 3rem;
            color: rgba(212, 175, 55, 0.15);
            margin-bottom: 20px;
        }

        .slide-indicators {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 8px;
            z-index: 3;
        }

        .slide-indicators .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .slide-indicators .dot.active {
            background: #d4af37;
            width: 24px;
            border-radius: 4px;
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.3);
        }

        .login-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .login-header .brand-name {
            font-size: 1.8rem;
            font-weight: 900;
            letter-spacing: 3px;
            background: linear-gradient(135deg, #d4af37, #f5d76e, #d4af37);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: block;
        }

        .login-header .brand-tagline {
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.25);
            letter-spacing: 5px;
            text-transform: uppercase;
            font-weight: 300;
        }

        .login-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffffff;
            margin-top: 20px;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }

        .login-subtitle {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.35);
            font-weight: 300;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 0.7rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.35);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 12px;
            color: #ffffff;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-group input:focus {
            border-color: rgba(212, 175, 55, 0.3);
            background: rgba(255, 255, 255, 0.06);
            box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.05);
        }

        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.12);
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 45px;
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.2);
            cursor: pointer;
            padding: 5px;
            font-size: 0.9rem;
            transition: 0.3s;
        }

        .password-toggle:hover {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
            margin-top: 5px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.3);
            cursor: pointer;
        }

        .remember-me input[type="checkbox"] {
            width: 15px;
            height: 15px;
            accent-color: #d4af37;
            cursor: pointer;
        }

        .forgot-link {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.2);
            text-decoration: none;
            transition: 0.3s;
        }

        .forgot-link:hover {
            color: #d4af37;
        }

        .login-btn {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #d4af37, #f5d76e);
            border: none;
            border-radius: 12px;
            color: #0a0a0f;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(212, 175, 55, 0.3);
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 22px 0;
        }

        .divider-line {
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.04);
        }

        .divider-text {
            font-size: 0.6rem;
            color: rgba(255, 255, 255, 0.12);
            text-transform: uppercase;
            letter-spacing: 2px;
            white-space: nowrap;
            font-weight: 300;
        }

        .social-login {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .social-btn {
            flex: 1;
            padding: 10px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            color: rgba(255, 255, 255, 0.35);
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: 'Inter', sans-serif;
            text-decoration: none;
        }

        .social-btn:hover {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(212, 175, 55, 0.12);
            color: rgba(255, 255, 255, 0.6);
        }

        .social-btn i {
            font-size: 0.9rem;
        }

        .social-btn.google i {
            color: #ea4335;
        }

        .social-btn.facebook i {
            color: #1877f2;
        }

        .login-footer {
            text-align: center;
            margin-top: 22px;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.25);
        }

        .login-footer a {
            color: #d4af37;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
        }

        .login-footer a:hover {
            color: #f5d76e;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.12);
            color: #ef4444;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 0.8rem;
            margin-bottom: 18px;
            text-align: center;
        }

        .particles {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: -1;
            overflow: hidden;
        }

        @media (max-width: 900px) {
            .login-wrapper {
                grid-template-columns: 1fr;
                max-width: 450px;
                min-height: auto;
            }
            .login-image-side {
                display: none;
            }
            .login-form-side {
                padding: 35px 25px;
            }
        }

        @media (max-width: 480px) {
            .login-form-side {
                padding: 25px 18px;
            }
            .login-header .brand-name {
                font-size: 1.5rem;
            }
            .login-title {
                font-size: 1.2rem;
            }
            .social-login {
                flex-direction: column;
            }
        }
    </style>
</head>
<body class="login-page">
    <div class="particles" id="particles"></div>

    <div class="login-wrapper animate-zoom">
        <div class="login-form-side">
            <div class="login-header">
                <span class="brand-name">DRAVEX</span>
                <div class="brand-tagline">WEAR CONFIDENCE</div>
                
                <h1 class="login-title">Welcome Back</h1>
                <p class="login-subtitle">Login to continue to your account<br>and explore our latest collection.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Email or Phone Number</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter your email" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <button type="button" class="password-toggle" id="passwordToggle">
                            <i class="fas fa-eye" id="passwordIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        <span>Remember Me</span>
                    </label>
                    <a href="#" class="forgot-link">Forgot Password?</a>
                </div>

                <button type="submit" class="login-btn">LOGIN</button>
            </form>

            <div class="divider">
                <span class="divider-line"></span>
                <span class="divider-text">OR CONTINUE WITH</span>
                <span class="divider-line"></span>
            </div>

            <div class="social-login">
                <a href="#" class="social-btn google">
                    <i class="fab fa-google"></i> Google
                </a>
                <a href="#" class="social-btn facebook">
                    <i class="fab fa-facebook-f"></i> Facebook
                </a>
            </div>

            <div class="login-footer">
                Don't have an account? <a href="register.php">Sign Up</a>
            </div>
        </div>

        <div class="login-image-side" id="sliderContainer">
            <div class="slider-container">
                <!-- Slide 1 -->
                <div class="slide active" style="background: linear-gradient(135deg, #1a0a2e, #2d1b69, #0a0a0f);">
                    <div class="slide-overlay"></div>
                    <div class="slide-content">
                        <div class="slide-icon"><i class="fas fa-crown"></i></div>
                        <span class="brand-name">DRAVEX</span>
                        <div class="brand-tagline">WEAR CONFIDENCE</div>
                        <div class="brand-name-small">DRAVEX</div>
                        <div class="brand-sub">WEAR CONFIDENCE</div>
                        <div class="brand-different">BUILT DIFFERENT</div>
                    </div>
                </div>

                <!-- Slide 2 -->
                <div class="slide" style="background: linear-gradient(135deg, #0a0a0f, #1a0a3e, #2d1b69);">
                    <div class="slide-overlay"></div>
                    <div class="slide-content">
                        <div class="slide-icon"><i class="fas fa-star"></i></div>
                        <span class="brand-name">DRAVEX</span>
                        <div class="brand-tagline">WEAR CONFIDENCE</div>
                        <div class="brand-name-small">DRAVEX</div>
                        <div class="brand-sub">WEAR CONFIDENCE</div>
                        <div class="brand-different">BUILT DIFFERENT</div>
                    </div>
                </div>

                <!-- Slide 3 -->
                <div class="slide" style="background: linear-gradient(135deg, #2d1b69, #0a0a0f, #1a0a2e);">
                    <div class="slide-overlay"></div>
                    <div class="slide-content">
                        <div class="slide-icon"><i class="fas fa-gem"></i></div>
                        <span class="brand-name">DRAVEX</span>
                        <div class="brand-tagline">WEAR CONFIDENCE</div>
                        <div class="brand-name-small">DRAVEX</div>
                        <div class="brand-sub">WEAR CONFIDENCE</div>
                        <div class="brand-different">BUILT DIFFERENT</div>
                    </div>
                </div>

                <!-- Slide 4 -->
                <div class="slide" style="background: linear-gradient(135deg, #1a0a2e, #0a0a0f, #2d1b69);">
                    <div class="slide-overlay"></div>
                    <div class="slide-content">
                        <div class="slide-icon"><i class="fas fa-crown"></i></div>
                        <span class="brand-name">DRAVEX</span>
                        <div class="brand-tagline">WEAR CONFIDENCE</div>
                        <div class="brand-name-small">DRAVEX</div>
                        <div class="brand-sub">WEAR CONFIDENCE</div>
                        <div class="brand-different">BUILT DIFFERENT</div>
                    </div>
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
        var toggleBtn = document.getElementById('passwordToggle');
        var passwordInput = document.getElementById('password');
        var icon = document.getElementById('passwordIcon');
        
        if (toggleBtn && passwordInput && icon) {
            toggleBtn.addEventListener('click', function() {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.className = 'fas fa-eye-slash';
                } else {
                    passwordInput.type = 'password';
                    icon.className = 'fas fa-eye';
                }
            });
        }

        // Slider
        var slides = document.querySelectorAll('.slide');
        var dots = document.querySelectorAll('.dot');
        var currentSlide = 0;
        var slideInterval;

        function goToSlide(index) {
            slides.forEach(function(slide) {
                slide.classList.remove('active');
            });
            dots.forEach(function(dot) {
                dot.classList.remove('active');
            });
            
            slides[index].classList.add('active');
            dots[index].classList.add('active');
            currentSlide = index;
        }

        function nextSlide() {
            var next = (currentSlide + 1) % slides.length;
            goToSlide(next);
        }

        function startAutoSlide() {
            slideInterval = setInterval(nextSlide, 4000);
        }

        function stopAutoSlide() {
            clearInterval(slideInterval);
        }

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

        // Particles
        var particles = document.getElementById('particles');
        if (particles) {
            for (var i = 0; i < 25; i++) {
                var p = document.createElement('div');
                p.className = 'particle';
                var s = Math.random() * 4 + 2;
                p.style.width = s + 'px';
                p.style.height = s + 'px';
                p.style.left = Math.random() * 100 + '%';
                p.style.animationDuration = Math.random() * 20 + 10 + 's';
                p.style.animationDelay = Math.random() * 10 + 's';
                p.style.background = Math.random() > 0.5 ? '#d4af37' : '#8b5cf6';
                particles.appendChild(p);
            }
        }
    });
    </script>
</body>
</html>