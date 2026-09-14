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

// Get all users
$users = executeQuery("SELECT * FROM users ORDER BY id DESC");

include 'includes/admin_header.php';
?>

<div class="admin-page">
    <div class="page-header">
        <h1><i class="fas fa-users" style="color:var(--gold);"></i> Users</h1>
        <div class="header-actions">
            <span class="total-users">Total: <?php echo mysqli_num_rows($users); ?> users</span>
        </div>
    </div>

    <?php if (isset($_SESSION['flash']['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['flash']['success']; unset($_SESSION['flash']['success']); ?></div>
    <?php endif; ?>

    <div class="table-wrapper">
        <?php if (mysqli_num_rows($users) > 0): ?>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Type</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = mysqli_fetch_assoc($users)): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td>
                                <?php 
                                $img_path = '../uploads/' . $user['profile_image'];
                                if (file_exists($img_path) && $user['profile_image'] != 'default.png'): 
                                ?>
                                    <img src="<?php echo $img_path; ?>" alt="<?php echo htmlspecialchars($user['full_name']); ?>" class="user-avatar">
                                <?php else: ?>
                                    <div class="user-avatar-placeholder">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($user['full_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td>
                                <?php if ($user['user_type'] == 'admin'): ?>
                                    <span class="user-badge admin-badge"><i class="fas fa-crown"></i> Admin</span>
                                <?php else: ?>
                                    <span class="user-badge customer-badge"><i class="fas fa-user"></i> Customer</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>No Users Found</h3>
                <p>There are no registered users yet.</p>
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

.page-header h1 i {
    color: #f9f9f9;
    margin-right: 0.75rem;
}

.header-actions .total-users {
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

/* Users Table */
.users-table {
    width: 100%;
    border-collapse: collapse;
}

.users-table thead {
    border-bottom: 1px solid rgba(212,175,55,0.06);
}

.users-table th {
    color: rgba(255,255,255,0.3);
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 0.75rem 0.5rem;
    text-align: left;
}

.users-table td {
    padding: 0.75rem 0.5rem;
    border-bottom: 1px solid rgba(255,255,255,0.02);
    color: rgba(255,255,255,0.8);
    vertical-align: middle;
    font-size: 0.85rem;
}

.users-table tr:hover td {
    background: rgba(255,255,255,0.02);
}

/* User Avatar */
.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(212,175,55,0.15);
}

.user-avatar-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.05);
    border: 2px solid rgba(212,175,55,0.06);
    color: rgba(255,255,255,0.1);
    font-size: 1.2rem;
}


.user-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.25rem 0.8rem;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}


.admin-badge {
    background: linear-gradient(135deg, rgba(212,175,55,0.15), rgba(139,92,246,0.15));
    color: #fafafade;
    border: 1px solid rgba(212,175,55,0.1);
}

.admin-badge i {
    color: #f6f5f2;
}


.customer-badge {
    background: linear-gradient(135deg, rgba(6,182,212,0.12), rgba(59,130,246,0.12));
    color: #fbfbfb;
    border: 1px solid rgba(6,182,212,0.08);
}

.customer-badge i {
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
    border-color: rgba(16,185,129,0.2);
    color: #10b981;
}

/* Empty State */
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
    .users-table th,
    .users-table td {
        padding: 0.5rem 0.3rem;
        font-size: 0.75rem;
    }
    .table-wrapper {
        padding: 1rem;
    }
    .user-avatar,
    .user-avatar-placeholder {
        width: 30px;
        height: 30px;
        font-size: 0.9rem;
    }
}

@media (max-width: 576px) {
    .users-table th,
    .users-table td {
        font-size: 0.65rem;
        padding: 0.3rem 0.2rem;
    }
    .page-header h1 {
        font-size: 1.3rem;
    }
    .user-badge {
        font-size: 0.6rem;
        padding: 0.15rem 0.5rem;
    }
}
</style>

<?php include 'includes/admin_footer.php'; ?>