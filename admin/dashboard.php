<?php
require_once '../config/database.php';
require_once '../config/session.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit();
}

$total_users = mysqli_fetch_assoc(executeQuery("SELECT COUNT(*) as total FROM users WHERE user_type = 'customer'"))['total'];
$total_products = mysqli_fetch_assoc(executeQuery("SELECT COUNT(*) as total FROM products"))['total'];
$total_orders = mysqli_fetch_assoc(executeQuery("SELECT COUNT(*) as total FROM orders"))['total'];
$total_revenue = mysqli_fetch_assoc(executeQuery("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'"))['total'] ?: 0;

include 'includes/admin_header.php';
?>

<div class="admin-container">
    <h2 class="page-title"><i class="fas fa-chart-line" style="color:var(--gold);"></i> Dashboard</h2>
    
    <div class="glass-dashboard">
        <div class="stat-card"><div class="stat-icon"><i class="fas fa-users"></i></div><div class="stat-number"><?php echo $total_users; ?></div><div class="stat-label">Total Users</div></div>
        <div class="stat-card"><div class="stat-icon"><i class="fas fa-box"></i></div><div class="stat-number"><?php echo $total_products; ?></div><div class="stat-label">Total Products</div></div>
        <div class="stat-card"><div class="stat-icon"><i class="fas fa-shopping-cart"></i></div><div class="stat-number"><?php echo $total_orders; ?></div><div class="stat-label">Total Orders</div></div>
        <div class="stat-card"><div class="stat-icon"><i class="fas fa-rupee-sign"></i></div><div class="stat-number">₹<?php echo number_format($total_revenue, 2); ?></div><div class="stat-label">Revenue</div></div>
    </div>

    <div class="glass-card">
        <h3><i class="fas fa-clock" style="color:var(--gold);"></i> Quick Actions</h3>
        <div style="display:flex; gap:1rem; flex-wrap:wrap; margin-top:1rem;">
            <a href="add_product.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Product</a>
            <a href="products.php" class="btn btn-secondary"><i class="fas fa-box"></i> Manage Products</a>
        </div>
    </div>
</div>

<style>
.admin-container { padding: 2rem; }
.page-title { margin-bottom: 2rem; font-size: 2rem; }
.glass-dashboard { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-lg); margin-bottom: var(--spacing-xl); }
.stat-card { background: rgba(255,255,255,0.04); backdrop-filter: blur(20px); border: 1px solid rgba(212,175,55,0.1); border-radius: var(--radius-lg); padding: var(--spacing-lg); text-align: center; transition: 0.3s; }
.stat-card:hover { transform: translateY(-5px); box-shadow: 0 12px 48px rgba(212,175,55,0.1); }
.stat-icon { font-size: 2rem; color: var(--gold); margin-bottom: 0.5rem; }
.stat-number { font-size: 2.5rem; font-weight: 900; background: var(--gold-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.stat-label { color: var(--text-secondary); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; }
</style>

<?php include 'includes/admin_footer.php'; ?>