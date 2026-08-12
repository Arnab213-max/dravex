<?php
require_once 'config/database.php';
require_once 'config/session.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$order_number = isset($_GET['order']) ? $_GET['order'] : '';

if (empty($order_number)) {
    header('Location: orders.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_query = "SELECT * FROM orders WHERE order_number = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $order_query);
mysqli_stmt_bind_param($stmt, "si", $order_number, $user_id);
mysqli_stmt_execute($stmt);
$order_result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($order_result);
mysqli_stmt_close($stmt);

if (!$order) {
    header('Location: orders.php');
    exit();
}

$items_query = "SELECT oi.*, p.name, p.image 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?";
$stmt = mysqli_prepare($conn, $items_query);
mysqli_stmt_bind_param($stmt, "i", $order['id']);
mysqli_stmt_execute($stmt);
$items_result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

include 'includes/header.php';
?>

<main>
    <div class="container" style="padding-top:var(--spacing-xl); padding-bottom:var(--spacing-xl);">
        <div class="order-success">
            <div class="glass-card success-card animate-zoom">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1>Order Placed Successfully!</h1>
                <p class="success-message">Thank you for your order. We'll deliver it soon.</p>
                
                <div class="order-details">
                    <div class="detail-row">
                        <span>Order Number:</span>
                        <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                    </div>
                    <div class="detail-row">
                        <span>Total Amount:</span>
                        <strong>₹<?php echo number_format($order['total_amount'], 2); ?></strong>
                    </div>
                    <div class="detail-row">
                        <span>Delivery Date:</span>
                        <strong><?php echo date('F d, Y', strtotime($order['delivery_date'])); ?></strong>
                    </div>
                    <div class="detail-row">
                        <span>Payment Method:</span>
                        <strong><?php echo htmlspecialchars($order['payment_method']); ?></strong>
                    </div>
                    <div class="detail-row">
                        <span>Status:</span>
                        <span class="badge badge-success"><?php echo ucfirst($order['status']); ?></span>
                    </div>
                </div>

                <div class="order-items-summary">
                    <h3>Order Items</h3>
                    <div class="items-list">
                        <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                            <div class="item-row">
                                <span><?php echo htmlspecialchars($item['name']); ?></span>
                                <span>× <?php echo $item['quantity']; ?></span>
                                <span>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div class="success-actions">
                    <a href="orders.php" class="btn btn-secondary">
                        <i class="fas fa-list"></i> View My Orders
                    </a>
                    <a href="products.php" class="btn btn-primary">
                        <i class="fas fa-shopping-bag"></i> Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.success-card {
    max-width: 600px;
    margin: 0 auto;
    text-align: center;
    padding: 3rem 2rem;
}

.success-icon {
    font-size: 4rem;
    color: var(--success);
    margin-bottom: 1rem;
}

.success-icon i {
    background: rgba(255,255,255,0.05);
    padding: 1rem;
    border-radius: 50%;
}

.success-message {
    font-size: 1.1rem;
    color: var(--text-secondary);
    margin-bottom: 2rem;
}

.order-details {
    text-align: left;
    margin: 2rem 0;
    padding: 1rem;
    background: rgba(255,255,255,0.03);
    border-radius: var(--radius-md);
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.detail-row:last-child {
    border-bottom: none;
}

.order-items-summary {
    text-align: left;
    margin: 2rem 0;
}

.order-items-summary h3 {
    margin-bottom: 1rem;
}

.items-list {
    background: rgba(255,255,255,0.03);
    border-radius: var(--radius-md);
    padding: 1rem;
}

.item-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.item-row:last-child {
    border-bottom: none;
}

.success-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .success-actions {
        flex-direction: column;
        align-items: center;
    }
    .success-actions .btn {
        width: 100%;
        max-width: 300px;
    }
    .detail-row {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
}
</style>

<?php include 'includes/footer.php'; ?>