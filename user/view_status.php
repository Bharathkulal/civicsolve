<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.html');
    exit;
}
include("../backend/config/db.php");

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM complaints WHERE user_id = '$user_id' ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Complaints - CivicSolve</title>
    <link rel="stylesheet" href="user.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="home.php" class="logo">CivicSolve</a>
            <div class="nav-links">
                <a href="home.php">Dashboard</a>
                <a href="submit_issue.php">Submit Issue</a>
                <a href="view_status.php">My Complaints</a>
                <a href="../backend/auth/logout.php" class="btn-nav">Logout</a>
            </div>
        </div>
    </nav>

    <div class="view-status">
        <div class="container">
            <h1>My Complaints</h1>
            <div class="complaints-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($c = $result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $c['id']; ?></td>
                            <td><?php echo htmlspecialchars($c['title']); ?></td>
                            <td><span class="badge dept-<?php echo $c['department']; ?>"><?php echo ucfirst($c['department']); ?></span></td>
                            <td><span class="badge status-<?php echo $c['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $c['status'])); ?></span></td>
                            <td><?php echo date('d M Y', strtotime($c['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php if ($result->num_rows == 0): ?>
                    <p class="no-issues">No complaints found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>