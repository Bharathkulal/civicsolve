<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

include(__DIR__ . '/../config/db.php');

$complaint_id = intval($_POST['complaint_id'] ?? 0);
$message = trim($_POST['message'] ?? '');
$sender_id = $_SESSION['user_id'];
$sender_role = $_SESSION['role'] ?? 'user';

if ($complaint_id <= 0 || $message === '') {
    echo json_encode(['success' => false, 'message' => 'Missing complaint_id or message']);
    exit;
}

// Get department from complaint
$dept_result = $conn->query("SELECT department FROM complaints WHERE id = $complaint_id");
if (!$dept_result || $dept_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Complaint not found']);
    exit;
}
$department = $dept_result->fetch_assoc()['department'];

// If admin, verify they belong to this department (unless super_admin)
if ($sender_role === 'admin' && isset($_SESSION['department']) && $_SESSION['department'] !== $department) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized department']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO messages (complaint_id, sender_id, sender_role, department, message) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iisss", $complaint_id, $sender_id, $sender_role, $department, $message);

if ($stmt->execute()) {
    // Get the inserted message with sender name
    $msg_id = $conn->insert_id;
    $result = $conn->query("SELECT m.*, u.name as sender_name FROM messages m LEFT JOIN users u ON m.sender_id = u.id WHERE m.id = $msg_id");
    $msg = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'message' => 'Message sent',
        'data' => [
            'id' => $msg['id'],
            'sender_name' => $msg['sender_name'],
            'sender_role' => $msg['sender_role'],
            'message' => $msg['message'],
            'created_at' => $msg['created_at']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send message']);
}
?>
