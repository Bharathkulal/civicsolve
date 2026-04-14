<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.html');
    exit;
}

$message = '';
$messageType = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include("../backend/config/db.php");
    
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $latitude = trim($_POST['latitude'] ?? '');
    $longitude = trim($_POST['longitude'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Auto-classify department if empty
    if ($department === '') {
        $text = strtolower($title . ' ' . $description);
        if (str_contains($text, 'light') || str_contains($text, 'power') || str_contains($text, 'electric') || str_contains($text, 'pole') || str_contains($text, 'wire')) {
            $department = 'electricity';
        } elseif (str_contains($text, 'garbage') || str_contains($text, 'waste') || str_contains($text, 'trash') || str_contains($text, 'dump') || str_contains($text, 'litter')) {
            $department = 'garbage';
        } elseif (str_contains($text, 'water') || str_contains($text, 'pipeline') || str_contains($text, 'leak') || str_contains($text, 'flood') || str_contains($text, 'drain')) {
            $department = 'water';
        } else {
            $department = 'road';
        }
    }

    // Handle image upload
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
        $title = ucfirst($department) . ' issue report';
    }

    $latVal = ($latitude !== '') ? floatval($latitude) : null;
    $lngVal = ($longitude !== '') ? floatval($longitude) : null;
    $addrVal = ($address !== '') ? $address : null;
    $imgVal = ($imagePath !== '') ? $imagePath : null;

    $stmt = $conn->prepare("INSERT INTO complaints (user_id, title, department, description, image_path, latitude, longitude, address, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("issssdds", $user_id, $title, $department, $description, $imgVal, $latVal, $lngVal, $addrVal);

    if ($stmt->execute()) {
        $complaint_id = $stmt->insert_id;
        $initialMessage = $description !== '' ? $description : 'Issue submitted: ' . $title;
        $msgStmt = $conn->prepare("INSERT INTO messages (complaint_id, sender_id, sender_role, department, message) VALUES (?, ?, 'user', ?, ?)");
        if ($msgStmt) {
            $msgStmt->bind_param("iiss", $complaint_id, $user_id, $department, $initialMessage);
            $msgStmt->execute();
            $msgStmt->close();
        }
        $message = "✅ Issue submitted successfully to <strong>" . ucfirst($department) . "</strong> department!";
        $messageType = 'success';
    } else {
        $message = "❌ Error: " . $stmt->error;
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Issue - CivicSolve</title>
    <link rel="stylesheet" href="user.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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

    <div class="submit-issue-page">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1>📸 Report a Civic Issue</h1>
                <p class="page-subtitle">Capture, tag, and submit — help make your city better</p>
            </div>

            <?php if ($message): ?>
                <div class="alert-banner <?php echo $messageType === 'success' ? 'alert-success' : 'alert-error'; ?>">
                    <?php echo $message; ?>
                    <?php if ($messageType === 'success'): ?>
                        <a href="view_status.php" class="alert-link">View My Complaints →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" id="issue-form" class="issue-form-modern">
                
                <!-- ===== STEP 1: Category Selection ===== -->
                <div class="form-section">
                    <div class="section-header">
                        <span class="step-badge">1</span>
                        <h2>What type of issue?</h2>
                    </div>
                    <div class="category-grid" id="category-grid">
                        <label class="category-card" data-category="electricity">
                            <input type="radio" name="department" value="electricity">
                            <div class="category-icon">⚡</div>
                            <span class="category-name">Electricity</span>
                            <span class="category-desc">Power outage, broken poles, wires</span>
                        </label>
                        <label class="category-card" data-category="garbage">
                            <input type="radio" name="department" value="garbage">
                            <div class="category-icon">🗑️</div>
                            <span class="category-name">Garbage</span>
                            <span class="category-desc">Waste dumping, overflowing bins</span>
                        </label>
                        <label class="category-card" data-category="road">
                            <input type="radio" name="department" value="road">
                            <div class="category-icon">🛣️</div>
                            <span class="category-name">Road</span>
                            <span class="category-desc">Potholes, damaged roads, signals</span>
                        </label>
                        <label class="category-card" data-category="water">
                            <input type="radio" name="department" value="water">
                            <div class="category-icon">💧</div>
                            <span class="category-name">Water</span>
                            <span class="category-desc">Leaks, no supply, drainage issues</span>
                        </label>
                    </div>
                </div>

                <!-- ===== STEP 2: Photo Capture ===== -->
                <div class="form-section">
                    <div class="section-header">
                        <span class="step-badge">2</span>
                        <h2>Capture the Issue</h2>
                    </div>
                    <div class="photo-capture-zone" id="photo-zone">
                        <!-- Before capture -->
                        <div class="capture-placeholder" id="capture-placeholder">
                            <div class="capture-icon">📷</div>
                            <h3>Take a Photo or Upload</h3>
                            <p>Photo will be automatically geotagged with your location</p>
                            <div class="capture-buttons">
                                <button type="button" class="btn-capture" id="btn-camera" onclick="openCamera()">
                                    <span>📸</span> Open Camera
                                </button>
                                <button type="button" class="btn-upload" id="btn-upload-trigger" onclick="document.getElementById('file-input').click()">
                                    <span>📁</span> Upload Photo
                                </button>
                            </div>
                            <input type="file" name="issue_image" id="file-input" accept="image/*" capture="environment" style="display:none;">
                        </div>

                        <!-- Camera Stream -->
                        <div class="camera-container" id="camera-container" style="display:none;">
                            <video id="camera-stream" autoplay playsinline></video>
                            <div class="camera-overlay">
                                <div class="geotag-badge" id="camera-geotag">
                                    <span class="geo-icon">📍</span>
                                    <span id="camera-coords">Fetching location...</span>
                                </div>
                            </div>
                            <div class="camera-controls">
                                <button type="button" class="btn-shutter" id="btn-shutter" onclick="capturePhoto()">
                                    <div class="shutter-ring"></div>
                                </button>
                                <button type="button" class="btn-camera-close" onclick="closeCamera()">✕</button>
                            </div>
                        </div>
                        <canvas id="capture-canvas" style="display:none;"></canvas>

                        <!-- After capture preview -->
                        <div class="preview-container" id="preview-container" style="display:none;">
                            <div class="preview-image-wrap">
                                <img id="preview-image" alt="Captured photo">
                                <div class="geotag-overlay" id="preview-geotag">
                                    <span class="geo-icon">📍</span>
                                    <span id="preview-coords">No location</span>
                                </div>
                                <div class="timestamp-overlay" id="preview-timestamp"></div>
                            </div>
                            <button type="button" class="btn-retake" onclick="retakePhoto()">🔄 Retake Photo</button>
                        </div>
                    </div>
                </div>

                <!-- ===== STEP 3: Location ===== -->
                <div class="form-section">
                    <div class="section-header">
                        <span class="step-badge">3</span>
                        <h2>Location</h2>
                    </div>
                    <div class="location-section">
                        <div class="location-info-bar" id="location-bar">
                            <div class="location-status" id="location-status">
                                <div class="location-spinner"></div>
                                <span>Detecting your location...</span>
                            </div>
                        </div>
                        <div class="location-details" id="location-details" style="display:none;">
                            <div class="loc-detail-row">
                                <span class="loc-label">📍 Address</span>
                                <span class="loc-value" id="address-display">--</span>
                            </div>
                            <div class="loc-detail-row">
                                <span class="loc-label">🌐 Coordinates</span>
                                <span class="loc-value" id="coords-display">--</span>
                            </div>
                            <div class="loc-detail-row">
                                <span class="loc-label">🎯 Accuracy</span>
                                <span class="loc-value" id="accuracy-display">--</span>
                            </div>
                        </div>
                        <div id="location-map" class="location-map"></div>
                    </div>
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                    <input type="hidden" name="address" id="address">
                </div>

                <!-- ===== STEP 4: Details ===== -->
                <div class="form-section">
                    <div class="section-header">
                        <span class="step-badge">4</span>
                        <h2>Issue Details</h2>
                    </div>
                    <div class="form-group">
                        <label for="title-input">Title <span class="optional-tag">Optional</span></label>
                        <input type="text" name="title" id="title-input" placeholder="e.g. Pothole on Main Street">
                    </div>
                    <div class="form-group">
                        <label for="desc-input">Description</label>
                        <textarea name="description" id="desc-input" rows="4" placeholder="Describe the issue in detail — what you see, how severe it is..." required></textarea>
                    </div>
                </div>

                <!-- Submit -->
                <div class="form-actions">
                    <button type="submit" class="btn-submit" id="btn-submit">
                        <span class="btn-submit-icon">🚀</span>
                        Submit Report
                    </button>
                    <p class="submit-note">Your report will be routed to the appropriate department automatically</p>
                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="../assets/js/theme-toggle.js"></script>
    <script>
    // ===== GLOBALS =====
    let currentStream = null;
    let userLat = null;
    let userLng = null;
    let userAccuracy = null;
    let leafletMap = null;
    let leafletMarker = null;
    let capturedBlob = null;

    // ===== GEOLOCATION =====
    (function initGeo() {
        if (!navigator.geolocation) {
            setLocationStatus('Geolocation not supported', 'error');
            return;
        }
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                userLat = pos.coords.latitude;
                userLng = pos.coords.longitude;
                userAccuracy = Math.round(pos.coords.accuracy);
                document.getElementById('latitude').value = userLat.toFixed(6);
                document.getElementById('longitude').value = userLng.toFixed(6);

                setLocationStatus('Location captured', 'success');
                updateCoordsDisplay();
                initMap();
                reverseGeocode(userLat, userLng);
            },
            (err) => {
                setLocationStatus('Location access denied — you can still submit', 'warning');
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    })();

    function setLocationStatus(text, type) {
        const bar = document.getElementById('location-status');
        const icons = { success: '✅', error: '❌', warning: '⚠️' };
        bar.innerHTML = `<span>${icons[type] || ''} ${text}</span>`;
        bar.className = 'location-status status-' + type;
    }

    function updateCoordsDisplay() {
        document.getElementById('location-details').style.display = 'block';
        document.getElementById('coords-display').textContent = `${userLat.toFixed(6)}, ${userLng.toFixed(6)}`;
        document.getElementById('accuracy-display').textContent = `± ${userAccuracy} meters`;
        
        // Update camera geotag if open
        const camCoords = document.getElementById('camera-coords');
        if (camCoords) camCoords.textContent = `${userLat.toFixed(4)}, ${userLng.toFixed(4)}`;
    }

    function reverseGeocode(lat, lng) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
            .then(r => r.json())
            .then(data => {
                if (data.display_name) {
                    const addr = data.display_name;
                    document.getElementById('address-display').textContent = addr;
                    document.getElementById('address').value = addr;
                }
            })
            .catch(() => {
                document.getElementById('address-display').textContent = 'Could not fetch address';
            });
    }

    function initMap() {
        if (!userLat || !userLng) return;
        const mapEl = document.getElementById('location-map');
        mapEl.style.display = 'block';
        
        leafletMap = L.map('location-map', { zoomControl: false }).setView([userLat, userLng], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(leafletMap);
        leafletMarker = L.marker([userLat, userLng]).addTo(leafletMap);
        leafletMarker.bindPopup('📍 Your Location').openPopup();

        // Let user drag to adjust
        leafletMap.on('click', function(e) {
            userLat = e.latlng.lat;
            userLng = e.latlng.lng;
            document.getElementById('latitude').value = userLat.toFixed(6);
            document.getElementById('longitude').value = userLng.toFixed(6);
            leafletMarker.setLatLng(e.latlng);
            updateCoordsDisplay();
            reverseGeocode(userLat, userLng);
        });
    }

    // ===== CAMERA =====
    async function openCamera() {
        const container = document.getElementById('camera-container');
        const placeholder = document.getElementById('capture-placeholder');
        const video = document.getElementById('camera-stream');
        
        try {
            currentStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment', width: { ideal: 1920 }, height: { ideal: 1080 } }
            });
            video.srcObject = currentStream;
            placeholder.style.display = 'none';
            container.style.display = 'block';
        } catch (err) {
            alert('Camera access denied or not available. Please use the upload option instead.');
        }
    }

    function closeCamera() {
        if (currentStream) {
            currentStream.getTracks().forEach(t => t.stop());
            currentStream = null;
        }
        document.getElementById('camera-container').style.display = 'none';
        document.getElementById('capture-placeholder').style.display = 'flex';
    }

    function capturePhoto() {
        const video = document.getElementById('camera-stream');
        const canvas = document.getElementById('capture-canvas');
        const ctx = canvas.getContext('2d');
        
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0);

        // Draw geotag watermark on canvas
        const now = new Date();
        const timestamp = now.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + now.toLocaleTimeString('en-IN');
        const coordsText = (userLat && userLng) ? `📍 ${userLat.toFixed(6)}, ${userLng.toFixed(6)}` : '📍 Location not available';
        
        // Semi-transparent bar at bottom
        ctx.fillStyle = 'rgba(0,0,0,0.55)';
        ctx.fillRect(0, canvas.height - 60, canvas.width, 60);
        ctx.fillStyle = '#ffffff';
        ctx.font = 'bold 18px Poppins, sans-serif';
        ctx.fillText(coordsText, 15, canvas.height - 35);
        ctx.font = '14px Poppins, sans-serif';
        ctx.fillText('CivicSolve • ' + timestamp, 15, canvas.height - 12);

        canvas.toBlob((blob) => {
            capturedBlob = blob;
            // Create a File from blob and assign to input
            const file = new File([blob], 'civic_issue_' + Date.now() + '.jpg', { type: 'image/jpeg' });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            document.getElementById('file-input').files = dataTransfer.files;
            showPreview(URL.createObjectURL(blob));
        }, 'image/jpeg', 0.92);

        closeCamera();
    }

    // ===== FILE INPUT =====
    document.getElementById('file-input').addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            showPreview(URL.createObjectURL(this.files[0]));
        }
    });

    function showPreview(src) {
        document.getElementById('capture-placeholder').style.display = 'none';
        document.getElementById('camera-container').style.display = 'none';
        
        const previewContainer = document.getElementById('preview-container');
        const previewImg = document.getElementById('preview-image');
        previewImg.src = src;
        previewContainer.style.display = 'block';

        // Set geotag overlay on preview
        const previewCoords = document.getElementById('preview-coords');
        if (userLat && userLng) {
            previewCoords.textContent = `${userLat.toFixed(6)}, ${userLng.toFixed(6)}`;
        } else {
            previewCoords.textContent = 'No location data';
        }

        const previewTs = document.getElementById('preview-timestamp');
        const now = new Date();
        previewTs.textContent = now.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + now.toLocaleTimeString('en-IN');
    }

    function retakePhoto() {
        document.getElementById('preview-container').style.display = 'none';
        document.getElementById('capture-placeholder').style.display = 'flex';
        document.getElementById('file-input').value = '';
        capturedBlob = null;
    }

    // ===== CATEGORY SELECTION ANIMATION =====
    document.querySelectorAll('.category-card').forEach(card => {
        card.addEventListener('click', function() {
            document.querySelectorAll('.category-card').forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
        });
    });

    // ===== FORM SUBMISSION VISUAL FEEDBACK =====
    document.getElementById('issue-form').addEventListener('submit', function() {
        const btn = document.getElementById('btn-submit');
        btn.disabled = true;
        btn.innerHTML = '<div class="btn-spinner"></div> Submitting...';
    });
    </script>
</body>
</html>