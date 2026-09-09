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

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $allowed_status = ['pending', 'processing', 'completed', 'cancelled'];
    
    if (in_array($status, $allowed_status)) {
        executeQuery("UPDATE orders SET status = '$status' WHERE id = $order_id");
        $_SESSION['flash']['success'] = 'Order status updated!';
    }
    header('Location: orders.php');
    exit();
}

// Get all orders with user details
$orders = executeQuery("
    SELECT o.*, u.full_name, u.email, u.phone 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
");

include 'includes/admin_header.php';
?>

<div class="admin-page">
    <div class="page-header">
        <h1><i class="fas fa-shopping-cart" style="color:var(--gold);"></i> Orders</h1>
        <div class="header-actions">
            <span class="total-orders">Total: <?php echo mysqli_num_rows($orders); ?> orders</span>
        </div>
    </div>

    <?php if (isset($_SESSION['flash']['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['flash']['success']; unset($_SESSION['flash']['success']); ?></div>
    <?php endif; ?>

    <div class="table-wrapper">
        <?php if (mysqli_num_rows($orders) > 0): ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Payment</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                            <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                            <td>
                                <?php if ($order['payment_method'] == 'COD'): ?>
                                    <span style="color:var(--gold);"><i class="fas fa-money-bill-wave"></i> COD</span>
                                <?php else: ?>
                                    <span style="color:var(--success);"><i class="fas fa-credit-card"></i> Card</span>
                                <?php endif; ?>
                            </td>
                            <td>Rs.?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <form method="POST" style="display:flex; gap:5px; align-items:center; flex-wrap:wrap;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="status" class="status-dropdown">
                                        <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update</button>
                                </form>
                            </td>
                            <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                            <td>
                                <a href="../order_success.php?order=<?php echo $order['order_number']; ?>" class="btn btn-sm btn-secondary" target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-shopping-cart"></i>
                <h3>No Orders</h3>
                <p>There are no orders yet.</p>
            </div>
        <?php endif; ?>
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

.header-actions .total-orders {
    color: rgba(255,255,255,0.5);
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
    background: rgba(255,255,255,0.05);
    border-radius: 8px;
}

.table-wrapper {
    background: rgba(255,255,255,0.02);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(212,175,55,0.06);
    border-radius: 16px;
    padding: 1.5rem;
    overflow-x: auto;
}

.orders-table {
    width: 100%;
    border-collapse: collapse;
}

.orders-table thead {
    border-bottom: 1px solid rgba(212,175,55,0.06);
}

.orders-table th {
    color: rgba(255,255,255,0.3);
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 0.75rem 0.5rem;
    text-align: left;
}

.orders-table td {
    padding: 0.75rem 0.5rem;
    border-bottom: 1px solid rgba(255,255,255,0.02);
    color: rgba(255,255,255,0.8);
    vertical-align: middle;
    font-size: 0.85rem;
}

.orders-table tr:hover td {
    background: rgba(255,255,255,0.02);
}

/* ============================================
   FIXED DROPDOWN STYLES
   ============================================ */
.status-dropdown {
    padding: 0.3rem 0.8rem;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(212,175,55,0.15);
    border-radius: 6px;
    color: #ffffff;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 110px;
    font-family: 'Inter', sans-serif;
    appearance: auto;
    -webkit-appearance: auto;
    -moz-appearance: auto;
}

.status-dropdown:hover {
    border-color: rgba(212,175,55,0.3);
    background: rgba(255,255,255,0.12);
}

.status-dropdown:focus {
    outline: none;
    border-color: #d4af37;
    box-shadow: 0 0 0 3px rgba(212,175,55,0.1);
}

.status-dropdown option {
    background: #1a0a2e;
    color: #ffffff;
    padding: 5px 10px;
    font-family: 'Inter', sans-serif;
}

.status-dropdown option:hover {
    background: rgba(212,175,55,0.2);
}

/* Status Colors */
.status-dropdown option[value="pending"] { color: #f59e0b; }
.status-dropdown option[value="processing"] { color: #3b82f6; }
.status-dropdown option[value="completed"] { color: #10b981; }
.status-dropdown option[value="cancelled"] { color: #ef4444; }

.btn-sm {
    padding: 0.3rem 0.8rem;
    font-size: 0.75rem;
}

.btn-primary {
    background: linear-gradient(135deg, #d4af37, #f5d76e);
    color: #0a0a0f;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(212,175,55,0.3);
}

.btn-secondary {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(212,175,55,0.08);
    color: rgba(255,255,255,0.7);
    padding: 0.3rem 0.8rem;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.btn-secondary:hover {
    background: rgba(255,255,255,0.08);
    color: #ffffff;
}

.alert {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.alert-success {
    background: rgba(16,185,129,0.1);
    border-color: rgba(16,185,129,0.2);
    color: #10b981;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-state i {
    font-size: 4rem;
    color: rgba(255,255,255,0.05);
    margin-bottom: 1rem;
}

.empty-state h3 {
    color: rgba(255,255,255,0.5);
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: rgba(255,255,255,0.3);
}

/* Responsive */
@media (max-width: 768px) {
    .orders-table th,
    .orders-table td {
        padding: 0.5rem 0.3rem;
        font-size: 0.7rem;
    }
    .table-wrapper {
        padding: 1rem;
    }
    .status-dropdown {
        min-width: 80px;
        padding: 0.2rem 0.5rem;
        font-size: 0.7rem;
    }
}

@media (max-width: 576px) {
    .page-header h1 {
        font-size: 1.3rem;
    }
    .orders-table th,
    .orders-table td {
        padding: 0.3rem 0.2rem;
        font-size: 0.6rem;
    }
    .status-dropdown {
        min-width: 60px;
        padding: 0.15rem 0.3rem;
        font-size: 0.6rem;
    }
    .btn-sm {
        padding: 0.15rem 0.5rem;
        font-size: 0.6rem;
    }
}
</style>

<?php include 'includes/admin_footer.php'; ?>