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

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($order_id <= 0) {
    header('Location: orders.php');
    exit();
}

// Get order details
$order_query = "SELECT o.*, u.full_name, u.email, u.phone FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = $order_id";
$order_result = executeQuery($order_query);
$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    header('Location: orders.php');
    exit();
}

// Get tracking history
$tracking_query = "SELECT * FROM order_tracking WHERE order_id = $order_id ORDER BY created_at DESC";
$tracking_result = executeQuery($tracking_query);
$tracking_history = [];
while ($track = mysqli_fetch_assoc($tracking_result)) {
    $tracking_history[] = $track;
}

include 'includes/admin_header.php';
?>

<div class="admin-page">
    <div class="page-header">
        <h1><i class="fas fa-shipping-fast" style="color:var(--gold);"></i> Order Tracking</h1>
        <div class="header-actions">
            <a href="orders.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
        </div>
    </div>

    <div class="tracking-container">
        <!-- Order Info -->
        <div class="glass-card order-info-card">
            <div class="order-info-grid">
                <div>
                    <span class="label">Order #</span>
                    <span class="value"><?php echo htmlspecialchars($order['order_number']); ?></span>
                </div>
                <div>
                    <span class="label">Customer</span>
                    <span class="value"><?php echo htmlspecialchars($order['full_name']); ?></span>
                </div>
                <div>
                    <span class="label">Email</span>
                    <span class="value"><?php echo htmlspecialchars($order['email']); ?></span>
                </div>
                <div>
                    <span class="label">Total</span>
                    <span class="value">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
                <div>
                    <span class="label">Status</span>
                    <span class="badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                </div>
                <div>
                    <span class="label">Date</span>
                    <span class="value"><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></span>
                </div>
            </div>
        </div>

        <!-- Tracking Timeline -->
        <div class="glass-card tracking-timeline-card">
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
                        <p>No tracking information available.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Add Tracking Entry -->
        <div class="glass-card add-tracking-card">
            <h3><i class="fas fa-plus-circle" style="color:var(--gold);"></i> Add Tracking Update</h3>
            <form method="POST" action="orders.php">
                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control" required>
                        <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tracking Message</label>
                    <textarea name="tracking_message" class="form-control" rows="3" placeholder="Enter tracking message..." required></textarea>
                </div>
                <button type="submit" name="update_status" class="btn btn-primary">
                    <i class="fas fa-save"></i> Add Update
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.admin-page {
    padding: 0;
    max-width: 900px;
    margin: 0 auto;
}

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
}

.page-header h1 i {
    color: #d4af37;
    margin-right: 0.75rem;
}

.tracking-container {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-lg);
}

.order-info-card {
    padding: var(--spacing-lg);
}

.order-info-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--spacing-md);
}

.order-info-grid .label {
    display: block;
    font-size: 0.7rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.order-info-grid .value {
    display: block;
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-top: var(--spacing-xs);
}

.tracking-timeline-card {
    padding: var(--spacing-lg);
}

.tracking-timeline-card h3 {
    margin-bottom: var(--spacing-lg);
    font-size: 1.1rem;
}

.tracking-timeline-card h3 i {
    margin-right: 0.5rem;
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

.add-tracking-card {
    padding: var(--spacing-lg);
}

.add-tracking-card h3 {
    margin-bottom: var(--spacing-md);
    font-size: 1.1rem;
}

.add-tracking-card h3 i {
    margin-right: 0.5rem;
}

.form-group {
    margin-bottom: var(--spacing-md);
}

.form-group label {
    display: block;
    margin-bottom: var(--spacing-xs);
    color: rgba(255,255,255,0.7);
    font-size: 0.85rem;
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(212,175,55,0.08);
    border-radius: 8px;
    color: #ffffff;
    font-size: 0.95rem;
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

.btn-primary {
    background: linear-gradient(135deg, #d4af37, #f5d76e);
    color: #0a0a0f;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(212,175,55,0.3);
    color: #0a0a0f;
}

.btn-secondary {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(212,175,55,0.08);
    color: rgba(255,255,255,0.6);
}

.btn-secondary:hover {
    background: rgba(255,255,255,0.08);
    transform: translateY(-2px);
    color: #ffffff;
}

.status-pending { background: rgba(245,158,11,0.15); color: #f59e0b; }
.status-processing { background: rgba(59,130,246,0.15); color: #3b82f6; }
.status-shipped { background: rgba(139,92,246,0.15); color: #8b5cf6; }
.status-delivered { background: rgba(16,185,129,0.15); color: #10b981; }
.status-cancelled { background: rgba(239,68,68,0.15); color: #ef4444; }

@media (max-width: 768px) {
    .order-info-grid {
        grid-template-columns: 1fr 1fr;
    }
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .header-actions {
        width: 100%;
    }
    .header-actions .btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 576px) {
    .order-info-grid {
        grid-template-columns: 1fr;
    }
    .timeline-item.latest .timeline-content {
        padding: var(--spacing-sm);
    }
}
</style>

<?php include 'includes/admin_footer.php'; ?>