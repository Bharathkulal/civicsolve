<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || $_SESSION['department'] !== 'water') {
    header('Location: ../../../login.html');
    exit;
}
include("../../../backend/config/db.php");

$dept = 'water';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $conn->query("UPDATE complaints SET status = '$status' WHERE id = '$id' AND department = '$dept'");
    $message = "Status updated successfully!";
}

$result = $conn->query("SELECT c.*, u.name as user_name FROM complaints c LEFT JOIN users u ON c.user_id = u.id WHERE c.department = '$dept' AND c.status != 'resolved' ORDER BY c.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Status - CivicSolve</title>
    <link rel="stylesheet" href="../admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar admin-nav dept-water">
        <div class="container">
            <a href="home.php" class="logo">CivicSolve <span class="badge-dept">WATER DEPT</span></a>
            <div class="nav-links">
                <a href="home.php">Dashboard</a>
                <a href="dashboard.php">Analytics</a>
                <a href="manage_issues.php">Issues</a>
                <a href="update_status.php">Update Status</a>
                <a href="../../../backend/auth/logout.php" class="btn-nav">Logout</a>
            </div>
        </div>
    </nav>
    <div class="admin-dashboard">
        <div class="container">
            <h1>Update Complaint Status</h1>
            <?php if ($message): ?>
                <div class="alert"><?php echo $message; ?></div>
            <?php endif; ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Description</th>
                        <th>Priority</th>
                        <th>Current Status</th>
                        <th>New Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($c = $result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $c['id']; ?></td>
                        <td><?php echo htmlspecialchars($c['user_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($c['description']); ?></td>
                        <td><span class="badge priority-<?php echo $c['priority']; ?>"><?php echo ucfirst($c['priority']); ?></span></td>
                        <td><span class="badge status-<?php echo $c['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $c['status'])); ?></span></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                <select name="status">
                                    <option value="pending" <?php echo $c['status']=='pending'?'selected':''; ?>>Pending</option>
                                    <option value="in_progress" <?php echo $c['status']=='in_progress'?'selected':''; ?>>In Progress</option>
                                    <option value="resolved" <?php echo $c['status']=='resolved'?'selected':''; ?>>Resolved</option>
                                </select>
                        </td>
                        <td><button type="submit" name="update_status" class="btn-small">Update</button></form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>