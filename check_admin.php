<?php

require_once 'config/database.php';

$email = 'admin@dravex.com';

echo "<h2> Checking Admin User</h2>";

$result = executeQuery("SELECT * FROM users WHERE email = '$email'");

if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    echo " Admin found!<br>";
    echo "ID: " . $user['id'] . "<br>";
    echo "Name: " . $user['full_name'] . "<br>";
    echo "Email: " . $user['email'] . "<br>";
    echo "User Type: " . $user['user_type'] . "<br>";
    echo "Password Hash: " . $user['password'] . "<br>";
    
    // Check if admin in admin table
    $admin_check = executeQuery("SELECT * FROM admin WHERE user_id = " . $user['id']);
    if (mysqli_num_rows($admin_check) > 0) {
        echo " Admin record found in admin table.<br>";
    } else {
        echo " Admin not in admin table!<br>";
        echo "Fixing...<br>";
        executeQuery("INSERT INTO admin (user_id, admin_level) VALUES (" . $user['id'] . ", 'super')");
        echo " Added to admin table.<br>";
    }
} else {
    echo "Admin not found!<br>";
    echo "Creating admin...<br>";
    $hashed = password_hash('admin123', PASSWORD_DEFAULT);
    executeQuery("INSERT INTO users (full_name, email, password, phone, user_type) VALUES ('Admin User', '$email', '$hashed', '9876543210', 'admin')");
    $id = mysqli_insert_id($conn);
    executeQuery("INSERT INTO admin (user_id, admin_level) VALUES ($id, 'super')");
    echo " Admin created!<br>";
}

echo "<br><a href='admin/login.php'>Go to Admin Login</a>";
?>