<?php
require_once 'config/database.php';
require_once 'config/session.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

$result = executeQuery("SELECT * FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitizeInput($_POST['full_name']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    $city = sanitizeInput($_POST['city']);
    $state = sanitizeInput($_POST['state']);
    $zipcode = sanitizeInput($_POST['zipcode']);
    
    $errors = [];
    if (empty($full_name)) $errors[] = 'Full name is required';
    if (!empty($phone) && !preg_match('/^[0-9]{10}$/', $phone)) $errors[] = 'Valid phone number required';
    
    if (empty($errors)) {
        executeQuery("UPDATE users SET full_name = '$full_name', phone = '$phone', address = '$address', city = '$city', state = '$state', zipcode = '$zipcode' WHERE id = $user_id");
        $_SESSION['user_name'] = $full_name;
        $message = 'Profile updated successfully!';
        $user['full_name'] = $full_name;
        $user['phone'] = $phone;
        $user['address'] = $address;
        $user['city'] = $city;
        $user['state'] = $state;
        $user['zipcode'] = $zipcode;
    } else {
        $error = implode('<br>', $errors);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    if (!password_verify($current_password, $user['password'])) $errors[] = 'Current password is incorrect';
    if (empty($new_password) || strlen($new_password) < 8 || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $new_password)) {
        $errors[] = 'New password must be 8+ chars with uppercase, lowercase and number';
    }
    if ($new_password !== $confirm_password) $errors[] = 'Passwords do not match';
    
    if (empty($errors)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        executeQuery("UPDATE users SET password = '$hashed_password' WHERE id = $user_id");
        $message = 'Password changed successfully!';
    } else {
        $error = implode('<br>', $errors);
    }
}

include 'includes/header.php';
?>

<main>
    <div class="container" style="padding-top:var(--spacing-xl);">
        <h1 class="section-title">My Profile</h1>
        <?php if ($message): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
        <div class="row row-2">
            <div class="glass-card">
                <h3><i class="fas fa-user-circle" style="color:var(--gold);"></i> Profile Information</h3>
                <form method="POST">
                    <div class="form-group"><label>Full Name</label><input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required></div>
                    <div class="form-group"><label>Email</label><input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly></div>
                    <div class="form-group"><label>Phone</label><input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>"></div>
                    <div class="form-group"><label>Address</label><textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($user['address']); ?></textarea></div>
                    <div class="row row-3" style="gap:var(--spacing-md);">
                        <div class="form-group"><label>City</label><input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($user['city']); ?>"></div>
                        <div class="form-group"><label>State</label><input type="text" name="state" class="form-control" value="<?php echo htmlspecialchars($user['state']); ?>"></div>
                        <div class="form-group"><label>Zipcode</label><input type="text" name="zipcode" class="form-control" value="<?php echo htmlspecialchars($user['zipcode']); ?>"></div>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary"><i class="fas fa-save"></i> Update Profile</button>
                </form>
            </div>
            <div class="glass-card">
                <h3><i class="fas fa-key" style="color:var(--gold);"></i> Change Password</h3>
                <form method="POST">
                    <div class="form-group"><label>Current Password</label><input type="password" name="current_password" class="form-control" required></div>
                    <div class="form-group"><label>New Password</label><input type="password" name="new_password" class="form-control" required></div>
                    <div class="form-group"><label>Confirm New Password</label><input type="password" name="confirm_password" class="form-control" required></div>
                    <button type="submit" name="change_password" class="btn btn-primary"><i class="fas fa-key"></i> Change Password</button>
                </form>
            </div>
        </div>
    </div>
</main>
<?php include 'includes/footer.php'; ?>