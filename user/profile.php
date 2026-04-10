<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.html');
    exit;
}

$profileDir = __DIR__ . '/../uploads/profiles';
if (!is_dir($profileDir)) {
    mkdir($profileDir, 0755, true);
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $displayName = trim($_POST['display_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($displayName !== '') {
        $_SESSION['name'] = $displayName;
    }
    $_SESSION['profile_phone'] = $phone;
    $_SESSION['profile_address'] = $address;

    if (!empty($_FILES['profile_image']['name']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = mime_content_type($_FILES['profile_image']['tmp_name']);
        if (!isset($allowed[$mime])) {
            $error = 'Only JPG, PNG, or WEBP images are allowed.';
        } else {
            $fileName = 'user_' . $_SESSION['user_id'] . '_' . time() . '.' . $allowed[$mime];
            $targetPath = $profileDir . '/' . $fileName;
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
                $_SESSION['profile_image'] = '../uploads/profiles/' . $fileName;
            } else {
                $error = 'Profile image upload failed. Please try again.';
            }
        }
    }

    if ($error === '') {
        $message = 'Profile updated successfully.';
    }
}

$profileImage = $_SESSION['profile_image'] ?? '../assets/default-avatar.svg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - CivicSolve</title>
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
                <a href="profile.php">Profile</a>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                <a href="../backend/auth/logout.php" class="btn-nav">Logout</a>
            </div>
        </div>
    </nav>

    <div class="profile-page">
        <div class="container">
            <h1>My Profile</h1>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="profile-form">
                <div class="profile-avatar-wrap">
                    <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile picture" class="profile-avatar">
                    <label for="profile_image">Upload Profile Picture</label>
                    <input type="file" id="profile_image" name="profile_image" accept="image/*">
                </div>

                <div class="form-group">
                    <label for="display_name">Name</label>
                    <input id="display_name" type="text" name="display_name" value="<?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input id="phone" type="text" name="phone" value="<?php echo htmlspecialchars($_SESSION['profile_phone'] ?? ''); ?>" placeholder="Optional">
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="4" placeholder="Optional"><?php echo htmlspecialchars($_SESSION['profile_address'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn-primary">Save Profile</button>
            </form>
        </div>
    </div>
</body>
</html>
