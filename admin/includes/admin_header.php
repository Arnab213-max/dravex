<?php
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../config/database.php';
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = executeQuery($user_query);
$admin_user = mysqli_fetch_assoc($user_result);

$profile_image = !empty($admin_user['profile_image']) ? $admin_user['profile_image'] : 'default.jpg';
if (!file_exists('../../uploads/' . $profile_image)) {
    $profile_image = 'default.jpg';
}
$use_fallback = !file_exists('../../uploads/default.jpg');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - DRAVEX</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/glass.css">
    <link rel="stylesheet" href="../../css/animations.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(ellipse at 50% 40%, #1a0a2e, #0a0a0f 70%);
            color: #ffffff;
            min-height: 100vh;
        }
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }
        .admin-sidebar {
            width: 260px;
            background: rgba(10, 10, 15, 0.92);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(212, 175, 55, 0.06);
            padding: 1.5rem 1.2rem;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }
        .admin-sidebar::-webkit-scrollbar { width: 3px; }
        .admin-sidebar::-webkit-scrollbar-track { background: transparent; }
        .admin-sidebar::-webkit-scrollbar-thumb { background: #d4af37; border-radius: 2px; }
        .sidebar-brand {
            text-align: center;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(212, 175, 55, 0.06);
            margin-bottom: 1.5rem;
        }
        .sidebar-brand h2 {
            color: #ffffff;
            font-size: 1.5rem;
            font-weight: 900;
            letter-spacing: 2px;
        }
        .sidebar-brand h2 i { color: #d4af37; margin-right: 0.5rem; }
        .sidebar-brand span {
            color: rgba(255, 255, 255, 0.2);
            font-size: 0.7rem;
            display: block;
            letter-spacing: 3px;
            margin-top: 0.2rem;
        }
        .sidebar-profile {
            text-align: center;
            padding: 1rem 0 1.5rem;
            border-bottom: 1px solid rgba(212, 175, 55, 0.06);
            margin-bottom: 1.5rem;
        }
        .sidebar-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #d4af37;
            margin: 0 auto 0.75rem;
        }
        .sidebar-avatar-fallback {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #2d1b69, #1a0a2e);
            border: 3px solid #d4af37;
            margin: 0 auto 0.75rem;
            color: rgba(255, 255, 255, 0.15);
            font-size: 2rem;
        }
        .sidebar-user strong { display: block; color: #ffffff; font-size: 0.95rem; }
        .sidebar-user span { color: rgba(255, 255, 255, 0.3); font-size: 0.7rem; display: block; }
        .sidebar-user .role-badge {
            display: inline-block;
            background: linear-gradient(135deg, #d4af37, #f5d76e);
            color: #0a0a0f;
            padding: 0.05rem 0.6rem;
            border-radius: 50px;
            font-size: 0.55rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 0.3rem;
        }
        .sidebar-nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            flex: 1;
        }
        .sidebar-nav ul li { margin-bottom: 0.2rem; }
        .sidebar-nav ul li a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 1rem;
            border-radius: 8px;
            color: rgba(255, 255, 255, 0.4);
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .sidebar-nav ul li a:hover {
            background: rgba(212, 175, 55, 0.05);
            color: #ffffff;
        }
        .sidebar-nav ul li a.active {
            background: rgba(212, 175, 55, 0.08);
            color: #d4af37;
        }
        .sidebar-nav ul li a i {
            width: 20px;
            text-align: center;
            font-size: 0.95rem;
        }
        .sidebar-logout {
            margin-top: auto;
            padding-top: 1rem;
            border-top: 1px solid rgba(212, 175, 55, 0.06);
        }
        .sidebar-logout a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 1rem;
            border-radius: 8px;
            color: rgba(239, 68, 68, 0.5);
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .sidebar-logout a:hover {
            background: rgba(239, 68, 68, 0.08);
            color: #ef4444;
        }
        .admin-content {
            flex: 1;
            margin-left: 260px;
            padding: 2rem;
            min-height: 100vh;
            width: calc(100% - 260px);
        }
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 200;
            background: rgba(10, 10, 15, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(212, 175, 55, 0.1);
            border-radius: 8px;
            color: #ffffff;
            padding: 0.5rem 0.75rem;
            cursor: pointer;
            font-size: 1.2rem;
        }
        @media (max-width: 992px) {
            .admin-sidebar { width: 220px; padding: 1rem; }
            .admin-content { margin-left: 220px; width: calc(100% - 220px); padding: 1.5rem; }
        }
        @media (max-width: 768px) {
            .sidebar-toggle { display: block; }
            .admin-sidebar {
                transform: translateX(-100%);
                width: 280px;
                padding: 1.5rem;
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
            }
            .admin-sidebar.open { transform: translateX(0); }
            .admin-content {
                margin-left: 0;
                width: 100%;
                padding: 1rem;
                padding-top: 4.5rem;
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 99;
            }
            .sidebar-overlay.active { display: block; }
        }
        @media (max-width: 576px) {
            .admin-content { padding: 0.75rem; padding-top: 4.5rem; }
            .admin-sidebar { width: 100%; max-width: 320px; }
        }
    </style>
</head>
<body>
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="admin-wrapper">
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-brand">
                <h2><i class="fas fa-crown"></i> DRAVEX</h2>
                <span>Admin Panel</span>
            </div>

            <div class="sidebar-profile">
                <?php if ($use_fallback): ?>
                    <div class="sidebar-avatar-fallback"><i class="fas fa-user"></i></div>
                <?php else: ?>
                    <img src="../../uploads/<?php echo htmlspecialchars($profile_image); ?>" alt="Profile" class="sidebar-avatar">
                <?php endif; ?>
                <div class="sidebar-user">
                    <strong><?php echo htmlspecialchars($admin_user['full_name'] ?? 'Admin'); ?></strong>
                    <span><?php echo htmlspecialchars($admin_user['email'] ?? ''); ?></span>
                    <span class="role-badge">Administrator</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a></li>
                    <li><a href="products.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
                        <i class="fas fa-box"></i> Products
                    </a></li>
                    <li><a href="add_product.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'add_product.php' ? 'active' : ''; ?>">
                        <i class="fas fa-plus-circle"></i> Add Product
                    </a></li>
                    <li><a href="categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
                        <i class="fas fa-tags"></i> Categories
                    </a></li>
                    <li><a href="orders.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
                        <i class="fas fa-shopping-cart"></i> Orders
                    </a></li>
                    <li><a href="users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i> Users
                    </a></li>
                    <li><a href="change_password.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'change_password.php' ? 'active' : ''; ?>">
                        <i class="fas fa-key"></i> Change Password
                    </a></li>
                </ul>
            </nav>

            <div class="sidebar-logout">
                <a href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </aside>

        <main class="admin-content" id="adminContent">