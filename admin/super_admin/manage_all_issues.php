<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    header('Location: ../../../login.html');
    exit;
}
require_once __DIR__ . '/../../backend/config/db.php';

$complaints = $conn->query("SELECT c.*, u.name as user_name FROM complaints c LEFT JOIN users u ON c.user_id = u.id ORDER BY c.created_at DESC")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE complaints SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    header('Location: manage_all_issues.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage All Issues - CivicSolve</title>
    <link rel="stylesheet" href="../admin.css">
    <link rel="stylesheet" href="../../assets/css/theme.css">
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
            <h1>Manage All Issues</h1>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Department</th>
                        <th>User</th>
                        <th>Title</th>
                        <th>Photo</th>
                        <th>Location</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($complaints as $c): ?>
                    <tr>
                        <td>#<?php echo $c['id']; ?></td>
                        <td><span class="badge dept-<?php echo $c['department']; ?>"><?php echo ucfirst($c['department']); ?></span></td>
                        <td><?php echo htmlspecialchars($c['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($c['title']); ?></td>
                        <td>
                            <?php if (!empty($c['image_path'])): ?>
                                <img src="../../../<?php echo htmlspecialchars($c['image_path']); ?>" alt="Issue" style="width:80px;height:60px;object-fit:cover;cursor:pointer;border-radius:5px;" onclick="window.location.href='../../view_image.php?file=<?php echo urlencode(basename($c['image_path'])); ?>'">
                            <?php else: ?>
                                <span style="color:var(--gray);font-size:0.8rem;">No photo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($c['latitude']) && !empty($c['longitude'])): ?>
                                📍 <?php echo number_format($c['latitude'], 4); ?>, <?php echo number_format($c['longitude'], 4); ?><br>
                                <small><?php echo !empty($c['address']) ? htmlspecialchars(substr($c['address'], 0, 40)) : 'No address'; ?></small>
                            <?php else: ?>
                                <span style="color:var(--gray);font-size:0.8rem;">No location</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars(substr($c['description'], 0, 80)); ?>...</td>
                        <td><span class="badge status-<?php echo $c['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $c['status'])); ?></span></td>
                        <td><?php echo date('d M Y', strtotime($c['created_at'])); ?></td>
                        <td>
                            <form method="POST" class="update-form">
                                <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                <select name="status">
                                    <option value="pending" <?php echo $c['status']=='pending'?'selected':''; ?>>Pending</option>
                                    <option value="in_progress" <?php echo $c['status']=='in_progress'?'selected':''; ?>>In Progress</option>
                                    <option value="resolved" <?php echo $c['status']=='resolved'?'selected':''; ?>>Resolved</option>
                                </select>
                                <button type="submit" name="update_status" class="btn-small">Update</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        departmental admin dashboard
        </div>
    </div>
</body>
</html>