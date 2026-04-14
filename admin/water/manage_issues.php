<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || $_SESSION['department'] !== 'water') {
    header('Location: ../../../login.html');
    exit;
}
require_once __DIR__ . '/../../backend/config/db.php';
$dept = 'water';
$complaints = $conn->query("SELECT c.*, u.name as user_name FROM complaints c LEFT JOIN users u ON c.user_id = u.id WHERE c.department = '$dept' ORDER BY c.created_at DESC");
$complaints = $complaints->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Issues - CivicSolve</title>
    <link rel="stylesheet" href="../admin.css">
    <link rel="stylesheet" href="../../assets/css/theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar admin-nav dept-water"><div class="container">
        <a href="home.php" class="logo">CivicSolve <span class="badge-dept">WATER DEPT</span></a>
        <div class="nav-links"><a href="home.php">Dashboard</a><a href="dashboard.php">Analytics</a><a href="manage_issues.php">Issues</a><a href="update_status.php">Update Status</a><a href="../../../backend/auth/logout.php" class="btn-nav">Logout</a></div>
    </div></nav>
    <div class="admin-dashboard"><div class="container">
        <h1>Manage Water Issues</h1>
        <table class="admin-table"><thead><tr><th>ID</th><th>User</th><th>Title</th><th>Photo</th><th>Location</th><th>Description</th><th>Status</th><th>Date</th></tr></thead>
            <tbody><?php foreach ($complaints as $c): ?><tr><td>#<?php echo $c['id']; ?></td><td><?php echo htmlspecialchars($c['user_name']); ?></td><td><?php echo htmlspecialchars($c['title']); ?></td><td><?php if (!empty($c['image_path'])): ?><img src="../../../<?php echo htmlspecialchars($c['image_path']); ?>" alt="Issue" style="width:80px;height:60px;object-fit:cover;cursor:pointer;border-radius:5px;" onclick="window.location.href='../../view_image.php?file=<?php echo urlencode(basename($c['image_path'])); ?>' "><?php else: ?><span style="color:var(--gray);font-size:0.8rem;">No photo</span><?php endif; ?></td><td><?php if (!empty($c['latitude']) && !empty($c['longitude'])): ?>📍 <?php echo number_format($c['latitude'], 4); ?>, <?php echo number_format($c['longitude'], 4); ?><br><small><?php echo !empty($c['address']) ? htmlspecialchars(substr($c['address'], 0, 40)) : 'No address'; ?></small><?php else: ?><span style="color:var(--gray);font-size:0.8rem;">No location</span><?php endif; ?></td><td><?php echo htmlspecialchars(substr($c['description'], 0, 100)); ?>...</td><td><span class="badge status-<?php echo $c['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $c['status'])); ?></span></td><td><?php echo date('d M Y', strtotime($c['created_at'])); ?></td></tr><?php endforeach; ?></tbody></table>
        </div></div>
</body></html>