<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.html');
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
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

    <div class="view-status">
        <div class="container">
            <h1>My Complaints</h1>
            <div class="complaints-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Photo</th>
                            <th>Title</th>
                            <th>Department</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($c = $result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $c['id']; ?></td>
                            <td>
                                <?php if (!empty($c['image_path'])): ?>
                                    <img src="../<?php echo htmlspecialchars($c['image_path']); ?>" 
                                         alt="Issue photo" 
                                         class="complaint-image-thumb" 
                                         onclick="openLightbox('../<?php echo htmlspecialchars($c['image_path']); ?>', '<?php echo htmlspecialchars($c['title']); ?>')">
                                <?php else: ?>
                                    <span style="color:var(--gray-light);font-size:0.85rem;">No photo</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($c['title']); ?></td>
                            <td><span class="badge dept-<?php echo $c['department']; ?>"><?php echo ucfirst($c['department']); ?></span></td>
                            <td>
                                <?php if (!empty($c['latitude']) && !empty($c['longitude'])): ?>
                                    <span class="complaint-location-tag">
                                        📍 <?php echo number_format($c['latitude'], 4); ?>, <?php echo number_format($c['longitude'], 4); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color:var(--gray-light);font-size:0.85rem;">—</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge status-<?php echo $c['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $c['status'])); ?></span></td>
                            <td><?php echo date('d M Y', strtotime($c['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php if ($result->num_rows == 0): ?>
                    <p class="no-issues">No complaints found. <a href="submit_issue.php">Report your first issue!</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Image Lightbox Modal -->
    <div class="image-lightbox" id="imageLightbox" onclick="closeLightboxOutside(event)">
        <div class="lightbox-toolbar">
            <button class="lightbox-back" onclick="closeLightbox()">
                <i class="fas fa-arrow-left"></i>
                <span>Back</span>
            </button>
            <span class="lightbox-info" id="lightboxTitle"></span>
            <div class="lightbox-actions">
                <button class="lightbox-action-btn" onclick="downloadImage()" title="Download">
                    <i class="fas fa-download"></i>
                </button>
                <button class="lightbox-action-btn" onclick="openNewTab()" title="Open in new tab">
                    <i class="fas fa-external-link-alt"></i>
                </button>
                <button class="lightbox-action-btn" onclick="closeLightbox()" title="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="lightbox-image-wrap" onclick="event.stopPropagation()">
            <img id="lightboxImage" src="" alt="Complaint photo">
        </div>
    </div>

    <script src="../assets/js/theme-toggle.js"></script>
    <script>
        let currentImageSrc = '';

        function openLightbox(src, title) {
            currentImageSrc = src;
            document.getElementById('lightboxImage').src = src;
            document.getElementById('lightboxTitle').textContent = title || 'Issue Photo';
            document.getElementById('imageLightbox').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            document.getElementById('imageLightbox').classList.remove('active');
            document.body.style.overflow = '';
        }

        function closeLightboxOutside(e) {
            if (e.target === document.getElementById('imageLightbox')) {
                closeLightbox();
            }
        }

        function downloadImage() {
            const a = document.createElement('a');
            a.href = currentImageSrc;
            a.download = currentImageSrc.split('/').pop();
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }

        function openNewTab() {
            window.open(currentImageSrc, '_blank');
        }

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeLightbox();
        });
    </script>
</body>
</html>