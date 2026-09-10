<?php
require_once 'config/database.php';
require_once 'config/session.php';

$message_sent = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $subject = sanitizeInput($_POST['subject']);
    $message = sanitizeInput($_POST['message']);
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NULL';
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email.';
    } else {
        // Save to database
        $query = "INSERT INTO contacts (user_id, name, email, subject, message, status) VALUES ($user_id, '$name', '$email', '$subject', '$message', 'unread')";
        if (executeQuery($query)) {
            $message_sent = true;
        } else {
            $error = 'Failed to send message. Please try again.';
        }
    }
}

include 'includes/header.php';
?>

<main>
    <div class="container" style="padding-top:var(--spacing-xl); padding-bottom:var(--spacing-xl);">
        <div class="row row-2">
            <div class="glass-card animate-zoom">
                <h1 style="color:var(--gold); margin-bottom:var(--spacing-md);">
                    <i class="fas fa-envelope"></i> Contact Us
                </h1>
                <p style="color:var(--text-secondary); margin-bottom:var(--spacing-lg);">
                    Have questions? We're here to help. Fill out the form below and we'll get back to you.
                </p>
                
                <?php if ($message_sent): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Message sent successfully! 
                        We'll get back to you soon.
                    </div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Your Name *</label>
                        <input type="text" name="name" class="form-control" value="<?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address *</label>
                        <input type="email" name="email" class="form-control" value="<?php echo isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Subject *</label>
                        <input type="text" name="subject" class="form-control" placeholder="Brief subject of your message" required>
                    </div>
                    <div class="form-group">
                        <label>Message *</label>
                        <textarea name="message" class="form-control" rows="5" placeholder="Describe your query in detail..." required></textarea>
                    </div>
                    <button type="submit" name="send_message" class="btn btn-primary w-100">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>
            
            <div>
                <div class="glass-card" style="text-align:center;">
                    <h3 style="color:var(--gold); margin-bottom:var(--spacing-md);">
                        <i class="fas fa-info-circle"></i> Contact Information
                    </h3>
                    
                    <div style="margin:var(--spacing-lg) 0;">
                        <i class="fas fa-map-marker-alt" style="font-size:2rem; color:var(--gold);"></i>
                        <p style="color:var(--text-secondary);">Lalitpur, Nepal</p>
                    </div>
                    <div style="margin:var(--spacing-lg) 0;">
                        <i class="fas fa-phone" style="font-size:2rem; color:var(--gold);"></i>
                        <p style="color:var(--text-secondary);">+977-9764545288</p>
                    </div>
                    <div style="margin:var(--spacing-lg) 0;">
                        <i class="fas fa-envelope" style="font-size:2rem; color:var(--gold);"></i>
                        <p style="color:var(--text-secondary);">info@dravex.com</p>
                    </div>
                    <div style="margin:var(--spacing-lg) 0;">
                        <i class="fas fa-clock" style="font-size:2rem; color:var(--gold);"></i>
                        <p style="color:var(--text-secondary);">Mon-Fri: 10:00 AM - 8:00 PM</p>
                    </div>
                    
                    <div style="display:flex; gap:var(--spacing-sm); justify-content:center; margin-top:var(--spacing-md);">
                        <a href="#" style="color:var(--text-muted); font-size:1.5rem; transition:0.3s;" onmouseover="this.style.color='#d4af37'" onmouseout="this.style.color='var(--text-muted)'">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" style="color:var(--text-muted); font-size:1.5rem; transition:0.3s;" onmouseover="this.style.color='#d4af37'" onmouseout="this.style.color='var(--text-muted)'">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" style="color:var(--text-muted); font-size:1.5rem; transition:0.3s;" onmouseover="this.style.color='#d4af37'" onmouseout="this.style.color='var(--text-muted)'">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="#" style="color:var(--text-muted); font-size:1.5rem; transition:0.3s;" onmouseover="this.style.color='#d4af37'" onmouseout="this.style.color='var(--text-muted)'">
                            <i class="fab fa-tiktok"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>