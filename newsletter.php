<?php
require_once 'config/database.php';
require_once 'config/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subscribe'])) {
    $email = sanitizeInput($_POST['email']);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $check = executeQuery("SELECT id FROM newsletter WHERE email = '$email'");
        if (mysqli_num_rows($check) == 0) {
            executeQuery("INSERT INTO newsletter (email) VALUES ('$email')");
            $_SESSION['flash']['success'] = 'Subscribed successfully!';
        } else {
            $_SESSION['flash']['error'] = 'Already subscribed!';
        }
    } else {
        $_SESSION['flash']['error'] = 'Invalid email!';
    }
    header('Location: index.php');
    exit();
}
?>