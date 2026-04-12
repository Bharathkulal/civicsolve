<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || $_SESSION['department'] !== 'water') {
    header('Location: ../../../login.html');
    exit;
}
include("../../backend/config/db.php");

$dept = 'water';
$total = $conn->query("SELECT COUNT(*) FROM complaints WHERE department = '$dept'")->fetch_row()[0];
$pending = $conn->query("SELECT COUNT(*) FROM complaints WHERE department = '$dept' AND status = 'pending'")->fetch_row()[0];
$in_progress = $conn->query("SELECT COUNT(*) FROM complaints WHERE department = '$dept' AND status = 'in_progress'")->fetch_row()[0];
$resolved = $conn->query("SELECT COUNT(*) FROM complaints WHERE department = '$dept' AND status = 'resolved'")->fetch_row()[0];
$result = $conn->query("SELECT c.*, u.name as user_name FROM complaints c LEFT JOIN users u ON c.user_id = u.id WHERE c.department = '$dept' ORDER BY c.created_at DESC LIMIT 20");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Department - CivicSolve</title>
    <link rel="stylesheet" href="../admin.css">
    <link rel="stylesheet" href="../../assets/css/theme.css">
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
            <h1>Water Department Dashboard</h1>
            <div class="stats-grid">
                <div class="stat-card"><h3>Total</h3><span class="stat-number"><?php echo $total; ?></span></div>
                <div class="stat-card"><h3>Pending</h3><span class="stat-number"><?php echo $pending; ?></span></div>
                <div class="stat-card"><h3>In Progress</h3><span class="stat-number"><?php echo $in_progress; ?></span></div>
                <div class="stat-card resolved"><h3>Resolved</h3><span class="stat-number"><?php echo $resolved; ?></span></div>
            </div>
            <div class="recent-complaints">
                <h2>Water Complaints</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Description</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Date</th>
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
                            <td><?php echo date('d M Y', strtotime($c['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="../../assets/js/theme-toggle.js"></script>
</body>
</html>