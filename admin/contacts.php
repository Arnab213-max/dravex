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

$admin_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_reply'])) {
    $contact_id = (int)$_POST['contact_id'];
    $reply_message = sanitizeInput($_POST['reply_message']);

    if (!empty($reply_message)) {
        executeQuery("UPDATE contacts SET status = 'replied', reply = '$reply_message' WHERE id = $contact_id");
        executeQuery("INSERT INTO contact_replies (contact_id, admin_id, reply_message) VALUES ($contact_id, $admin_id, '$reply_message')");
        $_SESSION['flash']['success'] = 'Reply sent successfully!';
    } else {
        $_SESSION['flash']['error'] = 'Please enter a reply message.';
    }
    header('Location: contacts.php');
    exit();
}

if (isset($_GET['read'])) {
    $id = (int)$_GET['read'];
    executeQuery("UPDATE contacts SET status = 'read' WHERE id = $id");
    header('Location: contacts.php');
    exit();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    executeQuery("DELETE FROM contacts WHERE id = $id");
    $_SESSION['flash']['success'] = 'Contact message deleted!';
    header('Location: contacts.php');
    exit();
}

$contacts = executeQuery("
    SELECT c.*, 
           (SELECT COUNT(*) FROM contact_replies WHERE contact_id = c.id) as reply_count,
           (SELECT reply_message FROM contact_replies WHERE contact_id = c.id ORDER BY id DESC LIMIT 1) as last_reply
    FROM contacts c 
    ORDER BY c.created_at DESC
");

$total = mysqli_num_rows($contacts);
$unread = mysqli_fetch_assoc(executeQuery("SELECT COUNT(*) as count FROM contacts WHERE status = 'unread'"))['count'];

include 'includes/admin_header.php';
?>

<div class="admin-page">
    <div class="page-header">
        <h1><i class="fas fa-envelope"></i> Contact Messages</h1>
        <div class="header-actions">
            <span class="total-orders">Total: <?php echo $total; ?> | Unread: <?php echo $unread; ?></span>
        </div>
    </div>

    <?php if (isset($_SESSION['flash']['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['flash']['success']; unset($_SESSION['flash']['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash']['error'])): ?>
        <div class="alert alert-error"><?php echo $_SESSION['flash']['error']; unset($_SESSION['flash']['error']); ?></div>
    <?php endif; ?>

    <div class="table-wrapper">
        <?php if (mysqli_num_rows($contacts) > 0): ?>
            <table class="contacts-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Replies</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($contact = mysqli_fetch_assoc($contacts)): ?>
                        <tr>
                            <td>
                                <?php if ($contact['status'] == 'unread'): ?>
                                    <span class="status-badge unread">Unread</span>
                                <?php elseif ($contact['status'] == 'read'): ?>
                                    <span class="status-badge read">Read</span>
                                <?php else: ?>
                                    <span class="status-badge replied">Replied</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($contact['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($contact['email']); ?></td>
                            <td><?php echo htmlspecialchars($contact['subject']); ?></td>
                            <td>
                                <div class="message-preview">
                                    <?php echo htmlspecialchars(substr($contact['message'], 0, 50)); ?>...
                                </div>
                            </td>
                            <td><?php echo $contact['reply_count']; ?></td>
                            <td><?php echo date('d M Y, h:i A', strtotime($contact['created_at'])); ?></td>
                            <td>
                                <div class="action-group">
                                    <?php if ($contact['status'] == 'unread'): ?>
                                        <a href="contacts.php?read=<?php echo $contact['id']; ?>" class="btn btn-sm btn-primary">Mark Read</a>
                                    <?php endif; ?>
                                    <button type="button"
                                            class="btn btn-sm btn-success reply-btn"
                                            data-id="<?php echo $contact['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($contact['name'], ENT_QUOTES); ?>"
                                            data-subject="<?php echo htmlspecialchars($contact['subject'], ENT_QUOTES); ?>"
                                            data-message="<?php echo htmlspecialchars($contact['message'], ENT_QUOTES); ?>">
                                        <i class="fas fa-reply"></i> Reply
                                    </button>
                                    <a href="contacts.php?delete=<?php echo $contact['id']; ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Delete this message?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-envelope-open-text"></i>
                <h3>No Messages</h3>
                <p>There are no contact messages yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="replyModal" class="reply-modal">
    <div class="reply-modal-content">
        <h3 class="reply-modal-title">
            <i class="fas fa-reply"></i> Reply to Message
        </h3>

        <div class="reply-modal-info">
            <p><strong>From:</strong> <span id="replyName"></span></p>
            <p><strong>Subject:</strong> <span id="replySubject"></span></p>
            <p><strong>Message:</strong></p>
            <p id="replyMessage" class="reply-modal-message"></p>
        </div>

        <form method="POST" class="reply-form">
            <input type="hidden" name="contact_id" id="replyContactId">
            <div class="form-group">
                <label for="replyMessageInput">Your Reply *</label>
                <textarea name="reply_message" id="replyMessageInput" class="form-control" rows="5" placeholder="Type your reply here..." required></textarea>
            </div>
            <div class="reply-modal-actions">
                <button type="submit" name="send_reply" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Send Reply
                </button>
                <button type="button" class="btn btn-secondary" id="replyCancelBtn">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('replyModal');
    const cancelBtn = document.getElementById('replyCancelBtn');

    document.querySelectorAll('.reply-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('replyContactId').value = this.dataset.id;
            document.getElementById('replyName').textContent = this.dataset.name;
            document.getElementById('replySubject').textContent = this.dataset.subject;
            document.getElementById('replyMessage').textContent = this.dataset.message;
            modal.classList.add('active');
        });
    });

    cancelBtn.addEventListener('click', function () {
        modal.classList.remove('active');
    });

    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            modal.classList.remove('active');
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            modal.classList.remove('active');
        }
    });
});
</script>

<style>
.admin-page {
    padding: 0;
    max-width: 1200px;
    margin: 0 auto;
    width: 100%;
    box-sizing: border-box;
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
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.page-header h1 i {
    color: #d4af37;
}

.header-actions .total-orders {
    color: rgba(255,255,255,0.6);
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
    background: rgba(255,255,255,0.05);
    border-radius: 8px;
    white-space: nowrap;
}

.table-wrapper {
    background: rgba(255,255,255,0.02);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(212,175,55,0.08);
    border-radius: 16px;
    padding: 1.5rem;
    overflow-x: auto;
    width: 100%;
    box-sizing: border-box;
}

.contacts-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 900px;
}

.contacts-table thead {
    border-bottom: 1px solid rgba(212,175,55,0.1);
}

.contacts-table th {
    color: rgba(255,255,255,0.4);
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 0.75rem 0.6rem;
    text-align: left;
    white-space: nowrap;
}

.contacts-table td {
    padding: 0.85rem 0.6rem;
    border-bottom: 1px solid rgba(255,255,255,0.03);
    color: rgba(255,255,255,0.85);
    vertical-align: middle;
    font-size: 0.85rem;
}

.contacts-table tr:hover td {
    background: rgba(255,255,255,0.02);
}

.message-preview {
    max-width: 220px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: rgba(255,255,255,0.7);
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.65rem;
    border-radius: 50px;
    font-size: 0.68rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
}

.status-badge.unread {
    background: rgba(239,68,68,0.15);
    color: #ef4444;
}

.status-badge.read {
    background: rgba(245,158,11,0.15);
    color: #f59e0b;
}

.status-badge.replied {
    background: rgba(16,185,129,0.15);
    color: #10b981;
}

.action-group {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    align-items: center;
}

.btn {
    padding: 0.6rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    transition: all 0.25s ease;
    font-family: inherit;
    line-height: 1;
}

.btn-sm {
    padding: 0.35rem 0.75rem;
    font-size: 0.75rem;
    border-radius: 6px;
}

.btn-primary {
    background: linear-gradient(135deg, #d4af37, #f5d76e);
    color: #0a0a0f;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(212,175,55,0.3);
}

.btn-success {
    background: #10b981;
    color: #ffffff;
}

.btn-success:hover {
    background: #059669;
    transform: translateY(-2px);
}

.btn-danger {
    background: #ef4444;
    color: #ffffff;
}

.btn-danger:hover {
    background: #dc2626;
    transform: translateY(-2px);
}

.btn-secondary {
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(212,175,55,0.15);
    color: rgba(255,255,255,0.85);
}

.btn-secondary:hover {
    background: rgba(255,255,255,0.12);
    color: #ffffff;
}

.alert {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
}

.alert-success {
    background: rgba(16,185,129,0.12);
    border: 1px solid rgba(16,185,129,0.25);
    color: #10b981;
}

.alert-error {
    background: rgba(239,68,68,0.12);
    border: 1px solid rgba(239,68,68,0.25);
    color: #ef4444;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-state i {
    font-size: 4rem;
    color: rgba(255,255,255,0.08);
    margin-bottom: 1rem;
}

.empty-state h3 {
    color: rgba(255,255,255,0.6);
    font-size: 1.4rem;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: rgba(255,255,255,0.35);
    margin: 0;
}

.reply-modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(8px);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    box-sizing: border-box;
}

.reply-modal.active {
    display: flex;
}

.reply-modal-content {
    background: #14141a;
    border: 1px solid rgba(212,175,55,0.15);
    border-radius: 16px;
    width: 100%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    padding: 2rem;
    box-sizing: border-box;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
}

.reply-modal-title {
    color: #d4af37;
    margin: 0 0 1.25rem;
    font-size: 1.3rem;
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.reply-modal-info {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.05);
    border-radius: 10px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.25rem;
    color: rgba(255,255,255,0.85);
    font-size: 0.9rem;
}

.reply-modal-info p {
    margin: 0.4rem 0;
    line-height: 1.5;
}

.reply-modal-info strong {
    color: rgba(255,255,255,0.6);
    font-weight: 600;
}

.reply-modal-message {
    color: rgba(255,255,255,0.75);
    white-space: pre-wrap;
    word-break: break-word;
    padding-left: 0.25rem;
}

.reply-form .form-group {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    margin-bottom: 1.25rem;
}

.reply-form label {
    color: rgba(255,255,255,0.7);
    font-size: 0.9rem;
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    color: #ffffff;
    font-family: inherit;
    font-size: 0.95rem;
    resize: vertical;
    min-height: 100px;
    box-sizing: border-box;
    line-height: 1.5;
    transition: border-color 0.2s, background 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #d4af37;
    background: rgba(255,255,255,0.08);
}

.form-control::placeholder {
    color: rgba(255,255,255,0.35);
}

.reply-modal-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .page-header h1 {
        font-size: 1.4rem;
    }

    .contacts-table th,
    .contacts-table td {
        padding: 0.6rem 0.4rem;
        font-size: 0.78rem;
    }

    .table-wrapper {
        padding: 1rem;
    }

    .reply-modal-content {
        padding: 1.5rem;
    }

    .reply-modal-actions {
        flex-direction: column;
    }

    .reply-modal-actions .btn {
        width: 100%;
    }

    .action-group {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>

<?php include 'includes/admin_footer.php'; ?>