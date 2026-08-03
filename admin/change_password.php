<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../config/session.php';

// Check if logged in as admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $user_id = $_SESSION['user_id'];
    $result = executeQuery("SELECT password FROM users WHERE id = $user_id");
    $user = mysqli_fetch_assoc($result);
    
    if (!password_verify($current_password, $user['password'])) {
        $error = 'Current password is incorrect!';
    } elseif (strlen($new_password) < 8) {
        $error = 'New password must be at least 8 characters!';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match!';
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $update = executeQuery("UPDATE users SET password = '$hashed' WHERE id = $user_id");
        if ($update) {
            $message = 'Password changed successfully!';
        } else {
            $error = 'Failed to change password!';
        }
    }
}

include 'includes/admin_header.php';
?>

<div class="change-password-page">
    <div class="page-header">
        <h1><i class="fas fa-key"></i> Change Password</h1>
        <a href="dashboard.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="password-container">
        <form method="POST" class="password-form">
            <!-- Current Password -->
            <div class="form-group">
                <label>Current Password</label>
                <div class="password-wrapper">
                    <input type="password" id="current_password" name="current_password" class="form-control" placeholder="Enter current password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                        <i class="fas fa-eye" id="current_passwordIcon"></i>
                    </button>
                </div>
            </div>
            
            <!-- New Password -->
            <div class="form-group">
                <label>New Password</label>
                <div class="password-wrapper">
                    <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Enter new password (min 8 characters)" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                        <i class="fas fa-eye" id="new_passwordIcon"></i>
                    </button>
                </div>
                <small class="text-muted">Password must be at least 8 characters</small>
            </div>
            
            <!-- Confirm Password -->
            <div class="form-group">
                <label>Confirm New Password</label>
                <div class="password-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm new password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye" id="confirm_passwordIcon"></i>
                    </button>
                </div>
            </div>
            
            <!-- Submit Buttons -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Change Password
                </button>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Password Toggle Function
function togglePassword(fieldId) {
    var input = document.getElementById(fieldId);
    var icon = document.getElementById(fieldId + 'Icon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Also add click listener for enter key
document.addEventListener('DOMContentLoaded', function() {
    var passwordInputs = document.querySelectorAll('.password-wrapper input');
    passwordInputs.forEach(function(input) {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                var form = this.closest('form');
                if (form) form.submit();
            }
        });
    });
});
</script>

<style>
.change-password-page {
    padding: 0;
    max-width: 600px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.page-header h1 {
    color: #ffffff;
    font-size: 2rem;
    font-weight: 700;
}

.page-header h1 i {
    color: #d4af37;
    margin-right: 0.75rem;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.5rem;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(212, 175, 55, 0.08);
    border-radius: 8px;
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 0.85rem;
}

.btn-back:hover {
    background: rgba(255, 255, 255, 0.08);
    transform: translateY(-2px);
    color: #ffffff;
}

.alert {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.alert-success {
    background: rgba(16, 185, 129, 0.1);
    border-color: rgba(16, 185, 129, 0.2);
    color: #10b981;
}

.alert-error {
    background: rgba(239, 68, 68, 0.1);
    border-color: rgba(239, 68, 68, 0.2);
    color: #ef4444;
}

.password-container {
    background: rgba(255, 255, 255, 0.02);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(212, 175, 55, 0.06);
    border-radius: 16px;
    padding: 2rem;
}

.password-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.85rem;
    font-weight: 500;
}

.password-wrapper {
    position: relative;
}

.password-wrapper .form-control {
    padding-right: 45px;
}

.password-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: rgba(255, 255, 255, 0.3);
    cursor: pointer;
    padding: 5px;
    font-size: 1rem;
    transition: 0.3s;
    z-index: 10;
}

.password-toggle:hover {
    color: rgba(255, 255, 255, 0.7);
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(212, 175, 55, 0.08);
    border-radius: 8px;
    color: #ffffff;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    font-family: 'Inter', sans-serif;
}

.form-control:focus {
    outline: none;
    border-color: rgba(212, 175, 55, 0.3);
    box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.05);
}

.form-control::placeholder {
    color: rgba(255, 255, 255, 0.15);
}

.text-muted {
    color: rgba(255, 255, 255, 0.3);
    font-size: 0.75rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 0.5rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 2rem;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: linear-gradient(135deg, #d4af37, #f5d76e);
    color: #0a0a0f;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);
    color: #0a0a0f;
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(212, 175, 55, 0.08);
    color: rgba(255, 255, 255, 0.6);
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.08);
    transform: translateY(-2px);
    color: #ffffff;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .btn-back {
        width: 100%;
        justify-content: center;
    }
    .password-container {
        padding: 1.5rem;
    }
    .form-actions {
        flex-direction: column;
    }
    .form-actions .btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .page-header h1 {
        font-size: 1.5rem;
    }
    .password-container {
        padding: 1rem;
    }
}
</style>

<?php include 'includes/admin_footer.php'; ?>