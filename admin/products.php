<?php
require_once '../config/database.php';
require_once '../config/session.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit();
}

$products = executeQuery("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");

include 'includes/admin_header.php';
?>

<div class="admin-container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
        <h2 class="page-title"><i class="fas fa-box" style="color:var(--gold);"></i> Products</h2>
        <a href="add_product.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Product</a>
    </div>

    <?php if (isset($_SESSION['flash']['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['flash']['success']; unset($_SESSION['flash']['success']); ?></div>
    <?php endif; ?>

    <div class="glass-table-wrapper">
        <table class="glass-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($products) > 0): ?>
                    <?php while ($p = mysqli_fetch_assoc($products)): ?>
                    <tr>
                        <td><img src="../uploads/<?php echo htmlspecialchars($p['image']); ?>" style="width:50px; height:50px; object-fit:cover; border-radius:8px;"></td>
                        <td><?php echo htmlspecialchars($p['name']); ?></td>
                        <td><?php echo htmlspecialchars($p['category_name']); ?></td>
                        <td>₹<?php echo number_format($p['price'], 2); ?></td>
                        <td><?php echo $p['quantity']; ?></td>
                        <td>
                            <a href="edit_product.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                            <a href="delete_product.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this product?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center; padding:2rem;">No products found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.admin-container { padding: 2rem; }
.page-title { font-size: 2rem; }
.glass-table-wrapper { overflow-x: auto; }
.glass-table { background: rgba(255,255,255,0.04); backdrop-filter: blur(20px); border: 1px solid rgba(212,175,55,0.1); border-radius: var(--radius-lg); overflow: hidden; width: 100%; }
.glass-table th { background: rgba(212,175,55,0.1); padding: 1rem; text-align: left; font-weight: 600; border-bottom: 2px solid rgba(212,175,55,0.1); }
.glass-table td { padding: 1rem; border-bottom: 1px solid rgba(212,175,55,0.05); }
.glass-table tr:hover { background: rgba(255,255,255,0.02); }
</style>

<?php include 'includes/admin_footer.php'; ?>