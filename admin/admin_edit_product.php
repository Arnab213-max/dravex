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

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id <= 0) {
    header('Location: products.php');
    exit();
}

$result = executeQuery("SELECT * FROM products WHERE id = $product_id");
$product = mysqli_fetch_assoc($result);
if (!$product) {
    header('Location: products.php');
    exit();
}

$categories = executeQuery("SELECT * FROM categories ORDER BY name");
$errors = [];
$form_data = $product;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data['name'] = sanitizeInput($_POST['name']);
    $form_data['description'] = sanitizeInput($_POST['description']);
    $form_data['price'] = (float)$_POST['price'];
    $form_data['discount_price'] = !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : NULL;
    $form_data['quantity'] = (int)$_POST['quantity'];
    $form_data['category_id'] = (int)$_POST['category_id'];
    $form_data['featured'] = isset($_POST['featured']) ? 1 : 0;
    $form_data['trending'] = isset($_POST['trending']) ? 1 : 0;
    $form_data['latest'] = isset($_POST['latest']) ? 1 : 0;
    
    if (empty($form_data['name'])) $errors['name'] = 'Product name is required';
    if (empty($form_data['description'])) $errors['description'] = 'Description is required';
    if ($form_data['price'] <= 0) $errors['price'] = 'Price must be greater than 0';
    if ($form_data['quantity'] < 0) $errors['quantity'] = 'Quantity cannot be negative';
    if ($form_data['category_id'] <= 0) $errors['category_id'] = 'Please select a category';
    
    $image_name = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($file_ext, $allowed)) {
            $errors['image'] = 'Only JPG, PNG, GIF, WEBP allowed';
        } elseif ($_FILES['image']['size'] > 5242880) {
            $errors['image'] = 'Image must be less than 5MB';
        } else {
            if ($product['image'] != 'default.jpg' && file_exists('../uploads/' . $product['image'])) {
                unlink('../uploads/' . $product['image']);
            }
            $image_name = 'product_' . time() . '_' . uniqid() . '.' . $file_ext;
            move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/' . $image_name);
        }
    }
    
    if (empty($errors)) {
        $discount = $form_data['discount_price'] ? $form_data['discount_price'] : 'NULL';
        executeQuery("UPDATE products SET category_id = $form_data[category_id], name = '$form_data[name]', description = '$form_data[description]', price = $form_data[price], discount_price = $discount, quantity = $form_data[quantity], image = '$image_name', featured = $form_data[featured], trending = $form_data[trending], latest = $form_data[latest] WHERE id = $product_id");
        $_SESSION['flash']['success'] = 'Product updated successfully!';
        header('Location: products.php');
        exit();
    }
}

include 'includes/admin_header.php';
?>

<div class="edit-product-page">
    <div class="page-header">
        <h1><i class="fas fa-edit"></i> Edit Product</h1>
        <a href="products.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>

    <?php if (isset($_SESSION['flash']['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['flash']['success']; unset($_SESSION['flash']['success']); ?></div>
    <?php endif; ?>

    <div class="edit-form-container">
        <form method="POST" enctype="multipart/form-data" class="edit-form">
            
            <!-- Product Name -->
            <div class="form-group">
                <label>Product Name <span class="required">*</span></label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($form_data['name']); ?>" placeholder="Enter product name" required>
                <?php if (isset($errors['name'])): ?>
                    <div class="form-error"><?php echo $errors['name']; ?></div>
                <?php endif; ?>
            </div>

            <!-- Category -->
            <div class="form-group">
                <label>Category <span class="required">*</span></label>
                <select name="category_id" class="form-control" required>
                    <option value="">Select Category</option>
                    <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $form_data['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <?php if (isset($errors['category_id'])): ?>
                    <div class="form-error"><?php echo $errors['category_id']; ?></div>
                <?php endif; ?>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label>Description <span class="required">*</span></label>
                <textarea name="description" class="form-control" rows="3" placeholder="Enter product description"><?php echo htmlspecialchars($form_data['description']); ?></textarea>
                <?php if (isset($errors['description'])): ?>
                    <div class="form-error"><?php echo $errors['description']; ?></div>
                <?php endif; ?>
            </div>

            <!-- Price & Discount Row -->
            <div class="form-row two-col">
                <div class="form-group">
                    <label>Price (₹) <span class="required">*</span></label>
                    <input type="number" name="price" class="form-control" step="0.01" min="0" value="<?php echo $form_data['price']; ?>" placeholder="0.00" required>
                    <?php if (isset($errors['price'])): ?>
                        <div class="form-error"><?php echo $errors['price']; ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Discount Price (₹)</label>
                    <input type="number" name="discount_price" class="form-control" step="0.01" min="0" value="<?php echo $form_data['discount_price']; ?>" placeholder="0.00">
                </div>
            </div>

            <!-- Quantity -->
            <div class="form-group">
                <label>Quantity <span class="required">*</span></label>
                <input type="number" name="quantity" class="form-control" min="0" value="<?php echo $form_data['quantity']; ?>" placeholder="0" required>
                <?php if (isset($errors['quantity'])): ?>
                    <div class="form-error"><?php echo $errors['quantity']; ?></div>
                <?php endif; ?>
            </div>

            <!-- Image Section -->
            <div class="image-section">
                <div class="image-section-title">Current Image</div>
                <div class="current-image-container">
                    <?php 
                    $img_path = '../uploads/' . $product['image'];
                    if (file_exists($img_path) && $product['image'] != 'default.jpg'): 
                    ?>
                        <img src="<?php echo $img_path; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="current-image">
                    <?php else: ?>
                        <div class="no-image-placeholder">
                            <i class="fas fa-image"></i>
                            <span>No image</span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="change-image-section">
                    <div class="change-image-title">Change Image</div>
                    <div class="file-input-wrapper">
                        <label for="imageInput" class="file-input-label">
                            <i class="fas fa-upload"></i> Choose File
                        </label>
                        <input type="file" name="image" id="imageInput" accept="image/*" onchange="previewImage(event)">
                        <span class="file-name" id="fileName">No file chosen</span>
                    </div>
                    <div class="image-hint">Leave empty to keep current image</div>
                    <?php if (isset($errors['image'])): ?>
                        <div class="form-error"><?php echo $errors['image']; ?></div>
                    <?php endif; ?>
                </div>

                <!-- Image Preview -->
                <div class="image-preview-container" id="imagePreviewContainer" style="display:none;">
                    <div class="image-preview-title">New Image Preview</div>
                    <div class="image-preview" id="imagePreview">
                        <img src="" alt="Preview">
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-update">
                    <i class="fas fa-save"></i> Update Product
                </button>
                <a href="products.php" class="btn btn-cancel">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<style>
/* Clean Edit Product Page */
.edit-product-page {
    padding: 0;
    max-width: 800px;
    margin: 0 auto;
}

/* Page Header */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.page-header h1 {
    color: #ffffff;
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
}

.page-header h1 i {
    color: #d4af37;
    margin-right: 0.75rem;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.5rem;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(212,175,55,0.08);
    border-radius: 8px;
    color: rgba(255,255,255,0.7);
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 0.85rem;
    font-weight: 500;
}

.btn-back:hover {
    background: rgba(255,255,255,0.08);
    transform: translateY(-2px);
    color: #ffffff;
}

/* Alert */
.alert {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.alert-success {
    background: rgba(16,185,129,0.1);
    border: 1px solid rgba(16,185,129,0.2);
    color: #10b981;
}

/* Form Container */
.edit-form-container {
    background: rgba(255,255,255,0.02);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(212,175,55,0.06);
    border-radius: 16px;
    padding: 2rem;
}

.edit-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Form Groups */
.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    color: rgba(255,255,255,0.7);
    font-size: 0.85rem;
    font-weight: 500;
}

.form-group .required {
    color: #ef4444;
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(212,175,55,0.08);
    border-radius: 8px;
    color: #ffffff;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    font-family: 'Inter', sans-serif;
}

.form-control:focus {
    outline: none;
    border-color: rgba(212,175,55,0.3);
    box-shadow: 0 0 0 3px rgba(212,175,55,0.05);
}

.form-control::placeholder {
    color: rgba(255,255,255,0.15);
}

.form-control.error {
    border-color: #ef4444;
}

.form-error {
    color: #ef4444;
    font-size: 0.8rem;
    margin-top: 0.25rem;
}

select.form-control option {
    background: #1a0a2e;
    color: #ffffff;
}

textarea.form-control {
    resize: vertical;
    min-height: 80px;
}

/* Two Column Layout */
.form-row.two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

/* Image Section */
.image-section {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(212,175,55,0.06);
    border-radius: 12px;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.image-section-title {
    color: rgba(255,255,255,0.6);
    font-size: 0.85rem;
    font-weight: 500;
}

.current-image-container {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 1rem;
    background: rgba(255,255,255,0.02);
    border-radius: 8px;
    min-height: 150px;
    border: 1px solid rgba(212,175,55,0.05);
}

.current-image {
    max-width: 150px;
    max-height: 150px;
    object-fit: cover;
    border-radius: 8px;
}

.no-image-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    color: rgba(255,255,255,0.15);
}

.no-image-placeholder i {
    font-size: 2.5rem;
}

.no-image-placeholder span {
    font-size: 0.85rem;
}

/* Change Image */
.change-image-section {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.change-image-title {
    color: rgba(255,255,255,0.6);
    font-size: 0.85rem;
    font-weight: 500;
}

.file-input-wrapper {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.file-input-wrapper input[type="file"] {
    display: none;
}

.file-input-label {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.5rem;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(212,175,55,0.08);
    border-radius: 8px;
    color: rgba(255,255,255,0.7);
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.85rem;
}

.file-input-label:hover {
    background: rgba(255,255,255,0.08);
    border-color: rgba(212,175,55,0.15);
}

.file-input-label i {
    color: #d4af37;
}

.file-name {
    color: rgba(255,255,255,0.3);
    font-size: 0.85rem;
}

.image-hint {
    color: rgba(255,255,255,0.2);
    font-size: 0.75rem;
}

/* Image Preview */
.image-preview-container {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.image-preview-title {
    color: rgba(255,255,255,0.6);
    font-size: 0.8rem;
    font-weight: 500;
}

.image-preview {
    max-width: 150px;
    border: 1px solid rgba(212,175,55,0.08);
    border-radius: 8px;
    overflow: hidden;
}

.image-preview img {
    width: 100%;
    height: auto;
    display: block;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 0.5rem;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 2rem;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-update {
    background: linear-gradient(135deg, #d4af37, #f5d76e);
    color: #0a0a0f;
}

.btn-update:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(212,175,55,0.3);
    color: #0a0a0f;
}

.btn-cancel {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(212,175,55,0.08);
    color: rgba(255,255,255,0.6);
}

.btn-cancel:hover {
    background: rgba(255,255,255,0.08);
    transform: translateY(-2px);
    color: #ffffff;
}

/* Responsive */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .btn-back {
        width: 100%;
        justify-content: center;
    }
    
    .edit-form-container {
        padding: 1.5rem;
    }
    
    .form-row.two-col {
        grid-template-columns: 1fr;
    }
    
    .file-input-wrapper {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .page-header h1 {
        font-size: 1.5rem;
    }
    
    .edit-form-container {
        padding: 1rem;
    }
    
    .image-section {
        padding: 1rem;
    }
    
    .current-image {
        max-width: 120px;
        max-height: 120px;
    }
}
</style>

<script>
function previewImage(event) {
    const file = event.target.files[0];
    const fileName = document.getElementById('fileName');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const preview = document.getElementById('imagePreview');
    
    if (file) {
        fileName.textContent = file.name;
        
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.querySelector('img').src = e.target.result;
            previewContainer.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        fileName.textContent = 'No file chosen';
        previewContainer.style.display = 'none';
    }
}
</script>

<?php include 'includes/admin_footer.php'; ?>