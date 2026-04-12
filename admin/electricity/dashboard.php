<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || $_SESSION['department'] !== 'electricity') {
    header('Location: ../../../login.html');
    exit;
}
require_once __DIR__ . '/../../backend/config/db.php';
$dept = 'electricity';
$high = $conn->query("SELECT COUNT(*) FROM complaints WHERE department = '$dept' AND priority = 'high'")->fetch_row()[0];
$week = $conn->query("SELECT COUNT(*) FROM complaints WHERE department = '$dept' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_row()[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electricity Analytics - CivicSolve</title>
    <link rel="stylesheet" href="../admin.css">
    <link rel="stylesheet" href="../../assets/css/theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar admin-nav dept-electricity"><div class="container">
        <a href="home.php" class="logo">CivicSolve <span class="badge-dept">ELECTRICITY DEPT</span></a>
        <div class="nav-links"><a href="home.php">Dashboard</a><a href="dashboard.php">Analytics</a><a href="manage_issues.php">Issues</a><a href="update_status.php">Update Status</a><a href="../../../backend/auth/logout.php" class="btn-nav">Logout</a></div>
    </div></nav>
    <div class="admin-dashboard"><div class="container">
        <h1>Electricity Department Analytics</h1>
        <div class="stats-grid">
            <div class="stat-card high"><h3>High Priority</h3><span class="stat-number"><?php echo $high; ?></span></div>
            <div class="stat-card"><h3>This Week</h3><span class="stat-number"><?php echo $week; ?></span></div>
        </div></div></div>
</body></html>