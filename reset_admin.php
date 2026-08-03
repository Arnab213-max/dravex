<?php   
require_once 'config/database.php';

$email = 'admin@dravex.com';
$password = 'admin123';
$hashed = password_hash($password, PASSWORD_DEFAULT);

echo "<h2> Resetting Admin Password...</h2>";

// Check if admin exists
$check = executeQuery("SELECT id FROM users WHERE email = '$email'");

if (mysqli_num_rows($check) > 0) {
    // Update password
    $update = executeQuery("UPDATE users SET password = '$hashed' WHERE email = '$email'");
    if ($update) {
        echo " Password updated successfully!<br>";
    } else {
        echo "Failed to update password!<br>";
    }
} else {
    // Create admin
    $insert = executeQuery("INSERT INTO users (full_name, email, password, phone, user_type) VALUES ('Admin User', '$email', '$hashed', '9876543210', 'admin')");
    if ($insert) {
        $user_id = mysqli_insert_id($conn);
        executeQuery("INSERT INTO admin (user_id, admin_level) VALUES ($user_id, 'super')");
        echo " Admin created successfully!<br>";
    } else {
        echo "Failed to create admin!<br>";
    }
}

echo "<br><strong>Email:</strong> $email<br>";
echo "<strong>Password:</strong> $password<br>";
echo "<br><a href='admin/login.php'>Go to Admin Login</a>";
?>