<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.html');
    exit;
}
include("../backend/config/db.php");

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM complaints WHERE user_id = '$user_id' ORDER BY created_at DESC LIMIT 5";
$result = $conn->query($sql);
$recent_complaints = [];
while ($row = $result->fetch_assoc()) {
    $recent_complaints[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CivicSolve</title>
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
                <span class="user-name"><?php echo $_SESSION['name']; ?></span>
                <a href="../backend/auth/logout.php" class="btn-nav">Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard">
        <div class="container">
            <h1>Welcome, <?php echo $_SESSION['name']; ?> 👋</h1>
            <div class="dashboard-grid">
                <div class="card action-card">
                    <h2>Submit New Issue</h2>
                    <p>Report a civic problem with photo and AI will classify it automatically.</p>
                    <a href="submit_issue.php" class="btn-primary">Report Issue</a>
                </div>
                <div class="card">
                    <h2>My Complaints</h2>
                    <p>Track status of your submitted complaints.</p>
                    <a href="view_status.php" class="btn-secondary">View All</a>
                </div>
            </div>

            <div class="recent-issues">
                <h2>Recent Complaints</h2>
                <div class="issues-list">
                    <?php foreach ($recent_complaints as $complaint): ?>
                    <div class="issue-card">
                        <div class="issue-info">
                            <h3><?php echo htmlspecialchars($complaint['title']); ?></h3>
                            <span class="department"><?php echo ucfirst($complaint['department']); ?></span>
                        </div>
                        <div class="issue-status status-<?php echo $complaint['status']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($recent_complaints)): ?>
                        <p class="no-issues">No complaints yet. <a href="submit_issue.php">Submit your first issue!</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>