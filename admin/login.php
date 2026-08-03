<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Correct path
require_once '../config/database.php';
require_once '../config/session.php';

// Redirect if already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin') {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        // Check admin credentials
        $query = "SELECT u.*, a.admin_level 
                  FROM users u 
                  JOIN admin a ON u.id = a.user_id 
                  WHERE u.email = ? AND u.user_type = 'admin'";
        $stmt = mysqli_prepare($conn, $query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($user = mysqli_fetch_assoc($result)) {
                // Verify password
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_type'] = 'admin';
                    $_SESSION['admin_level'] = $user['admin_level'];
                    $_SESSION['last_activity'] = time();
                    
                    header('Location: dashboard.php');
                    exit();
                } else {
                    $error = 'Invalid email or password.';
                }
            } else {
                $error = 'Invalid email or password.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = 'Database error: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - DRAVEX</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/glass.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(ellipse at 50% 40%, #1a0a2e, #0a0a0f 70%);
            font-family: 'Inter', sans-serif;
        }

        .admin-login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .admin-login-card {
            background: rgba(255,255,255,0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(212,175,55,0.08);
            border-radius: 24px;
            padding: 40px 35px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }

        .admin-login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .admin-login-header .brand-name {
            font-size: 2rem;
            font-weight: 900;
            letter-spacing: 3px;
            background: linear-gradient(135deg, #d4af37, #f5d76e, #d4af37);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: block;
        }

        .admin-login-header .brand-tagline {
            font-size: 0.7rem;
            color: rgba(255,255,255,0.25);
            letter-spacing: 5px;
            text-transform: uppercase;
            font-weight: 300;
        }

        .admin-login-header h2 {
            font-size: 1.3rem;
            color: #ffffff;
            margin-top: 20px;
            margin-bottom: 5px;
        }

        .admin-login-header p {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.35);
        }

        .admin-login-header i {
            color: #d4af37;
            font-size: 2.5rem;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 0.7rem;
            font-weight: 500;
            color: rgba(255,255,255,0.35);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 12px;
            color: #ffffff;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-group input:focus {
            border-color: rgba(212,175,55,0.3);
            background: rgba(255,255,255,0.06);
            box-shadow: 0 0 0 4px rgba(212,175,55,0.05);
        }

        .form-group input::placeholder {
            color: rgba(255,255,255,0.12);
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
            color: rgba(255,255,255,0.2);
            cursor: pointer;
            padding: 5px;
            font-size: 0.9rem;
            transition: 0.3s;
        }

        .password-toggle:hover {
            color: rgba(255,255,255,0.5);
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
            box-shadow: 0 8px 30px rgba(212,175,55,0.3);
        }

        .auth-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.8rem;
            color: rgba(255,255,255,0.25);
        }

        .auth-footer a {
            color: #d4af37;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
        }

        .auth-footer a:hover {
            color: #f5d76e;
        }

        .alert-error {
            background: rgba(239,68,68,0.08);
            border: 1px solid rgba(239,68,68,0.12);
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

        @media (max-width: 480px) {
            .admin-login-card {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="particles" id="particles"></div>

    <div class="admin-login-container">
        <div class="admin-login-card animate-zoom">
            <div class="admin-login-header">
                <span class="brand-name">DRAVEX</span>
                <div class="brand-tagline">WEAR CONFIDENCE</div>
                <i class="fas fa-cog"></i>
                <h2>Admin Login</h2>
                <p>Enter your credentials to access the admin panel</p>
            </div>

            <?php if ($error): ?>
                <div class="alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Admin Email</label>
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

                <button type="submit" class="login-btn"><i class="fas fa-sign-in-alt"></i> Login</button>
            </form>

            <div class="auth-footer">
                <a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Website</a>
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