<?php
require_once 'config/database.php';
require_once 'config/session.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user data
$user_result = executeQuery("SELECT * FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($user_result);

// Get cart items
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
$errors = array();

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
    
    // Card details (only if card payment selected)
    if ($payment_method === 'Card') {
        $card_number = str_replace(' ', '', $_POST['card_number']);
        $card_name = sanitizeInput($_POST['card_name']);
        $card_expiry = $_POST['card_expiry'];
        $card_cvv = $_POST['card_cvv'];
        
        // Basic card validation
        if (empty($card_number) || strlen($card_number) != 16) {
            $errors['card_number'] = 'Please enter a valid 16-digit card number';
        }
        if (empty($card_name)) {
            $errors['card_name'] = 'Name on card is required';
        }
        if (empty($card_expiry)) {
            $errors['card_expiry'] = 'Expiry date is required';
        }
        if (empty($card_cvv) || strlen($card_cvv) < 3) {
            $errors['card_cvv'] = 'Please enter a valid CVV';
        }
    }
    
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
                    <form method="POST" id="checkoutForm">
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
                        
                        <!-- Payment Method -->
                        <div class="form-group">
                            <label>Payment Method *</label>
                            <div class="payment-methods">
                                <label class="payment-option">
                                    <input type="radio" name="payment_method" value="COD" checked onclick="document.getElementById('cardPaymentForm').style.display='none'">
                                    <span><i class="fas fa-money-bill-wave"></i> Cash on Delivery</span>
                                </label>
                                <label class="payment-option">
                                    <input type="radio" name="payment_method" value="Card" onclick="document.getElementById('cardPaymentForm').style.display='block'">
                                    <span><i class="fas fa-credit-card"></i> Credit/Debit Card</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Card Payment Form (Hidden by default) -->
                        <div id="cardPaymentForm" style="display:none; margin-top:var(--spacing-md); padding:var(--spacing-md); background:rgba(255,255,255,0.03); border-radius:12px; border:1px solid rgba(212,175,55,0.08);">
                            <h4 style="color:var(--gold); margin-bottom:var(--spacing-md);"><i class="fas fa-credit-card"></i> Card Details</h4>
                            
                            <div class="form-group">
                                <label>Card Number *</label>
                                <input type="text" name="card_number" id="card_number" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19" oninput="formatCardNumber(this)">
                                <div class="form-error"><?php echo isset($errors['card_number']) ? $errors['card_number'] : ''; ?></div>
                            </div>
                            
                            <div class="form-group">
                                <label>Name on Card *</label>
                                <input type="text" name="card_name" id="card_name" class="form-control" placeholder="John Doe">
                                <div class="form-error"><?php echo isset($errors['card_name']) ? $errors['card_name'] : ''; ?></div>
                            </div>
                            
                            <div class="row row-2" style="gap:var(--spacing-md);">
                                <div class="form-group">
                                    <label>Expiry Date *</label>
                                    <input type="month" name="card_expiry" id="card_expiry" class="form-control">
                                    <div class="form-error"><?php echo isset($errors['card_expiry']) ? $errors['card_expiry'] : ''; ?></div>
                                </div>
                                <div class="form-group">
                                    <label>CVV *</label>
                                    <input type="password" name="card_cvv" id="card_cvv" class="form-control" placeholder="123" maxlength="4">
                                    <div class="form-error"><?php echo isset($errors['card_cvv']) ? $errors['card_cvv'] : ''; ?></div>
                                </div>
                            </div>
                            
                            <div class="card-icons" style="display:flex; gap:var(--spacing-sm); margin-top:var(--spacing-sm);">
                                <i class="fab fa-cc-visa" style="font-size:2rem; color:rgba(255,255,255,0.2);"></i>
                                <i class="fab fa-cc-mastercard" style="font-size:2rem; color:rgba(255,255,255,0.2);"></i>
                                <i class="fab fa-cc-amex" style="font-size:2rem; color:rgba(255,255,255,0.2);"></i>
                                <i class="fab fa-cc-discover" style="font-size:2rem; color:rgba(255,255,255,0.2);"></i>
                            </div>
                        </div>
                        
                        <button type="submit" name="place_order" class="btn btn-primary w-100" style="margin-top:var(--spacing-md);">
                            <i class="fas fa-check-circle"></i> Place Order
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Order Summary -->
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
// Format Card Number with spaces
function formatCardNumber(input) {
    var value = input.value.replace(/\s/g, '');
    var formatted = '';
    for (var i = 0; i < value.length; i++) {
        if (i > 0 && i % 4 === 0) {
            formatted += ' ';
        }
        formatted += value[i];
    }
    input.value = formatted;
}

// Delivery Date Validation
document.addEventListener('DOMContentLoaded', function() {
    var deliveryDate = document.getElementById('delivery_date');
    if (deliveryDate) {
        var today = new Date().toISOString().split('T')[0];
        var tomorrow = new Date(new Date().setDate(new Date().getDate() + 1)).toISOString().split('T')[0];
        deliveryDate.setAttribute('min', today);
        deliveryDate.setAttribute('max', tomorrow);
    }
});

// Form validation
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    var paymentMethod = document.querySelector('input[name="payment_method"]:checked');
    if (paymentMethod && paymentMethod.value === 'Card') {
        var cardNumber = document.getElementById('card_number');
        var cardName = document.getElementById('card_name');
        var cardExpiry = document.getElementById('card_expiry');
        var cardCvv = document.getElementById('card_cvv');
        
        var isValid = true;
        
        if (cardNumber.value.replace(/\s/g, '').length !== 16) {
            alert('Please enter a valid 16-digit card number');
            isValid = false;
        }
        if (!cardName.value.trim()) {
            alert('Please enter name on card');
            isValid = false;
        }
        if (!cardExpiry.value) {
            alert('Please select expiry date');
            isValid = false;
        }
        if (cardCvv.value.length < 3) {
            alert('Please enter valid CVV');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    }
});
</script>

<style>
.payment-methods {
    display: flex;
    gap: var(--spacing-md);
    flex-wrap: wrap;
}

.payment-option {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    padding: 0.75rem 1.5rem;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(212,175,55,0.08);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.3s ease;
}

.payment-option:hover {
    background: rgba(255,255,255,0.06);
    border-color: rgba(212,175,55,0.2);
}

.payment-option input[type="radio"] {
    accent-color: #d4af37;
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.payment-option span {
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.payment-option span i {
    color: var(--gold);
    width: 20px;
}

.card-icons i {
    transition: all 0.3s ease;
}

.card-icons i:hover {
    color: var(--gold) !important;
    transform: scale(1.1);
}
</style>

<?php include 'includes/footer.php'; ?>