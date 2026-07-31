<?php
require_once '../config/database.php';
require_once '../config/session.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit();
}

$categories = executeQuery("SELECT * FROM categories ORDER BY name");
$errors = [];
$form_data = ['name' => '', 'description' => '', 'price' => '', 'discount_price' => '', 'quantity' => '', 'category_id' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data['name'] = sanitizeInput($_POST['name']);
    $form_data['description'] = sanitizeInput($_POST['description']);
    $form_data['price'] = (float)$_POST['price'];
    $form_data['discount_price'] = !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : NULL;
    $form_data['quantity'] = (int)$_POST['quantity'];
    $form_data['category_id'] = (int)$_POST['category_id'];
    
    if (empty($form_data['name'])) $errors['name'] = 'Product name is required';
    if (empty($form_data['description'])) $errors['description'] = 'Description is required';
    if ($form_data['price'] <= 0) $errors['price'] = 'Price must be greater than 0';
    if ($form_data['quantity'] < 0) $errors['quantity'] = 'Quantity cannot be negative';
    if ($form_data['category_id'] <= 0) $errors['category_id'] = 'Please select a category';
    
    $image_name = 'default.jpg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($file_ext, $allowed)) {
            $errors['image'] = 'Only JPG, PNG, GIF, WEBP allowed';
        } elseif ($_FILES['image']['size'] > 5242880) {
            $errors['image'] = 'Image must be less than 5MB';
        } else {
            if (!is_dir('../uploads')) mkdir('../uploads', 0777, true);
            $image_name = 'product_' . time() . '_' . uniqid() . '.' . $file_ext;
            move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/' . $image_name);
        }
    }
    
    if (empty($errors)) {
        $discount = $form_data['discount_price'] ? $form_data['discount_price'] : 'NULL';
        executeQuery("INSERT INTO products (category_id, name, description, price, discount_price, quantity, image) VALUES ($form_data[category_id], '$form_data[name]', '$form_data[description]', $form_data[price], $discount, $form_data[quantity], '$image_name')");
        $_SESSION['flash']['success'] = 'Product added successfully!';
        header('Location: products.php');
        exit();
    }
}

include 'includes/admin_header.php';
?>

<div class="admin-container">
    <h2 class="page-title"><i class="fas fa-plus-circle" style="color:var(--gold);"></i> Add Product</h2>
    <form method="POST" enctype="multipart/form-data" class="glass-form" style="max-width:800px;">
        <div class="row row-2" style="gap:var(--spacing-md);">
            <div class="form-group">
                <label>Product Name *</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($form_data['name']); ?>" required>
                <div class="form-error"><?php echo isset($errors['name']) ? $errors['name'] : ''; ?></div>
            </div>
            <div class="form-group">
                <label>Category *</label>
                <select name="category_id" class="form-control" required>
                    <option value="">Select Category</option>
                    <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $form_data['category_id'] == $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endwhile; ?>
                </select>
                <div class="form-error"><?php echo isset($errors['category_id']) ? $errors['category_id'] : ''; ?></div>
            </div>
        </div>
        <div class="form-group">
            <label>Description *</label>
            <textarea name="description" class="form-control" rows="3" required><?php echo htmlspecialchars($form_data['description']); ?></textarea>
            <div class="form-error"><?php echo isset($errors['description']) ? $errors['description'] : ''; ?></div>
        </div>
        <div class="row row-3" style="gap:var(--spacing-md);">
            <div class="form-group">
                <label>Price (₹) *</label>
                <input type="number" name="price" class="form-control" step="0.01" min="0" value="<?php echo $form_data['price']; ?>" required>
                <div class="form-error"><?php echo isset($errors['price']) ? $errors['price'] : ''; ?></div>
            </div>
            <div class="form-group">
                <label>Discount Price (₹)</label>
                <input type="number" name="discount_price" class="form-control" step="0.01" min="0" value="<?php echo $form_data['discount_price']; ?>">
            </div>
            <div class="form-group">
                <label>Quantity *</label>
                <input type="number" name="quantity" class="form-control" min="0" value="<?php echo $form_data['quantity']; ?>" required>
                <div class="form-error"><?php echo isset($errors['quantity']) ? $errors['quantity'] : ''; ?></div>
            </div>
        </div>
        <div class="form-group">
            <label>Product Image</label>
            <input type="file" name="image" class="form-control" accept="image/*">
            <small class="text-muted">Allowed: JPG, PNG, GIF, WEBP (Max 5MB)</small>
            <div class="form-error"><?php echo isset($errors['image']) ? $errors['image'] : ''; ?></div>
        </div>
        <div style="display:flex; gap:1rem; margin-top:2rem;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Add Product</button>
            <a href="products.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Cancel</a>
        </div>
    </form>
</div>

<style>
.admin-container { padding: 2rem; max-width: 900px; margin: 0 auto; }
.page-title { margin-bottom: 2rem; font-size: 2rem; }
.text-muted { color: var(--text-muted); font-size: 0.85rem; display: block; margin-top: 0.25rem; }
</style>

<?php include 'includes/admin_footer.php'; ?>