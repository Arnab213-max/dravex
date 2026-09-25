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

// Get order details
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

// Get tracking history
$tracking_query = "SELECT * FROM order_tracking WHERE order_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $tracking_query);
mysqli_stmt_bind_param($stmt, "i", $order['id']);
mysqli_stmt_execute($stmt);
$tracking_result = mysqli_stmt_get_result($stmt);
$tracking_history = [];
while ($track = mysqli_fetch_assoc($tracking_result)) {
    $tracking_history[] = $track;
}
mysqli_stmt_close($stmt);

// Get order items
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
        <div class="tracking-page">
            <div class="tracking-header">
                <h1><i class="fas fa-shipping-fast" style="color:var(--gold);"></i> Track Your Order</h1>
                <p>Order #<?php echo htmlspecialchars($order['order_number']); ?></p>
            </div>

            <!-- Order Summary -->
            <div class="glass-card order-summary">
                <div class="summary-grid">
                    <div>
                        <span class="label">Order Date</span>
                        <span class="value"><?php echo date('F d, Y', strtotime($order['created_at'])); ?></span>
                    </div>
                    <div>
                        <span class="label">Total Amount</span>
                        <span class="value">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                    <div>
                        <span class="label">Delivery Date</span>
                        <span class="value"><?php echo date('F d, Y', strtotime($order['delivery_date'])); ?></span>
                    </div>
                    <div>
                        <span class="label">Current Status</span>
                        <span class="badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Tracking Timeline -->
            <div class="glass-card tracking-timeline">
                <h3><i class="fas fa-clock" style="color:var(--gold);"></i> Tracking History</h3>
                
                <div class="timeline">
                    <?php if (!empty($tracking_history)): ?>
                        <?php foreach ($tracking_history as $index => $track): ?>
                            <div class="timeline-item <?php echo $index === 0 ? 'latest' : ''; ?>">
                                <div class="timeline-icon">
                                    <?php if ($index === 0): ?>
                                        <i class="fas fa-check-circle"></i>
                                    <?php else: ?>
                                        <i class="fas fa-circle"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <span class="timeline-status badge status-<?php echo $track['status']; ?>">
                                            <?php echo ucfirst($track['status']); ?>
                                        </span>
                                        <span class="timeline-date">
                                            <?php echo date('d M Y, h:i A', strtotime($track['created_at'])); ?>
                                        </span>
                                    </div>
                                    <p class="timeline-message"><?php echo htmlspecialchars($track['message']); ?></p>
                                    <?php if ($track['updated_by']): ?>
                                        <small class="timeline-updated">Updated by: <?php echo htmlspecialchars($track['updated_by']); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-tracking">
                            <i class="fas fa-box"></i>
                            <p>No tracking information available yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Order Items -->
            <div class="glass-card order-items">
                <h3><i class="fas fa-shopping-bag" style="color:var(--gold);"></i> Order Items</h3>
                <div class="items-list">
                    <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                        <div class="item-row">
                            <div class="item-image">
                                <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>
                            <div class="item-details">
                                <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                <span class="item-qty">× <?php echo $item['quantity']; ?></span>
                            </div>
                            <span class="item-price">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="tracking-actions">
                <a href="orders.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Orders
                </a>
                <a href="products.php" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                </a>
            </div>
        </div>
    </div>
</main>

<style>

.tracking-page {
    max-width: 900px;
    margin: 0 auto;
}

.tracking-header {
    text-align: center;
    margin-bottom: var(--spacing-xl);
}

.tracking-header h1 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.tracking-header p {
    color: var(--text-secondary);
    font-size: 1.1rem;
}

/* Order Summary */
.order-summary {
    margin-bottom: var(--spacing-lg);
    padding: var(--spacing-lg);
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--spacing-md);
}

.summary-grid .label {
    display: block;
    font-size: 0.7rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.summary-grid .value {
    display: block;
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-top: var(--spacing-xs);
}

/* Timeline */
.tracking-timeline {
    margin-bottom: var(--spacing-lg);
    padding: var(--spacing-lg);
}

.tracking-timeline h3 {
    margin-bottom: var(--spacing-lg);
    font-size: 1.1rem;
}

.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 7px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: rgba(255,255,255,0.05);
}

.timeline-item {
    position: relative;
    padding-bottom: var(--spacing-lg);
    padding-left: var(--spacing-lg);
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-item.latest .timeline-icon i {
    color: var(--gold);
}

.timeline-item.latest .timeline-content {
    background: rgba(212, 175, 55, 0.05);
    border-radius: var(--radius-md);
    padding: var(--spacing-md);
}

.timeline-icon {
    position: absolute;
    left: -2rem;
    top: 0;
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.timeline-icon i {
    font-size: 1rem;
    color: rgba(255,255,255,0.2);
}

.timeline-content .timeline-header {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    flex-wrap: wrap;
    margin-bottom: var(--spacing-sm);
}

.timeline-status {
    font-size: 0.75rem;
    padding: 0.2rem 0.8rem;
}

.timeline-date {
    font-size: 0.75rem;
    color: var(--text-muted);
}

.timeline-message {
    color: var(--text-secondary);
    font-size: 0.95rem;
    margin: 0;
}

.timeline-updated {
    display: block;
    color: var(--text-muted);
    font-size: 0.7rem;
    margin-top: var(--spacing-xs);
}

.no-tracking {
    text-align: center;
    padding: 2rem;
    color: var(--text-muted);
}

.no-tracking i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: rgba(255,255,255,0.05);
}

/* Order Items */
.order-items {
    margin-bottom: var(--spacing-lg);
    padding: var(--spacing-lg);
}

.order-items h3 {
    margin-bottom: var(--spacing-md);
    font-size: 1.1rem;
}

.items-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.item-row {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    padding: var(--spacing-sm);
    background: rgba(255,255,255,0.02);
    border-radius: var(--radius-sm);
}

.item-image {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-details {
    flex: 1;
}

.item-name {
    color: var(--text-primary);
    font-weight: 500;
}

.item-qty {
    color: var(--text-muted);
    font-size: 0.85rem;
    margin-left: var(--spacing-sm);
}

.item-price {
    font-weight: 600;
    color: var(--gold);
}

/* Tracking Actions */
.tracking-actions {
    display: flex;
    gap: var(--spacing-md);
    justify-content: center;
    flex-wrap: wrap;
    margin-top: var(--spacing-lg);
}

/* Status Badges */
.status-pending { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
.status-processing { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
.status-shipped { background: rgba(139, 92, 246, 0.15); color: #8b5cf6; }
.status-delivered { background: rgba(16, 185, 129, 0.15); color: #10b981; }
.status-cancelled { background: rgba(239, 68, 68, 0.15); color: #ef4444; }

/* Responsive */
@media (max-width: 768px) {
    .summary-grid {
        grid-template-columns: 1fr 1fr;
        gap: var(--spacing-md);
    }
    .tracking-header h1 {
        font-size: 1.5rem;
    }
    .tracking-timeline {
        padding: var(--spacing-md);
    }
    .order-items {
        padding: var(--spacing-md);
    }
    .tracking-actions {
        flex-direction: column;
    }
    .tracking-actions .btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 576px) {
    .summary-grid {
        grid-template-columns: 1fr;
    }
    .timeline-item.latest .timeline-content {
        padding: var(--spacing-sm);
    }
    .item-row {
        flex-wrap: wrap;
    }
    .item-price {
        width: 100%;
        text-align: right;
    }
}
</style>

<?php include 'includes/footer.php'; ?>