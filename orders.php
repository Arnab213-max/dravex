<?php
require_once 'config/database.php';
require_once 'config/session.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Getting  the orders
$orders_query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
$orders_result = executeQuery($orders_query);

include 'includes/header.php';
?>

<main>
    <div class="container" style="padding-top:var(--spacing-xl); padding-bottom:var(--spacing-xl);">
        <h1 class="section-title" style="text-align:left; margin-bottom:var(--spacing-lg);">
            <i class="fas fa-box" style="color:var(--gold);"></i> My Orders
        </h1>
        
        <?php if (mysqli_num_rows($orders_result) > 0): ?>
            <div class="orders-list">
                <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                    <div class="glass-card order-card animate-fade">
                        <div class="order-header">
                            <div class="order-info">
                                <span class="order-number">#<?php echo htmlspecialchars($order['order_number']); ?></span>
                                <span class="order-date">
                                    <i class="fas fa-calendar-alt"></i>
                                    <?php echo date('F d, Y', strtotime($order['created_at'])); ?>
                                </span>
                            </div>
                            <div class="order-status">
                                <?php 
                                    $status_colors = [
                                        'pending' => 'badge-warning',
                                        'processing' => 'badge-info',
                                        'completed' => 'badge-success',
                                        'cancelled' => 'badge-danger'
                                    ];
                                    $status = $order['status'];
                                    $color = isset($status_colors[$status]) ? $status_colors[$status] : 'badge-warning';
                                ?>
                                <span class="badge <?php echo $color; ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="order-body">
                            <div class="order-details-grid">
                                <div class="detail-item">
                                    <span class="label">Total Amount</span>
                                    <span class="value">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Delivery Date</span>
                                    <span class="value"><?php echo date('F d, Y', strtotime($order['delivery_date'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Payment Method</span>
                                    <span class="value"><?php echo htmlspecialchars($order['payment_method']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Shipping Address</span>
                                    <span class="value">
                                        <?php 
                                            echo htmlspecialchars($order['shipping_address']) . ', ';
                                            echo htmlspecialchars($order['city']) . ', ';
                                            echo htmlspecialchars($order['state']) . ' - ';
                                            echo htmlspecialchars($order['zipcode']);
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="order-footer">
                            <a href="order_success.php?order=<?php echo $order['order_number']; ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="glass-card" style="text-align:center; padding:3rem;">
                <i class="fas fa-box-open" style="font-size:3rem; color:var(--text-muted);"></i>
                <h3>No orders yet</h3>
                <p class="text-muted">You haven't placed any orders yet. Start shopping now!</p>
                <a href="products.php" class="btn btn-primary">Start Shopping</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
.orders-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-lg);
    max-width: 900px;
    margin: 0 auto;
}

.order-card {
    padding: var(--spacing-lg);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: var(--spacing-md);
    border-bottom: 1px solid rgba(255,255,255,0.05);
    flex-wrap: wrap;
    gap: var(--spacing-sm);
}

.order-info {
    display: flex;
    align-items: center;
    gap: var(--spacing-lg);
    flex-wrap: wrap;
}

.order-number {
    font-weight: 700;
    font-size: 1.1rem;
    color: var(--gold);
}

.order-date {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.order-body {
    padding: var(--spacing-md) 0;
}

.order-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-md);
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xs);
}

.detail-item .label {
    font-size: 0.8rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-item .value {
    font-size: 0.95rem;
    color: var(--text-primary);
}

.order-footer {
    padding-top: var(--spacing-md);
    border-top: 1px solid rgba(255,255,255,0.05);
    display: flex;
    justify-content: flex-end;
}
</style>

<?php include 'includes/footer.php'; ?>