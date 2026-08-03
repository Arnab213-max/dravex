<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../config/session.php';

// Check if logged in as admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

//  admin  details
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = executeQuery($user_query);
$admin_user = mysqli_fetch_assoc($user_result);

// profile image
$profile_image = !empty($admin_user['profile_image']) ? $admin_user['profile_image'] : 'default.jpg';
$image_path = '../uploads/' . $profile_image;
if (!file_exists($image_path)) {
    $profile_image = 'default.jpg';
}
$use_fallback = !file_exists('../uploads/default.jpg');

// Get statistics
$total_users = mysqli_fetch_assoc(executeQuery("SELECT COUNT(*) as total FROM users WHERE user_type = 'customer'"))['total'] ?? 0;
$total_products = mysqli_fetch_assoc(executeQuery("SELECT COUNT(*) as total FROM products"))['total'] ?? 0;
$total_orders = mysqli_fetch_assoc(executeQuery("SELECT COUNT(*) as total FROM orders"))['total'] ?? 0;
$total_revenue = mysqli_fetch_assoc(executeQuery("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'"))['total'] ?? 0;

// Get all products  listing
$products = executeQuery("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC LIMIT 10");

include 'includes/admin_header.php';
?>

<div class="admin-dashboard">
    <!-- Welcome Section -->
    <div class="welcome-section">
        <div>
            <h1>Dashboard</h1>
            <p class="welcome-text">Welcome back, <?php echo htmlspecialchars($admin_user['full_name'] ?? 'Admin'); ?>!</p>
        </div>
        <div class="admin-profile-badge">
            <?php if ($use_fallback): ?>
                <div class="profile-avatar-sm">
                    <i class="fas fa-user"></i>
                </div>
            <?php else: ?>
                <img src="../uploads/<?php echo htmlspecialchars($profile_image); ?>" alt="Admin" class="profile-avatar-sm">
            <?php endif; ?>
            <div>
                <div class="profile-name"><?php echo htmlspecialchars($admin_user['full_name'] ?? 'Admin'); ?></div>
                <div class="profile-email"><?php echo htmlspecialchars($admin_user['email'] ?? ''); ?></div>
                <span class="admin-badge-sm">Administrator</span>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions-bar">
        <a href="add_product.php" class="quick-action-btn primary">
            <i class="fas fa-plus"></i> Add Product
        </a>
        <a href="products.php" class="quick-action-btn secondary">
            <i class="fas fa-box"></i> Manage Products
        </a>
        <a href="categories.php" class="quick-action-btn secondary">
            <i class="fas fa-tags"></i> Categories
        </a>
        <a href="orders.php" class="quick-action-btn secondary">
            <i class="fas fa-shopping-cart"></i> Orders
        </a>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div>
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-box"></i></div>
            <div>
                <div class="stat-number"><?php echo $total_products; ?></div>
                <div class="stat-label">Total Products</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
            <div>
                <div class="stat-number"><?php echo $total_orders; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-rupee-sign"></i></div>
            <div>
                <div class="stat-number">Rs.<?php echo number_format($total_revenue, 2); ?></div>
                <div class="stat-label">Revenue</div>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="products-section">
        <div class="section-header">
            <h2><i class="fas fa-box"></i> Products</h2>
            <a href="add_product.php" class="btn-add">
                <i class="fas fa-plus"></i> Add Product
            </a>
        </div>
        <div class="table-container">
            <table class="products-table">
                <thead>
                    <tr>
                        <th>IMAGE</th>
                        <th>NAME</th>
                        <th>CATEGORY</th>
                        <th>PRICE</th>
                        <th>STOCK</th>
                        <th>STATUS</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($products) > 0): ?>
                        <?php while ($p = mysqli_fetch_assoc($products)): ?>
                            <tr>
                                <td>
                                    <?php 
                                    $img_path = '../uploads/' . $p['image'];
                                    if (file_exists($img_path)): 
                                    ?>
                                        <img src="<?php echo $img_path; ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" class="product-img">
                                    <?php else: ?>
                                        <div class="product-img-placeholder">
                                            <i class="fas fa-tshirt"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($p['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($p['category_name']); ?></td>
                                <td>Rs.<?php echo number_format($p['price'], 2); ?></td>
                                <td><?php echo $p['quantity']; ?></td>
                                <td>
                                    <?php if ($p['quantity'] > 0): ?>
                                        <span class="status in-stock">In Stock</span>
                                    <?php else: ?>
                                        <span class="status out-of-stock">Out of Stock</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <a href="edit_product.php?id=<?php echo $p['id']; ?>" class="action-btn edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="products.php?delete=<?php echo $p['id']; ?>" class="action-btn delete" onclick="return confirm('Delete this product?')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="empty-row">
                                <i class="fas fa-box-open"></i>
                                <p>No products found</p>
                                <a href="add_product.php" class="btn-add-small">Add Product</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>

.admin-dashboard {
    padding: 0;
    max-width: 1400px;
    margin: 0 auto;
    width: 100%;
}

.welcome-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding: 1.5rem 2rem;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(212, 175, 55, 0.06);
    border-radius: 16px;
    flex-wrap: wrap;
    gap: 1rem;
}

.welcome-section h1 {
    color: #ffffff;
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.welcome-section h1 i {
    color: #d4af37;
    margin-right: 0.5rem;
}

.welcome-text {
    color: rgba(255, 255, 255, 0.5);
    font-size: 1rem;
}

.admin-profile-badge {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.5rem 1.5rem 0.5rem 0.5rem;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(212, 175, 55, 0.06);
    border-radius: 50px;
}

.profile-avatar-sm {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #d4af37;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #2d1b69, #1a0a2e);
    color: rgba(255, 255, 255, 0.2);
    font-size: 1.2rem;
}

.profile-name {
    color: #ffffff;
    font-weight: 600;
    font-size: 0.95rem;
}

.profile-email {
    color: rgba(255, 255, 255, 0.4);
    font-size: 0.75rem;
}

.admin-badge-sm {
    display: inline-block;
    background: linear-gradient(135deg, #d4af37, #f5d76e);
    color: #0a0a0f;
    padding: 0.05rem 0.6rem;
    border-radius: 50px;
    font-size: 0.55rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 0.1rem;
}

.quick-actions-bar {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.quick-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.5rem;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.quick-action-btn.primary {
    background: linear-gradient(135deg, #d4af37, #f5d76e);
    color: #0a0a0f;
}

.quick-action-btn.primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);
    color: #0a0a0f;
}

.quick-action-btn.secondary {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(212, 175, 55, 0.08);
    color: rgba(255, 255, 255, 0.7);
}

.quick-action-btn.secondary:hover {
    background: rgba(212, 175, 55, 0.05);
    transform: translateY(-2px);
    color: #ffffff;
}


.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(212, 175, 55, 0.06);
    border-radius: 12px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
}

.stat-card:hover {
    border-color: rgba(212, 175, 55, 0.15);
    transform: translateY(-3px);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(212, 175, 55, 0.05);
    color: #d4af37;
    font-size: 1.5rem;
}

.stat-number {
    font-size: 1.8rem;
    font-weight: 700;
    color: #ffffff;
    line-height: 1.2;
}

.stat-label {
    color: rgba(255, 255, 255, 0.4);
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}


.products-section {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(212, 175, 55, 0.06);
    border-radius: 16px;
    padding: 1.5rem;
    overflow: hidden;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.section-header h2 {
    color: #ffffff;
    font-size: 1.3rem;
    font-weight: 600;
}

.section-header h2 i {
    color: #d4af37;
    margin-right: 0.5rem;
}

.btn-add {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1.2rem;
    background: linear-gradient(135deg, #d4af37, #f5d76e);
    color: #0a0a0f;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-add:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);
    color: #0a0a0f;
}


.table-container {
    overflow-x: auto;
}

.products-table {
    width: 100%;
    border-collapse: collapse;
}

.products-table thead {
    border-bottom: 1px solid rgba(212, 175, 55, 0.06);
}

.products-table th {
    color: rgba(255, 255, 255, 0.3);
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 0.75rem 0.5rem;
    text-align: left;
}

.products-table td {
    padding: 0.75rem 0.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.02);
    color: rgba(255, 255, 255, 0.8);
    vertical-align: middle;
    font-size: 0.85rem;
}

.products-table tr:hover td {
    background: rgba(255, 255, 255, 0.02);
}

.products-table tr:last-child td {
    border-bottom: none;
}


.product-img {
    width: 45px;
    height: 45px;
    border-radius: 8px;
    object-fit: cover;
    border: 1px solid rgba(212, 175, 55, 0.06);
}

.product-img-placeholder {
    width: 45px;
    height: 45px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(212, 175, 55, 0.04);
    color: rgba(255, 255, 255, 0.05);
    font-size: 1.2rem;
}


.status {
    display: inline-block;
    padding: 0.15rem 0.7rem;
    border-radius: 50px;
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.status.in-stock {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
}

.status.out-of-stock {
    background: rgba(239, 68, 68, 0.12);
    color: #ef4444;
}

.action-btns {
    display: flex;
    gap: 0.5rem;
}

.action-btn {
    width: 30px;
    height: 30px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 0.8rem;
}

.action-btn.edit {
    background: rgba(59, 130, 246, 0.08);
    color: #3b82f6;
}

.action-btn.edit:hover {
    background: rgba(59, 130, 246, 0.15);
    transform: scale(1.1);
}

.action-btn.delete {
    background: rgba(239, 68, 68, 0.08);
    color: #ef4444;
}

.action-btn.delete:hover {
    background: rgba(239, 68, 68, 0.15);
    transform: scale(1.1);
}


.empty-row {
    text-align: center;
    padding: 3rem 0 !important;
    color: rgba(255, 255, 255, 0.2);
}

.empty-row i {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    color: rgba(255, 255, 255, 0.05);
}

.empty-row p {
    margin-bottom: 1rem;
}

.btn-add-small {
    display: inline-block;
    padding: 0.4rem 1.2rem;
    background: linear-gradient(135deg, #d4af37, #f5d76e);
    color: #0a0a0f;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-add-small:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);
    color: #0a0a0f;
}


@media (max-width: 1200px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 992px) {
    .welcome-section {
        flex-direction: column;
        align-items: flex-start;
        padding: 1.5rem;
    }
    .admin-profile-badge {
        width: 100%;
        border-radius: 12px;
        padding: 1rem;
    }
}

@media (max-width: 768px) {
    .quick-actions-bar {
        flex-direction: column;
    }
    .quick-action-btn {
        width: 100%;
        justify-content: center;
    }
    .stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    .stat-card {
        padding: 1rem;
    }
    .stat-number {
        font-size: 1.4rem;
    }
    .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
    }
    .section-header {
        flex-direction: column;
        align-items: stretch;
    }
    .btn-add {
        justify-content: center;
    }
    .products-table th,
    .products-table td {
        padding: 0.5rem 0.3rem;
        font-size: 0.75rem;
    }
    .product-img,
    .product-img-placeholder {
        width: 35px;
        height: 35px;
    }
}

@media (max-width: 576px) {
    .welcome-section {
        padding: 1rem;
    }
    .welcome-section h1 {
        font-size: 1.3rem;
    }
    .stats-grid {
        grid-template-columns: 1fr;
    }
    .products-section {
        padding: 1rem;
    }
    .products-table th,
    .products-table td {
        padding: 0.4rem 0.2rem;
        font-size: 0.65rem;
    }
    .action-btn {
        width: 26px;
        height: 26px;
        font-size: 0.7rem;
    }
    .profile-avatar-sm {
        width: 35px;
        height: 35px;
        font-size: 1rem;
    }
    .profile-name {
        font-size: 0.85rem;
    }
}
</style>

<?php include 'includes/admin_footer.php'; ?>