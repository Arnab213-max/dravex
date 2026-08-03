<?php
require_once 'config/database.php';
require_once 'config/session.php';

$email = 'admin@dravex.com';
$password = 'admin123';
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Check if admin exists
$check = executeQuery("SELECT id FROM users WHERE email = '$email'");
if (mysqli_num_rows($check) > 0) {
    // Update password
    executeQuery("UPDATE users SET password = '$hashed' WHERE email = '$email'");
    echo " Admin password updated to: $password<br>";
} else {
    // Create new admin
    executeQuery("INSERT INTO users (full_name, email, password, phone, user_type) VALUES ('Admin User', '$email', '$hashed', '9876543210', 'admin')");
    $user_id = mysqli_insert_id($conn);
    executeQuery("INSERT INTO admin (user_id, admin_level) VALUES ($user_id, 'super')");
    echo "Admin created successfully!<br>";
}

echo "<br><strong>Email:</strong> $email<br>";
echo "<strong>Password:</strong> $password<br>";
echo "<br><a href='admin/login.php'>Go to Admin Login</a>";
?>