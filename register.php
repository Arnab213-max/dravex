<?php
require_once 'config/database.php';
require_once 'config/session.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$errors = [];
$form_data = ['full_name' => '', 'email' => '', 'phone' => '', 'address' => '', 'city' => '', 'state' => '', 'zipcode' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data['full_name'] = sanitizeInput($_POST['full_name']);
    $form_data['email'] = sanitizeInput($_POST['email']);
    $form_data['phone'] = sanitizeInput($_POST['phone']);
    $form_data['address'] = sanitizeInput($_POST['address']);
    $form_data['city'] = sanitizeInput($_POST['city']);
    $form_data['state'] = sanitizeInput($_POST['state']);
    $form_data['zipcode'] = sanitizeInput($_POST['zipcode']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
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
    if (empty($password) || strlen($password) < 8 || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
        $errors['password'] = 'Password must be 8+ chars with uppercase, lowercase and number';
    }
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    if (empty($form_data['address'])) $errors['address'] = 'Address is required';
    if (empty($form_data['city'])) $errors['city'] = 'City is required';
    if (empty($form_data['state'])) $errors['state'] = 'State is required';
    if (empty($form_data['zipcode']) || !preg_match('/^[0-9]{5,6}$/', $form_data['zipcode'])) {
        $errors['zipcode'] = 'Valid zipcode is required';
    }
    
    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "INSERT INTO users (full_name, email, password, phone, address, city, state, zipcode) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssssssss", $form_data['full_name'], $form_data['email'], $hashed, $form_data['phone'], $form_data['address'], $form_data['city'], $form_data['state'], $form_data['zipcode']);
        if (mysqli_stmt_execute($stmt)) {
            setFlashMessage('success', 'Registration successful! Please login.');
            header('Location: login.php');
            exit();
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
    <title>Register - CodeCraze & Threads</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/glass.css">
    <link rel="stylesheet" href="css/animations.css">
</head>
<body>
    <div class="auth-container">
        <div class="glass-card auth-card animate-zoom" style="max-width: 600px;">
            <div class="auth-header">
                <a href="index.php" class="auth-logo"><i class="fas fa-code" style="color:var(--purple);"></i> CodeCraze &amp; Threads</a>
                <h2>Create Account</h2>
                <p>Join the CodeCraze community</p>
            </div>
            <form method="POST">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($form_data['full_name']); ?>" required>
                    <div class="form-error"><?php echo isset($errors['full_name']) ? $errors['full_name'] : ''; ?></div>
                </div>
                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($form_data['email']); ?>" required>
                    <div class="form-error"><?php echo isset($errors['email']) ? $errors['email'] : ''; ?></div>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($form_data['phone']); ?>">
                    <div class="form-error"><?php echo isset($errors['phone']) ? $errors['phone'] : ''; ?></div>
                </div>
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" class="form-control" required>
                    <div class="form-error"><?php echo isset($errors['password']) ? $errors['password'] : ''; ?></div>
                </div>
                <div class="form-group">
                    <label>Confirm Password *</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                    <div class="form-error"><?php echo isset($errors['confirm_password']) ? $errors['confirm_password'] : ''; ?></div>
                </div>
                <div class="form-group">
                    <label>Address *</label>
                    <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($form_data['address']); ?></textarea>
                    <div class="form-error"><?php echo isset($errors['address']) ? $errors['address'] : ''; ?></div>
                </div>
                <div class="row row-3" style="gap:var(--spacing-md);">
                    <div class="form-group">
                        <label>City *</label>
                        <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($form_data['city']); ?>" required>
                        <div class="form-error"><?php echo isset($errors['city']) ? $errors['city'] : ''; ?></div>
                    </div>
                    <div class="form-group">
                        <label>State *</label>
                        <input type="text" name="state" class="form-control" value="<?php echo htmlspecialchars($form_data['state']); ?>" required>
                        <div class="form-error"><?php echo isset($errors['state']) ? $errors['state'] : ''; ?></div>
                    </div>
                    <div class="form-group">
                        <label>Zipcode *</label>
                        <input type="text" name="zipcode" class="form-control" value="<?php echo htmlspecialchars($form_data['zipcode']); ?>" required>
                        <div class="form-error"><?php echo isset($errors['zipcode']) ? $errors['zipcode'] : ''; ?></div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-user-plus"></i> Register</button>
                <div class="auth-footer"><p>Already have an account? <a href="login.php">Login here</a></p></div>
            </form>
        </div>
    </div>
    <script src="js/script.js"></script>
</body>
</html>