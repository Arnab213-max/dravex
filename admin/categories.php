<?php
/**
 * Admin Categories Management
 * DRAVEX - Premium Streetwear E-Commerce Platform
 */

// Start session
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

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    
    if (!empty($name)) {
        $check = executeQuery("SELECT id FROM categories WHERE name = '$name'");
        if (mysqli_num_rows($check) == 0) {
            executeQuery("INSERT INTO categories (name, description) VALUES ('$name', '$description')");
            $_SESSION['flash']['success'] = 'Category added successfully!';
        } else {
            $_SESSION['flash']['error'] = 'Category already exists!';
        }
    } else {
        $_SESSION['flash']['error'] = 'Category name is required!';
    }
    header('Location: categories.php');
    exit();
}

// Handle Edit Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category'])) {
    $id = (int)$_POST['category_id'];
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    
    if (!empty($name) && $id > 0) {
        executeQuery("UPDATE categories SET name = '$name', description = '$description' WHERE id = $id");
        $_SESSION['flash']['success'] = 'Category updated successfully!';
    } else {
        $_SESSION['flash']['error'] = 'Category name is required!';
    }
    header('Location: categories.php');
    exit();
}

// Handle Delete Category
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        // Check if category has products
        $check = executeQuery("SELECT id FROM products WHERE category_id = $id");
        if (mysqli_num_rows($check) > 0) {
            $_SESSION['flash']['error'] = 'Cannot delete category with products!';
        } else {
            executeQuery("DELETE FROM categories WHERE id = $id");
            $_SESSION['flash']['success'] = 'Category deleted successfully!';
        }
    }
    header('Location: categories.php');
    exit();
}

// Get all categories
$categories = executeQuery("SELECT * FROM categories ORDER BY id DESC");

include 'includes/admin_header.php';
?>

<div class="admin-container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
        <h2 class="page-title"><i class="fas fa-tags" style="color:var(--gold);"></i> Categories</h2>
        <button class="btn btn-primary" onclick="document.getElementById('addCategoryModal').style.display='flex'">
            <i class="fas fa-plus"></i> Add Category
        </button>
    </div>

    <?php if (isset($_SESSION['flash']['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['flash']['success']; unset($_SESSION['flash']['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash']['error'])): ?>
        <div class="alert alert-error"><?php echo $_SESSION['flash']['error']; unset($_SESSION['flash']['error']); ?></div>
    <?php endif; ?>

    <!-- Categories Table -->
    <div class="glass-table-wrapper">
        <table class="glass-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category Name</th>
                    <th>Description</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($categories) > 0): ?>
                    <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                        <tr>
                            <td><?php echo $cat['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($cat['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($cat['description']); ?></td>
                            <td><?php echo date('d M Y', strtotime($cat['created_at'])); ?></td>
                            <td>
                                <button onclick="editCategory(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars($cat['name']); ?>', '<?php echo htmlspecialchars($cat['description']); ?>')" 
                                        class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="categories.php?delete=<?php echo $cat['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Delete this category?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:2rem; color:var(--text-muted);">
                            No categories found. Add your first category!
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Category Modal -->
<div id="addCategoryModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); backdrop-filter:blur(10px); z-index:1000; align-items:center; justify-content:center;">
    <div class="glass-card" style="max-width:500px; width:90%; padding:2rem;">
        <h3 style="color:var(--gold); margin-bottom:1.5rem;"><i class="fas fa-plus-circle"></i> Add Category</h3>
        <form method="POST">
            <div class="form-group">
                <label>Category Name *</label>
                <input type="text" name="name" class="form-control" placeholder="Enter category name" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Enter category description"></textarea>
            </div>
            <div style="display:flex; gap:1rem; margin-top:1.5rem;">
                <button type="submit" name="add_category" class="btn btn-primary"><i class="fas fa-save"></i> Save Category</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('addCategoryModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editCategoryModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); backdrop-filter:blur(10px); z-index:1000; align-items:center; justify-content:center;">
    <div class="glass-card" style="max-width:500px; width:90%; padding:2rem;">
        <h3 style="color:var(--gold); margin-bottom:1.5rem;"><i class="fas fa-edit"></i> Edit Category</h3>
        <form method="POST">
            <input type="hidden" name="category_id" id="edit_category_id">
            <div class="form-group">
                <label>Category Name *</label>
                <input type="text" name="name" id="edit_category_name" class="form-control" placeholder="Enter category name" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="edit_category_description" class="form-control" rows="3" placeholder="Enter category description"></textarea>
            </div>
            <div style="display:flex; gap:1rem; margin-top:1.5rem;">
                <button type="submit" name="edit_category" class="btn btn-primary"><i class="fas fa-save"></i> Update Category</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('editCategoryModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function editCategory(id, name, description) {
    document.getElementById('edit_category_id').value = id;
    document.getElementById('edit_category_name').value = name;
    document.getElementById('edit_category_description').value = description || '';
    document.getElementById('editCategoryModal').style.display = 'flex';
}
</script>

<style>
.glass-table-wrapper {
    overflow-x: auto;
}
.glass-table {
    background: rgba(255,255,255,0.04);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(212,175,55,0.08);
    border-radius: 12px;
    overflow: hidden;
    width: 100%;
}
.glass-table th {
    background: rgba(212,175,55,0.08);
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: var(--gold);
    border-bottom: 2px solid rgba(212,175,55,0.1);
}
.glass-table td {
    padding: 1rem;
    border-bottom: 1px solid rgba(212,175,55,0.05);
    color: rgba(255,255,255,0.8);
}
.glass-table tr:hover td {
    background: rgba(255,255,255,0.02);
}
</style>

<?php include 'includes/admin_footer.php'; ?>