<?php
// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'dravex');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "<h2> Creating Admin Directly</h2>";

$email = 'admin@dravex.com';
$password = 'admin123';
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Delete existing admin
mysqli_query($conn, "DELETE FROM admin WHERE user_id IN (SELECT id FROM users WHERE email = '$email')");
mysqli_query($conn, "DELETE FROM users WHERE email = '$email'");

// Create new admin
$insert = mysqli_query($conn, "INSERT INTO users (full_name, email, password, phone, user_type) VALUES ('Admin User', '$email', '$hashed', '9876543210', 'admin')");

if ($insert) {
    $user_id = mysqli_insert_id($conn);
    mysqli_query($conn, "INSERT INTO admin (user_id, admin_level) VALUES ($user_id, 'super')");
    echo " Admin created successfully!<br>";
} else {
    echo " Failed to create admin!<br>";
}

echo "<br><strong>Login Credentials:</strong><br>";
echo "Email: <strong>$email</strong><br>";
echo "Password: <strong>$password</strong><br>";
echo "<br><a href='admin/login.php'>Go to Admin Login</a>";

mysqli_close($conn);
?>