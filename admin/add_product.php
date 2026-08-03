<?php
/**
 * Admin Add Product - Full Screen Design
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

// Get categories for dropdown
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

<div class="admin-page">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-plus-circle"></i> Add New Product</h1>
            <p class="page-subtitle">Fill in the details below to add a new product to your store</p>
        </div>
        <div class="header-actions">
            <a href="products.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Products
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['flash']['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['flash']['success']; unset($_SESSION['flash']['success']); ?></div>
    <?php endif; ?>

    <?php if (isset($errors['general'])): ?>
        <div class="alert alert-error"><?php echo $errors['general']; ?></div>
    <?php endif; ?>

    <div class="form-wrapper">
        <form method="POST" enctype="multipart/form-data" class="product-form" id="productForm">
            
            <!-- Top Section: Image Upload & Basic Info -->
            <div class="form-row two-col">
                <!-- Left: Image Upload -->
                <div class="form-section image-upload-section">
                    <h3><i class="fas fa-image"></i> Product Image</h3>
                    <div class="image-upload-wrapper">
                        <div class="image-preview" id="imagePreview">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Click to upload image</p>
                            <span>JPG, PNG, GIF, WEBP (Max 5MB)</span>
                        </div>
                        <input type="file" name="image" id="imageInput" accept="image/*" onchange="previewImage(event)">
                    </div>
                    <?php if (isset($errors['image'])): ?>
                        <div class="form-error"><?php echo $errors['image']; ?></div>
                    <?php endif; ?>
                </div>

                <!-- Right: Basic Info -->
                <div class="form-section">
                    <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                    <div class="form-group">
                        <label>Product Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($form_data['name']); ?>" placeholder="Enter product name" required>
                        <?php if (isset($errors['name'])): ?>
                            <div class="form-error"><?php echo $errors['name']; ?></div>
                        <?php endif; ?>
                    </div>
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
                </div>
            </div>

            <!-- Description -->
            <div class="form-section">
                <h3><i class="fas fa-align-left"></i> Description</h3>
                <div class="form-group">
                    <label>Product Description <span class="required">*</span></label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Describe your product in detail"><?php echo htmlspecialchars($form_data['description']); ?></textarea>
                    <?php if (isset($errors['description'])): ?>
                        <div class="form-error"><?php echo $errors['description']; ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pricing & Stock -->
            <div class="form-row three-col">
                <div class="form-section">
                    <h3><i class="fas fa-tag"></i> Pricing</h3>
                    <div class="form-group">
                        <label>Price (₹) <span class="required">*</span></label>
                        <input type="number" name="price" class="form-control" step="0.01" min="0" value="<?php echo $form_data['price']; ?>" placeholder="0.00" required>
                        <?php if (isset($errors['price'])): ?>
                            <div class="form-error"><?php echo $errors['price']; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-section">
                    <h3><i class="fas fa-percent"></i> Discount</h3>
                    <div class="form-group">
                        <label>Discount Price (₹)</label>
                        <input type="number" name="discount_price" class="form-control" step="0.01" min="0" value="<?php echo $form_data['discount_price']; ?>" placeholder="0.00">
                    </div>
                </div>
                <div class="form-section">
                    <h3><i class="fas fa-boxes"></i> Stock</h3>
                    <div class="form-group">
                        <label>Quantity <span class="required">*</span></label>
                        <input type="number" name="quantity" class="form-control" min="0" value="<?php echo $form_data['quantity']; ?>" placeholder="0" required>
                        <?php if (isset($errors['quantity'])): ?>
                            <div class="form-error"><?php echo $errors['quantity']; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Featured Settings -->
            <div class="form-section">
                <h3><i class="fas fa-star"></i> Product Status</h3>
                <div class="checkbox-group">
                    <label class="checkbox-item">
                        <input type="checkbox" name="featured" value="1">
                        <span class="checkmark"></span>
                        <span class="checkbox-label">Featured Product</span>
                        <small>Show this product on the homepage</small>
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="trending" value="1">
                        <span class="checkmark"></span>
                        <span class="checkbox-label">Trending Product</span>
                        <small>Mark as trending</small>
                    </label>
                    <label class="checkbox-item">
                        <input type="checkbox" name="latest" value="1">
                        <span class="checkmark"></span>
                        <span class="checkbox-label">Latest Arrival</span>
                        <small>Show in new arrivals section</small>
                    </label>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Add Product
                </button>
                <a href="products.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<style>

.admin-page {
    padding: 0;
    max-width: 1200px;
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


.form-wrapper {
    background: rgba(255, 255, 255, 0.02);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(212, 175, 55, 0.06);
    border-radius: 16px;
    padding: 2rem;
}


.form-row {
    display: grid;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-row.two-col {
    grid-template-columns: 1fr 1fr;
}

.form-row.three-col {
    grid-template-columns: 1fr 1fr 1fr;
}

.form-section {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(212, 175, 55, 0.06);
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
}

.form-section:hover {
    border-color: rgba(212, 175, 55, 0.12);
}

.form-section h3 {
    color: #d4af37;
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-section h3 i {
    color: #d4af37;
}


.form-group {
    margin-bottom: 1rem;
}

.form-group:last-child {
    margin-bottom: 0;
}

.form-group label {
    display: block;
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.85rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.form-group .required {
    color: #ef4444;
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(212, 175, 55, 0.08);
    border-radius: 8px;
    color: #ffffff;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    font-family: 'Inter', sans-serif;
}

.form-control:focus {
    outline: none;
    border-color: rgba(212, 175, 55, 0.3);
    box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.05);
}

.form-control::placeholder {
    color: rgba(255, 255, 255, 0.2);
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


.image-upload-wrapper {
    position: relative;
}

.image-preview {
    width: 100%;
    aspect-ratio: 1/1;
    max-height: 300px;
    background: rgba(255, 255, 255, 0.03);
    border: 2px dashed rgba(212, 175, 55, 0.15);
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    color: rgba(255, 255, 255, 0.2);
    overflow: hidden;
}

.image-preview:hover {
    border-color: rgba(212, 175, 55, 0.3);
    background: rgba(255, 255, 255, 0.05);
}

.image-preview i {
    font-size: 3rem;
    color: rgba(212, 175, 55, 0.3);
    margin-bottom: 0.5rem;
}

.image-preview p {
    font-size: 1rem;
    margin-bottom: 0.25rem;
}

.image-preview span {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.15);
}

.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

#imageInput {
    display: none;
}

.checkbox-group {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 1rem;
}

.checkbox-item {
    display: flex;
    flex-direction: column;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(212, 175, 55, 0.06);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.checkbox-item:hover {
    background: rgba(255, 255, 255, 0.04);
    border-color: rgba(212, 175, 55, 0.12);
}

.checkbox-item input[type="checkbox"] {
    display: none;
}

.checkbox-item .checkmark {
    width: 18px;
    height: 18px;
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-radius: 4px;
    display: inline-block;
    position: relative;
    transition: all 0.3s ease;
    margin-bottom: 0.5rem;
}

.checkbox-item input:checked + .checkmark {
    background: #d4af37;
    border-color: #d4af37;
}

.checkbox-item input:checked + .checkmark::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #0a0a0f;
    font-size: 12px;
    font-weight: 700;
}

.checkbox-item .checkbox-label {
    color: #ffffff;
    font-weight: 500;
    font-size: 0.9rem;
}

.checkbox-item small {
    color: rgba(255, 255, 255, 0.3);
    font-size: 0.75rem;
    margin-top: 0.25rem;
}


.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid rgba(212, 175, 55, 0.06);
    flex-wrap: wrap;
}

.btn-lg {
    padding: 0.75rem 2.5rem;
    font-size: 1rem;
}


.alert {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    border: 1px solid transparent;
}

.alert-success {
    background: rgba(16, 185, 129, 0.1);
    border-color: rgba(16, 185, 129, 0.2);
    color: #10b981;
}

.alert-error {
    background: rgba(239, 68, 68, 0.1);
    border-color: rgba(239, 68, 68, 0.2);
    color: #ef4444;
}


@media (max-width: 992px) {
    .form-row.two-col {
        grid-template-columns: 1fr;
    }
    .form-row.three-col {
        grid-template-columns: 1fr 1fr;
    }
    .checkbox-group {
        grid-template-columns: 1fr 1fr;
    }
    .form-wrapper {
        padding: 1.5rem;
    }
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .form-row.three-col {
        grid-template-columns: 1fr;
    }
    .checkbox-group {
        grid-template-columns: 1fr;
    }
    .form-wrapper {
        padding: 1rem;
    }
    .form-section {
        padding: 1rem;
    }
    .form-actions {
        flex-direction: column;
    }
    .form-actions .btn {
        width: 100%;
        justify-content: center;
    }
    .header-actions {
        width: 100%;
    }
    .header-actions .btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .page-header h1 {
        font-size: 1.5rem;
    }
    .form-wrapper {
        padding: 0.75rem;
    }
    .image-preview {
        max-height: 200px;
    }
}
</style>

<script>
function previewImage(event) {
    const reader = new FileReader();
    const preview = document.getElementById('imagePreview');
    const file = event.target.files[0];
    
    if (file) {
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Product Image">`;
            preview.style.borderColor = '#d4af37';
        };
        reader.readAsDataURL(file);
    }
}

// Drag and drop support
document.addEventListener('DOMContentLoaded', function() {
    const preview = document.getElementById('imagePreview');
    const input = document.getElementById('imageInput');
    
    preview.addEventListener('click', function() {
        input.click();
    });
    
    preview.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.borderColor = '#d4af37';
        this.style.background = 'rgba(212, 175, 55, 0.05)';
    });
    
    preview.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.style.borderColor = 'rgba(212, 175, 55, 0.15)';
        this.style.background = 'rgba(255, 255, 255, 0.03)';
    });
    
    preview.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.borderColor = 'rgba(212, 175, 55, 0.15)';
        this.style.background = 'rgba(255, 255, 255, 0.03)';
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            input.files = files;
            const event = new Event('change');
            input.dispatchEvent(event);
        }
    });
});
</script>

<?php include 'includes/admin_footer.php'; ?>