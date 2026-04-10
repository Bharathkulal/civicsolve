<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    header('Location: ../../../login.html');
    exit;
}
require_once __DIR__ . '/../../backend/config/db.php';

$dept_stats = [];
$depts = ['road', 'garbage', 'water', 'electricity'];
foreach ($depts as $dept) {
    $total = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE department = ?");
    $total->execute([$dept]);
    $resolved = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE department = ? AND status = 'resolved'");
    $resolved->execute([$dept]);
    $dept_stats[$dept] = ['total' => $total->fetchColumn(), 'resolved' => $resolved->fetchColumn()];
}

$all_admins = $pdo->query("SELECT * FROM users WHERE role = 'admin' ORDER BY department")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - CivicSolve</title>
    <link rel="stylesheet" href="../admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar admin-nav">
        <div class="container">
            <a href="home.php" class="logo">CivicSolve <span class="badge-super">SUPER ADMIN</span></a>
            <div class="nav-links">
                <a href="home.php">Dashboard</a>
                <a href="dashboard.php">Analytics</a>
                <a href="manage_all_issues.php">All Issues</a>
                <a href="manage_users.php">Manage Users</a>
                <a href="../../../backend/auth/logout.php" class="btn-nav">Logout</a>
            </div>
        </div>
    </nav>

    <div class="admin-dashboard">
        <div class="container">
            <h1>Analytics Dashboard</h1>
            
            <h2>Department-wise Stats</h2>
            <div class="dept-stats">
                <?php foreach ($dept_stats as $dept => $stats): ?>
                <div class="dept-card dept-<?php echo $dept; ?>">
                    <h3><?php echo ucfirst($dept); ?></h3>
                    <div class="dept-stat">
                        <span>Total: <?php echo $stats['total']; ?></span>
                        <span>Resolved: <?php echo $stats['resolved']; ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <h2>Department Admins</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_admins as $admin): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($admin['name']); ?></td>
                        <td><?php echo htmlspecialchars($admin['email']); ?></td>
                        <td><span class="badge dept-<?php echo $admin['department']; ?>"><?php echo ucfirst($admin['department']); ?></span></td>
                        <td><?php echo date('d M Y', strtotime($admin['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>