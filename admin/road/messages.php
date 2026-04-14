<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || $_SESSION['department'] !== 'road') {
    header('Location: ../../../login.html');
    exit;
}
include("../../backend/config/db.php");

$dept = 'road';
$admin_id = $_SESSION['user_id'];

// Get unread count
$unread_count = 0;
if ($res = $conn->query("SELECT COUNT(*) FROM messages WHERE department = '$dept' AND sender_role = 'user' AND is_read = 0")) {
    $unread_count = $res->fetch_row()[0];
}

// Get all complaint threads that have messages for this department
$threads = $conn->query("
    SELECT c.id as complaint_id, c.title, c.description, c.status, c.priority, c.created_at as complaint_date,
           u.name as user_name, u.email as user_email,
           (SELECT COUNT(*) FROM messages WHERE complaint_id = c.id AND sender_role = 'user' AND is_read = 0) as unread,
           (SELECT MAX(created_at) FROM messages WHERE complaint_id = c.id) as last_message_time
    FROM complaints c
    LEFT JOIN users u ON c.user_id = u.id
    WHERE c.department = '$dept'
    AND EXISTS (SELECT 1 FROM messages WHERE complaint_id = c.id)
    ORDER BY last_message_time DESC
");

// Get selected complaint messages
$selected_id = intval($_GET['complaint_id'] ?? 0);
$selected_messages = [];
$selected_complaint = null;

if ($selected_id > 0) {
    // Mark messages as read
    $conn->query("UPDATE messages SET is_read = 1 WHERE complaint_id = $selected_id AND department = '$dept' AND sender_role = 'user'");
    
    $complaint_result = $conn->query("SELECT c.*, u.name as user_name FROM complaints c LEFT JOIN users u ON c.user_id = u.id WHERE c.id = $selected_id AND c.department = '$dept'");
    $selected_complaint = $complaint_result ? $complaint_result->fetch_assoc() : null;
    
    $msg_result = $conn->query("SELECT m.*, u.name as sender_name FROM messages m LEFT JOIN users u ON m.sender_id = u.id WHERE m.complaint_id = $selected_id ORDER BY m.created_at ASC");
    while ($msg_result && $m = $msg_result->fetch_assoc()) {
        $selected_messages[] = $m;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Road Department - CivicSolve</title>
    <link rel="stylesheet" href="../admin.css">
    <link rel="stylesheet" href="../../assets/css/theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar admin-nav dept-road">
        <div class="container">
            <a href="home.php" class="logo">CivicSolve <span class="badge-dept">ROAD DEPT</span></a>
            <div class="nav-links">
                <a href="home.php">Dashboard</a>
                <a href="dashboard.php">Analytics</a>
                <a href="manage_issues.php">Issues</a>
                <a href="messages.php" class="nav-msg-link active">Messages <?php if($unread_count > 0): ?><span class="msg-badge"><?php echo $unread_count; ?></span><?php endif; ?></a>
                <a href="update_status.php">Update Status</a>
                <a href="../../../backend/auth/logout.php" class="btn-nav">Logout</a>
            </div>
        </div>
    </nav>

    <div class="admin-dashboard">
        <div class="container">
            <h1>📬 Messages</h1>
            <div class="messages-layout">
                <!-- Thread List -->
                <div class="thread-list">
                    <div class="thread-list-header">
                        <h3>Complaint Threads</h3>
                        <span class="thread-count"><?php echo $threads ? $threads->num_rows : 0; ?> conversations</span>
                    </div>
                    <div class="thread-items">
                        <?php if (!$threads || $threads->num_rows === 0): ?>
                            <div class="empty-threads">
                                <div class="empty-icon">📭</div>
                                <p>No messages yet</p>
                            </div>
                        <?php else: ?>
                            <?php while ($t = $threads->fetch_assoc()): ?>
                            <a href="messages.php?complaint_id=<?php echo $t['complaint_id']; ?>" 
                               class="thread-item <?php echo ($selected_id == $t['complaint_id']) ? 'active' : ''; ?> <?php echo ($t['unread'] > 0) ? 'unread' : ''; ?>">
                                <div class="thread-avatar">
                                    <?php echo strtoupper(substr($t['user_name'] ?? 'U', 0, 1)); ?>
                                </div>
                                <div class="thread-info">
                                    <div class="thread-top">
                                        <span class="thread-user"><?php echo htmlspecialchars($t['user_name'] ?? 'Unknown'); ?></span>
                                        <span class="thread-time"><?php echo date('d M, H:i', strtotime($t['last_message_time'])); ?></span>
                                    </div>
                                    <div class="thread-subject">#<?php echo $t['complaint_id']; ?> — <?php echo htmlspecialchars(substr($t['title'], 0, 40)); ?></div>
                                    <div class="thread-meta">
                                        <span class="badge status-<?php echo $t['status']; ?>"><?php echo ucfirst(str_replace('_',' ',$t['status'])); ?></span>
                                        <?php if ($t['unread'] > 0): ?>
                                            <span class="unread-badge"><?php echo $t['unread']; ?> new</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Chat Area -->
                <div class="chat-area">
                    <?php if ($selected_complaint): ?>
                        <div class="chat-header">
                            <div class="chat-header-back">
                                <a href="messages.php" class="btn-back">← Back to Messages</a>
                            </div>
                            <div class="chat-header-info">
                                <h3>#<?php echo $selected_complaint['id']; ?> — <?php echo htmlspecialchars($selected_complaint['title']); ?></h3>
                                <span class="chat-header-user">From: <?php echo htmlspecialchars($selected_complaint['user_name']); ?> • <?php echo date('d M Y', strtotime($selected_complaint['created_at'])); ?></span>
                            </div>
                            <span class="badge status-<?php echo $selected_complaint['status']; ?>"><?php echo ucfirst(str_replace('_',' ',$selected_complaint['status'])); ?></span>
                        </div>
                        <div class="chat-messages" id="chat-messages">
                            <?php foreach ($selected_messages as $msg): ?>
                            <div class="chat-bubble <?php echo ($msg['sender_role'] !== 'user') ? 'sent' : 'received'; ?>">
                                <div class="bubble-sender"><?php echo htmlspecialchars($msg['sender_name'] ?? 'Unknown'); ?> <span class="bubble-role">(<?php echo ucfirst($msg['sender_role']); ?>)</span></div>
                                <div class="bubble-text"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                                <div class="bubble-time"><?php echo date('d M Y, h:i A', strtotime($msg['created_at'])); ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="chat-input">
                            <form id="reply-form" onsubmit="sendReply(event)">
                                <input type="hidden" name="complaint_id" value="<?php echo $selected_id; ?>">
                                <textarea name="message" id="reply-text" placeholder="Type your reply..." rows="2" required></textarea>
                                <button type="submit" class="btn-send" id="btn-send">
                                    <span>Send</span>
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13"/><path d="M22 2L15 22L11 13L2 9L22 2Z"/></svg>
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="chat-placeholder">
                            <div class="chat-placeholder-icon">💬</div>
                            <h3>Select a conversation</h3>
                            <p>Choose a complaint thread from the left to view and reply to messages</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/theme-toggle.js"></script>
    <script>
    // Scroll to bottom of chat
    const chatMessages = document.getElementById('chat-messages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    async function sendReply(e) {
        e.preventDefault();
        const form = document.getElementById('reply-form');
        const btn = document.getElementById('btn-send');
        const textarea = document.getElementById('reply-text');
        const message = textarea.value.trim();
        
        if (!message) return;
        
        btn.disabled = true;
        btn.innerHTML = '<span>Sending...</span>';
        
        const formData = new FormData();
        formData.append('complaint_id', form.querySelector('[name=complaint_id]').value);
        formData.append('message', message);
        
        try {
            const res = await fetch('../../backend/admin/send_message.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            
            if (data.success) {
                // Add bubble to chat
                const bubble = document.createElement('div');
                bubble.className = 'chat-bubble sent new-bubble';
                bubble.innerHTML = `
                    <div class="bubble-sender">${data.data.sender_name} <span class="bubble-role">(${data.data.sender_role.charAt(0).toUpperCase() + data.data.sender_role.slice(1)})</span></div>
                    <div class="bubble-text">${data.data.message.replace(/\n/g, '<br>')}</div>
                    <div class="bubble-time">Just now</div>
                `;
                chatMessages.appendChild(bubble);
                chatMessages.scrollTop = chatMessages.scrollHeight;
                textarea.value = '';
            } else {
                alert('Failed to send: ' + data.message);
            }
        } catch (err) {
            alert('Network error. Please try again.');
        }
        
        btn.disabled = false;
        btn.innerHTML = '<span>Send</span><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13"/><path d="M22 2L15 22L11 13L2 9L22 2Z"/></svg>';
    }
    </script>
</body>
</html>
