<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../config/session.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $result = executeQuery("SELECT image FROM products WHERE id = $id");
    $product = mysqli_fetch_assoc($result);
    if ($product && $product['image'] != 'default.jpg' && file_exists('../uploads/' . $product['image'])) {
        unlink('../uploads/' . $product['image']);
    }
    executeQuery("DELETE FROM products WHERE id = $id");
    $_SESSION['flash']['success'] = 'Product deleted successfully!';
    header('Location: products.php');
    exit();
}

$products = executeQuery("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");

include 'includes/admin_header.php';
?>

<div class="admin-page">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-box"></i> Manage Products</h1>
            <p class="page-subtitle">View and manage all products in your store</p>
        </div>
        <div class="header-actions">
            <a href="add_product.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Product
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['flash']['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['flash']['success']; unset($_SESSION['flash']['success']); ?></div>
    <?php endif; ?>

    <div class="table-wrapper">
        <?php if (mysqli_num_rows($products) > 0): ?>
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($p = mysqli_fetch_assoc($products)): ?>
                        <tr>
                            <td>
                                <?php 
                                $img_path = '../uploads/' . $p['image'];
                                if (file_exists($img_path)): 
                                ?>
                                    <img src="<?php echo $img_path; ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" class="product-thumb">
                                <?php else: ?>
                                    <div class="product-thumb-placeholder">
                                        <i class="fas fa-tshirt"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($p['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($p['category_name']); ?></td>
                            <td>₹<?php echo number_format($p['price'], 2); ?></td>
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
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3>No Products Found</h3>
                <p>Start by adding your first product to the store.</p>
                <a href="add_product.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Product
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.admin-page {
    padding: 0;
    max-width: 1400px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.page-header h1 {
    color: #ffffff;
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.page-header h1 i {
    color: #d4af37;
    margin-right: 0.75rem;
}

.page-subtitle {
    color: rgba(255, 255, 255, 0.5);
    font-size: 1rem;
}

.header-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.5rem;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: linear-gradient(135deg, #d4af37, #f5d76e);
    color: #0a0a0f;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);
    color: #0a0a0f;
}

.table-wrapper {
    background: rgba(255, 255, 255, 0.02);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(212, 175, 55, 0.06);
    border-radius: 16px;
    padding: 1.5rem;
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

.product-thumb {
    width: 45px;
    height: 45px;
    border-radius: 8px;
    object-fit: cover;
    border: 1px solid rgba(212, 175, 55, 0.06);
}

.product-thumb-placeholder {
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

.alert {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.alert-success {
    background: rgba(16, 185, 129, 0.1);
    border-color: rgba(16, 185, 129, 0.2);
    color: #10b981;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-state i {
    font-size: 4rem;
    color: rgba(255, 255, 255, 0.05);
    margin-bottom: 1rem;
}

.empty-state h3 {
    color: rgba(255, 255, 255, 0.5);
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: rgba(255, 255, 255, 0.3);
    margin-bottom: 1.5rem;
}

@media (max-width: 992px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .header-actions {
        width: 100%;
    }
    .header-actions .btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .table-wrapper {
        padding: 1rem;
    }
    .products-table th,
    .products-table td {
        padding: 0.5rem 0.3rem;
        font-size: 0.75rem;
    }
    .product-thumb,
    .product-thumb-placeholder {
        width: 35px;
        height: 35px;
    }
}

@media (max-width: 576px) {
    .page-header h1 {
        font-size: 1.5rem;
    }
    .table-wrapper {
        padding: 0.75rem;
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
}
</style>

<?php include 'includes/admin_footer.php'; ?>