<?php
session_start();

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && ($_SESSION['department'] ?? '') === 'garbage') {
    header('Location: ../admin/garbage/home.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === 'admin' && $password === 'admin') {
        $_SESSION['user_id'] = 1002;
        $_SESSION['name'] = 'Garbage Admin';
        $_SESSION['role'] = 'admin';
        $_SESSION['department'] = 'garbage';
        header('Location: ../admin/garbage/home.php');
        exit;
    }
    $error = 'Invalid credentials. Use admin / admin.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Garbage Admin Login</title>
    <link rel="stylesheet" href="../assets/css/admin-entry.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <main class="entry-card">
        <h1>Garbage Admin</h1>
        <p>Sign in to manage garbage complaints.</p>
        <?php if ($error): ?><div class="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <p class="hint">Default credentials: admin / admin</p>
    </main>
</body>
</html>
