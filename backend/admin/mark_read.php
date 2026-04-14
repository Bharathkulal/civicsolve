<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

include(__DIR__ . '/../config/db.php');

$complaint_id = intval($_POST['complaint_id'] ?? 0);

if ($complaint_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Missing complaint_id']);
    exit;
}

$role = $_SESSION['role'] ?? 'user';
$department = $_SESSION['department'] ?? '';

// Mark messages as read: for admin, mark user messages as read; for user, mark admin messages as read
if ($role === 'admin') {
    $stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE complaint_id = ? AND department = ? AND sender_role = 'user'");
    $stmt->bind_param("is", $complaint_id, $department);
} elseif ($role === 'super_admin') {
    $stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE complaint_id = ? AND sender_role = 'user'");
    $stmt->bind_param("i", $complaint_id);
} else {
    $stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE complaint_id = ? AND sender_role != 'user'");
    $stmt->bind_param("i", $complaint_id);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Messages marked as read']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update']);
}
?>
