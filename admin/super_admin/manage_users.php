<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    header('Location: ../../../login.html');
    exit;
}
require_once __DIR__ . '/../../backend/config/db.php';

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $department = $_POST['department'];
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, department) VALUES (?, ?, ?, 'admin', ?)");
    $stmt->execute([$name, $email, $password, $department]);
    header('Location: manage_users.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'super_admin'");
    $stmt->execute([$id]);
    header('Location: manage_users.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - CivicSolve</title>
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
            <h1>Manage Users & Admins</h1>
            
            <div class="add-admin-form">
                <h2>Add New Admin</h2>
                <form method="POST">
                    <input type="text" name="name" placeholder="Name" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <select name="department" required>
                        <option value="">Select Department</option>
                        <option value="road">Road</option>
                        <option value="garbage">Garbage</option>
                        <option value="water">Water</option>
                        <option value="electricity">Electricity</option>
                    </select>
                    <button type="submit" name="add_admin" class="btn-primary">Add Admin</button>
                </form>
            </div>

            <h2>All Users</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Joined</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><span class="badge <?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                        <td><?php echo $user['department'] ? ucfirst($user['department']) : '-'; ?></td>
                        <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <?php if ($user['role'] !== 'super_admin'): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                <button type="submit" name="delete_user" class="btn-small btn-danger">Delete</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>