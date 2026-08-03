<?php
require_once 'config/database.php';
require_once 'config/session.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$errors = [];
$form_data = ['full_name' => '', 'phone' => '', 'email' => '', 'password' => '', 'confirm_password' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data['full_name'] = sanitizeInput($_POST['full_name']);
    $form_data['phone'] = sanitizeInput($_POST['phone']);
    $form_data['email'] = sanitizeInput($_POST['email']);
    $form_data['password'] = $_POST['password'];
    $form_data['confirm_password'] = $_POST['confirm_password'];
    $terms = isset($_POST['terms']) ? true : false;
    
    if (empty($form_data['full_name']) || strlen($form_data['full_name']) < 3) {
        $errors['full_name'] = 'Full name is required (min 3 characters)';
    }
    if (empty($form_data['email']) || !filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Valid email is required';
    } else {
        $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check, "s", $form_data['email']);
        mysqli_stmt_execute($check);
        if (mysqli_num_rows(mysqli_stmt_get_result($check)) > 0) {
            $errors['email'] = 'Email already registered';
        }
        mysqli_stmt_close($check);
    }
    if (!empty($form_data['phone']) && !preg_match('/^[0-9]{10}$/', $form_data['phone'])) {
        $errors['phone'] = 'Enter valid 10-digit phone number';
    }
    if (empty($form_data['password']) || strlen($form_data['password']) < 8 || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $form_data['password'])) {
        $errors['password'] = 'Password must be 8+ chars with uppercase, lowercase and number';
    }
    if ($form_data['password'] !== $form_data['confirm_password']) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    if (!$terms) {
        $errors['terms'] = 'You must agree to the Terms & Conditions';
    }
    
    if (empty($errors)) {
        $hashed = password_hash($form_data['password'], PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "INSERT INTO users (full_name, email, password, phone) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $form_data['full_name'], $form_data['email'], $hashed, $form_data['phone']);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['flash']['success'] = 'Registration successful! Please login.';
            header('Location: login.php');
            exit();
        }
        mysqli_stmt_close($stmt);
    }
}

// Get ONE image from uploads folder
$display_image = 'd.jpg';
$upload_dir = 'uploads/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - DRAVEX</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/glass.css">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="css/forms.css">
    <style>
        body.register-page {
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

        .register-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 1100px;
            width: 100%;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(212, 175, 55, 0.08);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            min-height: 650px;
        }

        .register-form-side {
            padding: 35px 35px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .register-image-side {
            background: rgba(0, 0, 0, 0.23);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-left: 1px solid rgba(255, 255, 255, 0.03);
            padding: 30px;
        }

        .brand-text-side {
            text-align: center;
            margin-bottom: 25px;
            color: #fff;
        }

        .brand-text-side .brand-name {
            font-size: 2rem;
            font-weight: 900;
            letter-spacing: 4px;
            background: linear-gradient(135deg, #d4af37, #f5d76e, #d4af37);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: block;
        }

        .brand-text-side .brand-tagline {`
            font-size: 0.55rem;
            color: rgb(255, 255, 255);
            letter-spacing: 8px;
            text-transform: uppercase;
            font-weight: 500;
            margin-top: 5px;
        }

        .brand-text-side .brand-sub {
            font-size: 0.5rem;
            color: rgb(245, 245, 247);
            letter-spacing: 10px;
            text-transform: uppercase;
            margin-top: 8px;
        }

        .single-image-container {
            width: 100%;
            max-width: 250px;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(212, 175, 55, 0.1);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .single-image-container:hover {
            transform: scale(1.02);
            border-color: rgba(212, 175, 55, 0.3);
        }

        .single-image-container img {
            width: 100%;
            height: auto;
            display: block;
            aspect-ratio: 1/1;
            object-fit: cover;
        }

        .side-text {
            text-align: center;
            color: rgb(255, 255, 255);
            font-size: 0.8rem;
            letter-spacing: 5px;
            text-transform: uppercase;
            margin-top: 18px;
            font-weight: 600;
        }

        .side-text i {
            color: rgba(212, 175, 55, 0.12);
        }

        .side-text-bottom {
            text-align: center;
            color: rgb(255, 255, 255);
            font-size: 0.4rem;
            letter-spacing: 8px;
            text-transform: uppercase;
            margin-top: 10px;
        }

        .register-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .register-header .brand-name {
            font-size: 1.5rem;
            font-weight: 900;
            letter-spacing: 3px;
            background: linear-gradient(135deg, #d4af37, #f5d76e, #d4af37);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: block;
        }

        .register-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #ffffff;
            margin-top: 15px;
            margin-bottom: 3px;
            letter-spacing: 1px;
        }

        .register-subtitle {
            font-size: 0.9rem;
            color: rgba(235, 235, 52, 0.43);
            font-weight: 300;
        }

        .form-group {
            margin-bottom: 12px;
        }

        .form-group label {
            display: block;
            font-size: 0.65rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.35);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .form-group input {
            width: 100%;
            padding: 10px 14px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 10px;
            color: #ffffff;
            font-size: 0.85rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-group input:focus {
            border-color: rgba(212, 175, 55, 0.3);
            background: rgb(255, 255, 255);
            box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.05);
        }

        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.12);
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 40px;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.2);
            cursor: pointer;
            padding: 5px;
            font-size: 0.85rem;
            transition: 0.3s;
        }

        .password-toggle:hover {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-error {
            color: #ef4444;
            font-size: 0.7rem;
            margin-top: 3px;
        }

        .terms-check {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 10px 0 15px;
        }

        .terms-check input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #d4af37;
            cursor: pointer;
        }

        .terms-check label {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.3);
            cursor: pointer;
        }

        .terms-check label a {
            color: #d4af37;
            text-decoration: none;
        }

        .terms-check label a:hover {
            color: #f5d76e;
        }

        .register-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #d4af37, #f5d76e);
            border: none;
            border-radius: 10px;
            color: #0a0a0f;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }

        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(212, 175, 55, 0.3);
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 18px 0;
        }

        .divider-line {
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.04);
        }

        .divider-text {
            font-size: 0.55rem;
            color: rgb(255, 255, 255);
            text-transform: uppercase;
            letter-spacing: 2px;
            white-space: nowrap;
            font-weight: 400;
        }

        .social-login {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .social-btn {
            flex: 1;
            padding: 9px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            color: rgba(255, 255, 255, 0.35);
            font-size: 0.7rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-family: 'Inter', sans-serif;
            text-decoration: none;
        }

        .social-btn:hover {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(212, 175, 55, 0.12);
            color: rgba(255, 255, 255, 0.6);
        }

        .social-btn i {
            font-size: 0.85rem;
        }

        .social-btn.google i {
            color: #ea4335;
        }
        .social-btn.facebook i {
            color: #1877f2;
        }

        .register-footer {
            text-align: center;
            margin-top: 18px;
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.25);
        }

        .register-footer a {
            color: #d4af37;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
        }

        .register-footer a:hover {
            color: #f5d76e;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.12);
            color: #ef4444;
            padding: 8px 12px;
            border-radius: 10px;
            font-size: 0.75rem;
            margin-bottom: 12px;
            text-align: center;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.08);
            border: 1px solid rgba(16, 185, 129, 0.12);
            color: #10b981;
            padding: 8px 12px;
            border-radius: 10px;
            font-size: 0.75rem;
            margin-bottom: 12px;
            text-align: center;
        }

        .particles {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: -1;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            border-radius: 50%;
            opacity: 0.1;
            animation: particleFloat linear infinite;
        }

        @keyframes particleFloat {
            0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10% { opacity: 0.1; }
            90% { opacity: 0.1; }
            100% { transform: translateY(-100vh) rotate(720deg); opacity: 0; }
        }

        @media (max-width: 992px) {
            .register-wrapper {
                grid-template-columns: 1fr;
                max-width: 480px;
                min-height: auto;
            }
            .register-image-side {
                display: none;
            }
            .register-form-side {
                padding: 30px 20px;
            }
        }

        @media (max-width: 480px) {
            .register-form-side {
                padding: 20px 15px;
            }
            .register-title {
                font-size: 1.2rem;
            }
            .social-login {
                flex-direction: column;
            }
            .register-header .brand-name {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body class="register-page">
    <div class="particles" id="particles"></div>

    <div class="register-wrapper animate-zoom">
        <div class="register-form-side">
            <div class="register-header">
                <span class="brand-name">DRAVEX</span>
                <h1 class="register-title">CREATE YOUR ACCOUNT</h1>
                <p class="register-subtitle">Join Dravex and start your style journey.</p>
            </div>

            <?php if (isset($_SESSION['flash']['success'])): ?>
                <div class="alert-success"><?php echo $_SESSION['flash']['success']; unset($_SESSION['flash']['success']); ?></div>
            <?php endif; ?>

            <?php if (isset($errors['general'])): ?>
                <div class="alert-error"><?php echo $errors['general']; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($form_data['full_name']); ?>" placeholder="Enter your full name" required>
                    <div class="form-error"><?php echo isset($errors['full_name']) ? $errors['full_name'] : ''; ?></div>
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($form_data['phone']); ?>" placeholder="Enter your phone number">
                    <div class="form-error"><?php echo isset($errors['phone']) ? $errors['phone'] : ''; ?></div>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($form_data['email']); ?>" placeholder="Enter your email address" required>
                    <div class="form-error"><?php echo isset($errors['email']) ? $errors['email'] : ''; ?></div>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" placeholder="Create a password" required>
                        <button type="button" class="password-toggle" id="passwordToggle">
                            <i class="fas fa-eye" id="passwordIcon"></i>
                        </button>
                    </div>
                    <div class="form-error"><?php echo isset($errors['password']) ? $errors['password'] : ''; ?></div>
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                        <button type="button" class="password-toggle" id="confirmPasswordToggle">
                            <i class="fas fa-eye" id="confirmPasswordIcon"></i>
                        </button>
                    </div>
                    <div class="form-error"><?php echo isset($errors['confirm_password']) ? $errors['confirm_password'] : ''; ?></div>
                </div>

                <div class="terms-check">
                    <input type="checkbox" id="terms" name="terms" <?php echo isset($_POST['terms']) ? 'checked' : ''; ?>>
                    <label for="terms">I agree to the <a href="#">Terms &amp; Conditions</a></label>
                </div>
                <div class="form-error"><?php echo isset($errors['terms']) ? $errors['terms'] : ''; ?></div>

                <button type="submit" class="register-btn">CREATE ACCOUNT</button>
            </form>

            <div class="divider">
                <span class="divider-line"></span>
                <span class="divider-text">OR SIGN UP WITH</span>
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

            <div class="register-footer">
                Already have an account? <a href="login.php">Log in</a>
            </div>
        </div>

        <div class="register-image-side">
            <div class="brand-text-side">
                <span class="brand-name">DRAVEX</span>
                <div class="brand-tagline">WEAR CONFIDENCE</div>
                <div class="brand-sub">DRAVEX</div>
            </div>

            <div class="single-image-container">
                <?php 
                $image_path = $upload_dir . $display_image;
                $image_exists = file_exists($image_path);
                ?>
                <?php if ($image_exists): ?>
                    <img src="<?php echo $image_path; ?>" alt="DRAVEX Product" loading="lazy">
                <?php else: ?>
                    <div style="width:100%;height:100%;aspect-ratio:1/1;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#2d1b69,#1a0a2e);color:rgba(255,255,255,0.1);font-size:3rem;">
                        <i class="fas fa-tshirt"></i>
                    </div>
                <?php endif; ?>
            </div>

            <div class="side-text">
                <span style="display: block; margin-top: 6px;">PREMIUM WEAR</span>
            </div>

            <div class="side-text-bottom">DRAVEX - WEAR CONFIDENCE - DRAVEX</div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password Toggle
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

        // Confirm Password 
        var confirmToggleBtn = document.getElementById('confirmPasswordToggle');
        var confirmPasswordInput = document.getElementById('confirm_password');
        var confirmIcon = document.getElementById('confirmPasswordIcon');
        
        if (confirmToggleBtn && confirmPasswordInput && confirmIcon) {
            confirmToggleBtn.addEventListener('click', function() {
                if (confirmPasswordInput.type === 'password') {
                    confirmPasswordInput.type = 'text';
                    confirmIcon.className = 'fas fa-eye-slash';
                } else {
                    confirmPasswordInput.type = 'password';
                    confirmIcon.className = 'fas fa-eye';
                }
            });
        }

        //  for Particles
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