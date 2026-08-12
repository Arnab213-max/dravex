<?php
require_once 'config/database.php';
require_once 'config/session.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Getting  user data
$user_result = executeQuery("SELECT * FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($user_result);

// Gettting  cart items
$cart_query = "
    SELECT c.*, p.name, p.price, p.discount_price, p.image, p.quantity as stock 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = $user_id
";
$cart_result = executeQuery($cart_query);

$cart_items = [];
$subtotal = 0;
$total_items = 0;

if ($cart_result && mysqli_num_rows($cart_result) > 0) {
    while ($item = mysqli_fetch_assoc($cart_result)) {
        $item['price_to_use'] = $item['discount_price'] ?: $item['price'];
        $item['item_total'] = $item['price_to_use'] * $item['quantity'];
        $subtotal += $item['item_total'];
        $total_items += $item['quantity'];
        $cart_items[] = $item;
    }
}

if (empty($cart_items)) {
    header('Location: cart.php');
    exit();
}

$tax = $subtotal * 0.05;
$total = $subtotal + $tax;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $full_name = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    $city = sanitizeInput($_POST['city']);
    $state = sanitizeInput($_POST['state']);
    $zipcode = sanitizeInput($_POST['zipcode']);
    $delivery_date = $_POST['delivery_date'];
    $payment_method = $_POST['payment_method'];
    
    if (empty($full_name)) $errors['full_name'] = 'Full name is required';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email is required';
    if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) $errors['phone'] = 'Valid 10-digit phone is required';
    if (empty($address)) $errors['address'] = 'Address is required';
    if (empty($city)) $errors['city'] = 'City is required';
    if (empty($state)) $errors['state'] = 'State is required';
    if (empty($zipcode) || !preg_match('/^[0-9]{5,6}$/', $zipcode)) $errors['zipcode'] = 'Valid zipcode is required';
    
    $selected_date = new DateTime($delivery_date);
    $today = new DateTime('today');
    $tomorrow = new DateTime('tomorrow');
    if ($selected_date < $today || $selected_date > $tomorrow) {
        $errors['delivery_date'] = 'Only today and tomorrow can be selected';
    }
    
    if (empty($errors)) {
        $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
        
        mysqli_begin_transaction($conn);
        
        try {
            // Insert order
            $insert_order = "INSERT INTO orders (user_id, order_number, total_amount, delivery_date, payment_method, shipping_address, city, state, zipcode, phone) VALUES ($user_id, '$order_number', $total, '$delivery_date', '$payment_method', '$address', '$city', '$state', '$zipcode', '$phone')";
            executeQuery($insert_order);
            $order_id = mysqli_insert_id($conn);
            
            // Insert order items
            foreach ($cart_items as $item) {
                $insert_item = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ($order_id, " . $item['product_id'] . ", " . $item['quantity'] . ", " . $item['price_to_use'] . ")";
                executeQuery($insert_item);
                // Update stock
                executeQuery("UPDATE products SET quantity = quantity - " . $item['quantity'] . " WHERE id = " . $item['product_id'] . " AND quantity >= " . $item['quantity']);
            }
            
            // Clear cart
            executeQuery("DELETE FROM cart WHERE user_id = $user_id");
            mysqli_commit($conn);
            
            header('Location: order_success.php?order=' . $order_number);
            exit();
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $errors['general'] = 'Failed to place order. Please try again.';
        }
    }
}

include 'includes/header.php';
?>

<main>
    <div class="container" style="padding-top:var(--spacing-xl); padding-bottom:var(--spacing-xl);">
        <h1 class="section-title" style="text-align:left; margin-bottom:var(--spacing-lg);">
            <i class="fas fa-credit-card" style="color:var(--gold);"></i> Checkout
        </h1>
        
        <?php if (isset($errors['general'])): ?>
            <div class="alert alert-error"><?php echo $errors['general']; ?></div>
        <?php endif; ?>

        <div class="row row-2">
            <div>
                <div class="glass-card">
                    <h3 style="color:var(--gold); margin-bottom:var(--spacing-md);">
                        <i class="fas fa-address-card"></i> Billing Details
                    </h3>
                    <form method="POST">
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            <div class="form-error"><?php echo isset($errors['full_name']) ? $errors['full_name'] : ''; ?></div>
                        </div>
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            <div class="form-error"><?php echo isset($errors['email']) ? $errors['email'] : ''; ?></div>
                        </div>
                        <div class="form-group">
                            <label>Phone *</label>
                            <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                            <div class="form-error"><?php echo isset($errors['phone']) ? $errors['phone'] : ''; ?></div>
                        </div>
                        <div class="form-group">
                            <label>Address *</label>
                            <textarea name="address" class="form-control" rows="2" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                            <div class="form-error"><?php echo isset($errors['address']) ? $errors['address'] : ''; ?></div>
                        </div>
                        <div class="row row-3" style="gap:var(--spacing-md);">
                            <div class="form-group">
                                <label>City *</label>
                                <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($user['city']); ?>" required>
                                <div class="form-error"><?php echo isset($errors['city']) ? $errors['city'] : ''; ?></div>
                            </div>
                            <div class="form-group">
                                <label>State *</label>
                                <input type="text" name="state" class="form-control" value="<?php echo htmlspecialchars($user['state']); ?>" required>
                                <div class="form-error"><?php echo isset($errors['state']) ? $errors['state'] : ''; ?></div>
                            </div>
                            <div class="form-group">
                                <label>Zipcode *</label>
                                <input type="text" name="zipcode" class="form-control" value="<?php echo htmlspecialchars($user['zipcode']); ?>" required>
                                <div class="form-error"><?php echo isset($errors['zipcode']) ? $errors['zipcode'] : ''; ?></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Delivery Date *</label>
                            <input type="date" id="delivery_date" name="delivery_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            <small class="text-muted">Only today and tomorrow can be selected</small>
                            <div class="form-error"><?php echo isset($errors['delivery_date']) ? $errors['delivery_date'] : ''; ?></div>
                        </div>
                        <div class="form-group">
                            <label>Payment Method *</label>
                            <div class="payment-options">
                                <label class="payment-option">
                                    <input type="radio" name="payment_method" value="COD" checked>
                                    <span><i class="fas fa-money-bill-wave"></i> Cash on Delivery</span>
                                </label>
                            </div>
                        </div>
                        <button type="submit" name="place_order" class="btn btn-primary w-100">
                            <i class="fas fa-check-circle"></i> Place Order
                        </button>
                    </form>
                </div>
            </div>
            <div>
                <div class="glass-card" style="position:sticky; top:100px;">
                    <h3 style="color:var(--gold); margin-bottom:var(--spacing-md);">
                        <i class="fas fa-shopping-bag"></i> Order Summary
                    </h3>
                    <?php foreach ($cart_items as $item): ?>
                        <div style="display:flex; justify-content:space-between; padding:var(--spacing-sm) 0; border-bottom:1px solid rgba(255,255,255,0.05);">
                            <span><?php echo htmlspecialchars($item['name']); ?> × <?php echo $item['quantity']; ?></span>
                            <span>₹<?php echo number_format($item['item_total'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                    <div style="margin-top:10px; padding-top:10px; border-top:1px solid rgba(255,255,255,0.1);">
                        <div style="display:flex; justify-content:space-between;"><span>Subtotal</span><span>₹<?php echo number_format($subtotal, 2); ?></span></div>
                        <div style="display:flex; justify-content:space-between;"><span>Tax (5%)</span><span>₹<?php echo number_format($tax, 2); ?></span></div>
                        <div style="display:flex; justify-content:space-between;"><span>Delivery</span><span style="color:var(--success);">Free</span></div>
                        <div style="display:flex; justify-content:space-between; font-weight:700; font-size:1.2rem; padding-top:var(--spacing-md); border-top:2px solid var(--gold); margin-top:var(--spacing-sm);">
                            <span>Total</span>
                            <span style="color:var(--gold);">₹<?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var deliveryDate = document.getElementById('delivery_date');
    if (deliveryDate) {
        var today = new Date().toISOString().split('T')[0];
        var tomorrow = new Date(new Date().setDate(new Date().getDate() + 1)).toISOString().split('T')[0];
        deliveryDate.setAttribute('min', today);
        deliveryDate.setAttribute('max', tomorrow);
    }
});
</script>

<?php include 'includes/footer.php'; ?>