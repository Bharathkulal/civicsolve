<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.html');
    exit;
}

$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include("../backend/config/db.php");
    
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $latitude = trim($_POST['latitude'] ?? '');
    $longitude = trim($_POST['longitude'] ?? '');
    $locationAccuracy = trim($_POST['location_accuracy'] ?? '');
    $apiKeyPlaceholder = trim($_POST['api_key_placeholder'] ?? '');

    if ($department === '') {
        $text = strtolower($title . ' ' . $description);
        if (str_contains($text, 'light') || str_contains($text, 'power') || str_contains($text, 'electric')) {
            $department = 'electricity';
        } elseif (str_contains($text, 'garbage') || str_contains($text, 'waste') || str_contains($text, 'trash')) {
            $department = 'garbage';
        } elseif (str_contains($text, 'water') || str_contains($text, 'pipeline') || str_contains($text, 'leak')) {
            $department = 'water';
        } else {
            $department = 'road';
        }
    }

    $imagePath = '';
    if (!empty($_FILES['issue_image']['name']) && $_FILES['issue_image']['error'] === UPLOAD_ERR_OK) {
        $issueDir = __DIR__ . '/../uploads/issues';
        if (!is_dir($issueDir)) {
            mkdir($issueDir, 0755, true);
        }

        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = mime_content_type($_FILES['issue_image']['tmp_name']);
        if (isset($allowed[$mime])) {
            $fileName = 'issue_' . $user_id . '_' . time() . '.' . $allowed[$mime];
            $target = $issueDir . '/' . $fileName;
            if (move_uploaded_file($_FILES['issue_image']['tmp_name'], $target)) {
                $imagePath = 'uploads/issues/' . $fileName;
            }
        }
    }

    if ($title === '') {
        $title = 'Civic issue report';
    }

    $descriptionWithMeta = $description;
    if ($latitude !== '' && $longitude !== '') {
        $descriptionWithMeta .= "\n\nLocation: {$latitude}, {$longitude}";
    }
    if ($locationAccuracy !== '') {
        $descriptionWithMeta .= "\nAccuracy: {$locationAccuracy} meters";
    }
    if ($imagePath !== '') {
        $descriptionWithMeta .= "\nImage: {$imagePath}";
    }
    if ($apiKeyPlaceholder !== '') {
        $descriptionWithMeta .= "\nAI_API_KEY_PLACEHOLDER: {$apiKeyPlaceholder}";
    }

    $stmt = $conn->prepare("INSERT INTO complaints (user_id, title, department, description, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->bind_param("isss", $user_id, $title, $department, $descriptionWithMeta);

    if ($stmt->execute()) {
        $message = "Issue submitted successfully to " . ucfirst($department) . " department.";
    } else {
        $message = "Error: " . $stmt->error;
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
                <a href="profile.php">Profile</a>
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
            <form method="POST" enctype="multipart/form-data" id="issue-form">
                <div class="form-group">
                    <label>Title (Optional)</label>
                    <input type="text" name="title" placeholder="Brief title">
                </div>
                <div class="form-group">
                    <label>Department (Auto if left empty)</label>
                    <select name="department">
                        <option value="">Auto classify department</option>
                        <option value="road">Road</option>
                        <option value="water">Water</option>
                        <option value="electricity">Electricity</option>
                        <option value="garbage">Garbage</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Issue Photo (with geotag support)</label>
                    <input type="file" name="issue_image" accept="image/*" capture="environment">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="5" placeholder="Describe the issue..." required></textarea>
                </div>
                <div class="form-group">
                    <label>Future AI API Key Slot (for later integration)</label>
                    <input type="text" name="api_key_placeholder" placeholder="Paste API key later (optional)">
                </div>
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">
                <input type="hidden" name="location_accuracy" id="location_accuracy">
                <p id="location-status" class="hint-text">Fetching current location...</p>
                <button type="submit" class="btn-primary">Submit Issue</button>
            </form>
        </div>
    </div>
    <script>
        (function () {
            const status = document.getElementById('location-status');
            if (!navigator.geolocation) {
                status.textContent = 'Geolocation is not supported in this browser.';
                return;
            }
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    document.getElementById('latitude').value = pos.coords.latitude.toFixed(6);
                    document.getElementById('longitude').value = pos.coords.longitude.toFixed(6);
                    document.getElementById('location_accuracy').value = Math.round(pos.coords.accuracy);
                    status.textContent = 'Location captured successfully.';
                },
                () => {
                    status.textContent = 'Location access denied. You can still submit without geotag.';
                },
                { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
            );
        })();
    </script>
</body>
</html>