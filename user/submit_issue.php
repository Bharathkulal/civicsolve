<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.html');
    exit;
}

$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include("../backend/config/db.php");
    
    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'];
    $department = $_POST['department'];
    $description = $_POST['description'];

    $sql = "INSERT INTO complaints (user_id, title, department, description, status) 
            VALUES ('$user_id', '$title', '$department', '$description', 'pending')";

    if ($conn->query($sql)) {
        $message = "Issue submitted successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Issue - CivicSolve</title>
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

    <div class="submit-issue">
        <div class="container">
            <h1>Submit New Issue</h1>
            <?php if ($message): ?>
                <div class="alert"><?php echo $message; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" placeholder="Brief title" required>
                </div>
                <div class="form-group">
                    <label>Department</label>
                    <select name="department" required>
                        <option value="">Select Department</option>
                        <option value="road">Road</option>
                        <option value="water">Water</option>
                        <option value="electricity">Electricity</option>
                        <option value="garbage">Garbage</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="5" placeholder="Describe the issue..." required></textarea>
                </div>
                <button type="submit" class="btn-primary">Submit Issue</button>
            </form>
        </div>
    </div>
</body>
</html>